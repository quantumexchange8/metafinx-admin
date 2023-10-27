<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddMemberRequest;
use App\Http\Requests\EditMemberRequest;
use App\Http\Requests\KycApprovalRequest;
use App\Models\SettingCountry;
use App\Models\SettingRank;
use App\Models\User;
use App\Models\InvestmentSubscription;
use App\Models\Wallet;
use App\Notifications\KycApprovalNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function listing()
    {
        $settingRanks = SettingRank::all();

        $formattedRanks = $settingRanks->map(function ($country) {
            return [
                'value' => $country->id,
                'label' => $country->name,
            ];
        });

        $settingCountries = SettingCountry::all();

        $formattedCountries = $settingCountries->map(function ($country) {
            return [
                'value' => $country->name_en,
                'label' => $country->name_en,
                'phone_code' => $country->phone_code,
            ];
        });

        return Inertia::render('Member/MemberListing', [
            'pendingKycCount' => User::where('kyc_approval', '=', 'pending')->count(),
            'unverifiedKycCount' => User::where('kyc_approval', '=', 'unverified')->count(),
            'settingRanks' => $formattedRanks,
            'countries' => $formattedCountries,
        ]);
    }

    public function getMemberDetails(Request $request)
    {
        $members = User::query()
            ->where('role', '=', 'user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $type = $request->input('type');
                $query->where(function ($innerQuery) use ($type) {
                    $innerQuery->where('kyc_approval', $type);
                });
            })
            ->when($request->filled('rank'), function ($query) use ($request) {
                $rank_id = $request->input('rank');
                $query->where(function ($innerQuery) use ($rank_id) {
                    $innerQuery->where('setting_rank_id', $rank_id);
                });
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $date = $request->input('date');
                $dateRange = explode(' - ', $date);
                $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
                $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();
                $query->whereBetween('created_at', [$start_date, $end_date]);
            })
            ->select('id', 'name', 'email', 'setting_rank_id', 'kyc_approval', 'created_at')
            ->with('rank:id,name')
            ->withSum('wallets', 'balance')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $members->each(function ($user) {
            $user->profile_photo_url = $user->getFirstMediaUrl('profile_photo');
            $user->front_identity = $user->getFirstMediaUrl('front_identity');
            $user->back_identity = $user->getFirstMediaUrl('back_identity');
            $user->kyc_upload_date = $user->getMedia('back_identity')->first()->created_at ?? null;
            $user->active_investment_amount = $this->getActiveSubscriptionAmount($user);
        });

        return response()->json($members);
    }

    public function addMember(AddMemberRequest $request)
    {
        $upline_id = $request->upline_id['value'];
        $upline = User::find($upline_id);

        if(empty($upline->hierarchyList)) {
            $hierarchyList = "-" . $upline_id . "-";
        } else {
            $hierarchyList = $upline->hierarchyList . $upline_id . "-";
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'country' => $request->country,
            'phone' => $request->phone,
            'verification_type' => $request->verification_type,
            'upline_id' => $upline_id,
            'hierarchyList' => $hierarchyList,
            'setting_rank_id' => $request->ranking,
            'password' => Hash::make($request->password),
            'identity_number' => $request->identity_number,
            'role' => 'user',
            'kyc_approval' => 'unverified',
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'name' => 'Internal Wallet'
        ]);

        $user->setReferralId();

        return redirect()->back()->with('title', 'New member added!')->with('success', 'The new member has been added successfully.');
    }

    public function deleteMember(Request $request)
    {
        $user = User::find($request->user_id);

        $user->delete();

        return redirect()->back();
    }

    public function verifyMember(KycApprovalRequest $request)
    {
        $user = User::find($request->id);
        $approvalType = $request->type;

        $title = '';
        $message = '';

        if ($approvalType == 'approve') {
            $user->update([
                'kyc_approval' => 'verified',
            ]);

            $title = 'Member verified!';
            $message = 'The member has been verified successfully.';

        } elseif ($approvalType == 'reject') {
            $user->update([
                'kyc_approval' => 'unverified',
                'kyc_approval_description' => $request->remark,
            ]);

            $title = 'Member unverified!';
            $message = 'An email has been sent to the member to request updated KYC information.';

        }

        Notification::route('mail', $user->email)
            ->notify(new KycApprovalNotification($user));

        return redirect()->back()->with('title', $title)->with('success', $message);
    }

    public function editMember(EditMemberRequest $request)
    {
        $user = User::find($request->user_id);
        $upline_id = $request->upline_id['value'];

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'identity_number' => $request->identity_number,
            'setting_rank_id' => $request->rank,
        ]);

        if ($request->password) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        if ($user->upline_id != $upline_id) {
            $this->transferUpline($user, $upline_id);
        }

        return redirect()->back()->with('title', 'Member updated!')->with('success', 'The member has been updated successfully.');
    }

    public function viewMemberDetails($id)
    {
        $user = User::with('media')->find($id);

        $upline = User::with('media')
        ->where('id', $user->upline_id)
        ->select('name','id')
        ->first();

        $investments = InvestmentSubscription::query()
            ->with('investment_plan:id,name,roi_per_annum,investment_period')
            ->where('user_id', $user->id)
            ->get();

        $locale = app()->getLocale(); // Get the current locale

        $investmentSubscriptions = $investments->map(function ($investmentSubscription) use ($locale) {
            return [
                'id' => $investmentSubscription->id,
                'plan_name' => [
                    'name' => $investmentSubscription->investment_plan->getTranslation('name', $locale),
                ],
                'roi_per_annum' => $investmentSubscription->investment_plan->roi_per_annum,
                'investment_period' => $investmentSubscription->investment_plan->investment_period,
                'subscription_id' => $investmentSubscription->subscription_id,
                'amount' => $investmentSubscription->amount,
                'total_earning' => $investmentSubscription->total_earning,
                'status' => $investmentSubscription->status,
                'next_roi_date' => $investmentSubscription->next_roi_date,
                'expired_date' => $investmentSubscription->expired_date,
                'created_at' => $investmentSubscription->created_at,
            ];
        });

        $settingRanks = SettingRank::all();

        $formattedRanks = $settingRanks->map(function ($country) {
            return [
                'value' => $country->id,
                'label' => $country->name,
            ];
        });

        $wallets = Wallet::where('user_id', $user->id)->get();
        $walletSum = Wallet::where('user_id', $user->id)->sum('balance');
        $referralCount = User::where('upline_id', $user->id)->count();

        return Inertia::render('Member/MemberDetails/MemberDetail', [
            'member_details' => $user,
            'upline_member' => $upline,
            'investments' => $investmentSubscriptions,
            'settingRank' => $formattedRanks,
            'wallets' => $wallets,
            'walletSum' => floatval($walletSum),
            'referralCount' => $referralCount,
            // 'total_affiliate' => count($user->getChildrenIds()),
            'self_deposit' => floatval($this->getSelfDeposit($user)),
            'valid_affiliate_deposit' => floatval($this->getValidAffiliateDeposit($user)),
        ]);
    }

    public function unsubscribePlan(Request $request)
    {
        $investment_subscriptions = InvestmentSubscription::find($request->investment_id);

        $request->validate([
            'remark' => 'required'
        ], [
            'remark' => 'The Remark field is required.'
        ]);

        $investment_subscriptions->update([
            'status' => 'Terminated',
            'remark' => $request->remark,
        ]);

        return redirect()->back()->with('title', 'Subscription terminated!')->with('success', 'The investment plan has been terminated successfully.');
    }

    public function affiliate_tree($id)
    {
        $user = User::find($id);

        return Inertia::render('Member/MemberAffiliates/MemberAffiliate', [
            'user' => $user
        ]);
    }

    public function getTreeData(Request $request, $id)
    {

        $searchUser = null;
        $searchTerm = $request->input('search');

        if ($searchTerm) {
            $searchUser = User::where('name', 'like', '%' . $searchTerm . '%')
                ->orWhere('email', 'like', '%' . $searchTerm . '%')
                ->first();

            if (!$searchUser) {
                return response()->json(['error' => 'User not found for the given search term.'], 404);
            }
        }

        $user = $searchUser ?? User::find($id);

        $query = User::whereHas('upline', function ($query) use ($user) {
            $query->where('id', $user->id);
        });

        if ($searchUser) {
            $query->orWhere('id', $searchUser->id);
        }

        $users = $query->get();

        $level = 0;
        $rootNode = [
            'name' => $user->name,
            'profile_photo' => $user->getFirstMediaUrl('profile_photo'),
            'email' => $user->email,
            'level' => $level,
            'total_affiliate' => count($user->getChildrenIds()),
            'rank' => $user->setting_rank_id,
            'self_deposit' => $this->getSelfDeposit($user),
            'valid_affiliate_deposit' => $this->getValidAffiliateDeposit($user),
            'children' => $users->map(function ($user) {
                return $this->mapUser($user, 0);
            })
        ];

        return response()->json($rootNode);
    }

    public function getAllUsers(Request $request)
    {
        $users = User::query()
            ->where('role', '=', 'user')
            ->whereNot('id', $request->id)
            ->when($request->filled('query'), function ($query) use ($request) {
                $search = $request->input('query');
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->select('id', 'name', 'email')
            ->get();

        $users->each(function ($users) {
            $users->profile_photo = $users->getFirstMediaUrl('profile_photo');
        });

        return response()->json($users);
    }

    protected function mapUser($user, $level) {
        $children = $user->children;

        $mappedChildren = $children->map(function ($child) use ($level) {
            return $this->mapUser($child, $level + 1);
        });

        $mappedUser = [
            'name' => $user->name,
            'profile_photo' => $user->getFirstMediaUrl('profile_photo'),
            'email' => $user->email,
            'level' => $level + 1,
            'total_affiliate' => count($user->getChildrenIds()),
            'rank' => $user->setting_rank_id,
            'self_deposit' => $this->getSelfDeposit($user),
            'valid_affiliate_deposit' => $this->getValidAffiliateDeposit($user),
        ];

        // Add 'children' only if there are children
        if (!$mappedChildren->isEmpty()) {
            $mappedUser['children'] = $mappedChildren;
        }

        return $mappedUser;
    }

    protected function getSelfDeposit($user)
    {
        return InvestmentSubscription::query()
            ->where('user_id', $user->id)
            ->whereDate('expired_date', '>', now())
            ->sum('amount');
    }

    protected function getValidAffiliateDeposit($user)
    {
        $ids = $user->getChildrenIds();
        $ids[] = $user->id;

        return InvestmentSubscription::query()
            ->whereIn('user_id', $ids)
            ->whereDate('expired_date', '>', now())
            ->sum('amount');
    }

    protected function getActiveSubscriptionAmount($user)
    {
        return InvestmentSubscription::query()
            ->where('user_id', $user->id)
            ->whereDate('expired_date', '>', now())
            ->sum('amount');
    }

    protected function transferUpline($user, $upline_id)
    {
        if ($user->id == $upline_id) {
            throw ValidationException::withMessages(['upline_id' => 'Upline cannot be themselves']);
        }

        if ($upline_id == $user->upline_id) {
            throw ValidationException::withMessages(['upline_id' => 'Upline cannot be the same']);
        }

        $new_parent = User::find($upline_id);

        if ($user->upline_id != $new_parent->id) {

            if (str_contains($new_parent->hierarchyList, $user->id)) {
                $new_parent->hierarchyList = $user->hierarchyList;
                $new_parent->upline_id = $user->upline_id;
                $new_parent->save();
            }

            if (empty($new_parent->hierarchyList)) {
                $user_hierarchy = "-" . $new_parent->id . "-";
            } else {
                $user_hierarchy = $new_parent->hierarchyList . $new_parent->id . "-";
            }

            $this->updateHierarchyList($user, $user_hierarchy, '-' . $user->id . '-');

            $user->hierarchyList = $user_hierarchy;
            $user->upline_id = $new_parent->id;
            $user->save();

            // Update hierarchyList for users with same upline_id
            $sameUplineIdUsers = User::where('upline_id', $new_parent->id)->get();
            if ($sameUplineIdUsers) {
                foreach ($sameUplineIdUsers as $sameUplineUser) {
                    $new_user_hierarchy = $new_parent->hierarchyList . $new_parent->id . "-";
                    $this->updateHierarchyList($sameUplineUser, $new_user_hierarchy, '-' . $sameUplineUser->id . '-');
                    $sameUplineUser->hierarchyList = $new_user_hierarchy;
                    $sameUplineUser->upline_id = $new_parent->id;
                    $sameUplineUser->save();
                }
            }
        }
    }

    private function updateHierarchyList($user, $list, $id)
    {
        $children = $user->children;
        if (count($children)) {
            foreach ($children as $child) {
                //$child->hierarchyList = substr($list, -1) . substr($child->hierarchyList, strpos($child->hierarchyList, $id) + strlen($id));
                $child->hierarchyList = substr($list, 0, -1) . $id;
                $child->save();
                $this->updateHierarchyList($child, $list, $id . $child->id . '-');
            }
        }
    }

}
