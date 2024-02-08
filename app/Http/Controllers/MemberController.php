<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use App\Models\CoinPrice;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Wallet;
use App\Models\Earning;
use App\Models\SettingCoin;
use App\Models\SettingRank;
use Illuminate\Http\Request;
use App\Models\CoinAdjustment;
use App\Models\SettingCountry;
use Illuminate\Support\Carbon;
use App\Models\BalanceAdjustment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\InvestmentSubscription;
use App\Http\Requests\AddMemberRequest;
use Spatie\Activitylog\Models\Activity;
use App\Http\Requests\EditMemberRequest;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\KycApprovalRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Http\Requests\CoinAdjustmentRequest;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\WalletAdjustmentRequest;
use App\Models\AssetAdjustment;
use App\Models\Transaction;
use App\Notifications\KycApprovalNotification;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MemberListingExport;
use App\Exports\MemberListingTypeExport;
use App\Services\RunningNumberService;
use App\Models\CoinMultiLevel;
use App\Models\CoinStacking;

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
        
        // if ($request->has('exportStatus')) {
        //     if($request->type != null){
        //         return Excel::download(new MemberListingTypeExport($members), Carbon::now() . '-' . $request->type . '-report.xlsx');
        //     } else {
        //         return Excel::download(new MemberListingExport($members), Carbon::now() . '-' . '-report.xlsx');
        //     }
        // }

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

        $currentRank = $user->setting_rank_id;

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'identity_number' => $request->identity_number,
            'setting_rank_id' => $request->rank,
        ]);

        if ($currentRank != $user->setting_rank_id) {
            $user->rank_up_status = 'manual';
            $user->save();
        }

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
        $coins = Coin::with('setting_coin')->where('user_id', $user->id)->get();

        return Inertia::render('Member/MemberDetails/MemberDetail', [
            'member_details' => $user,
            'upline_member' => $upline,
            'investments' => $investmentSubscriptions,
            'settingRank' => $formattedRanks,
            'wallets' => $wallets,
            'walletSum' => floatval($walletSum),
            'referralCount' => $referralCount,
            'coins' => $coins,
            'setting_coin' => SettingCoin::where('symbol', 'MXT/USD')->first(),
            // 'total_affiliate' => count($user->getChildrenIds()),
            'self_deposit' => floatval($this->getSelfDeposit($user)),
            'valid_affiliate_deposit' => floatval($this->getValidAffiliateDeposit($user)),
        ]);
    }

    public function getMemberInformation($id)
    {
        $user = User::find($id);

        $referralCount = User::where('upline_id', $user->id)->count();
        $walletSum = Wallet::where('user_id', $user->id)->sum('balance');
        $earningSum = Earning::where('upline_id', $user->id)->sum('after_amount');

        $types = [
            'StandardRewards', 
            'ReferralEarnings', 
            'AffiliateEarnings', 
            'DividendEarnings', 
            'AffiliateDividendEarnings',
            'StackingRewards',
            'BinaryReferralEarnings',
            'PairingEarnings'
        ];

        $totalEarnings = [];
        foreach ($types as $type) {
            $totalEarnings[$type] = Earning::query()
                ->where('upline_id', $user->id)
                ->where('type', $type)
                ->sum('after_amount');
        }

        return response()->json([
            'walletSum' => floatval($walletSum),
            'earningSum' => floatval($earningSum),
            'referralCount' => $referralCount,
            'self_deposit' => floatval($this->getSelfDeposit($user)),
            'valid_affiliate_deposit' => floatval($this->getValidAffiliateDeposit($user)),
            'totalEarnings' => $totalEarnings,
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

    public function wallet_adjustment(WalletAdjustmentRequest $request)
    {
        $amount = $request->amount;

        $wallet = Wallet::find($request->wallet_id);
        $new_balance = $wallet->balance + $amount;
        
        if ($new_balance < 0 || $amount == 0) {
            throw ValidationException::withMessages(['amount' => 'Insufficient balance']);
        }

        $adjustment_id = RunningNumberService::getID('adjustment');
        
        $wallet_balance = Transaction::create([
            'user_id' => $request->user_id,
            'from_wallet_id' => $request->wallet_id,
            'transaction_type' => 'WalletAdjustment',
            'category' => 'wallet',
            // 'old_amount' => $wallet->balance,
            'amount' => '0',
            'transaction_number' => $adjustment_id,
            'transaction_amount' => $amount,
            'remarks' => $request->description,
            'handle_by' => Auth::id(),
            'status' => 'Success',
        ]);

        $wallet->update([
            'balance' => $new_balance
        ]);

        $wallet_balance->update([
            'new_wallet_amount' => $new_balance
        ]);

        return redirect()->back()->with('title', 'Wallet Adjusted!')->with('success', 'This wallet has been adjusted successfully.');
    }

    public function internal_transfer(Request $request)
    {
        $amount = $request->amount;

        //transfer from
        $user_id = $request->user_id;
        $wallet = Wallet::find($request->wallet_id);

        //transfer to
        $to_user_id = $request->to_user_id['value'];
        $to_user = User::find($to_user_id);
        $to_wallet = Wallet::where('user_id', $to_user_id)->first();

        if ($wallet->balance < $amount || $amount == 0) {
            throw ValidationException::withMessages(['amount' => 'Insufficient balance']);
        }

        if ($to_user_id == $user_id) {
            throw ValidationException::withMessages(['to_user_id' => 'Cannot transfer to his own account']);
        }

        $from_user_after_balance = $wallet->balance - $amount;
        $to_user_after_balance = $to_wallet->balance + $amount;

        $wallet_balance = AssetAdjustment::create([
            'user_id' => $user_id,
            'wallet_id' => $request->wallet_id,
            'to_user_id' => $to_user_id,
            'to_wallet_id' => $to_wallet->id,
            'type' => 'InternalTransfer',
            'old_amount' => $wallet->balance,
            'amount' => $amount,
            'description' => $request->description,
            'handle_by' => Auth::id(),
        ]);

        $wallet->update([
            'balance' => $from_user_after_balance
        ]);

        $to_wallet->update([
            'balance' => $to_user_after_balance
        ]);

        $wallet_balance->update([
            'new_amount' => $from_user_after_balance
        ]);

        return redirect()->back()->with('title', 'Balance Transferred!')->with('success', 'The amount is transferred successfully to ' . $to_user->name . '.');
    }

    public function coin_adjustment(CoinAdjustmentRequest $request)
    {
        $unit = $request->unit;

        $coin = Coin::find($request->coin_id);
        $new_unit = $coin->unit + $unit;

        if ($new_unit < 0 || $unit == 0) {
            throw ValidationException::withMessages(['unit' => 'Insufficient unit']);
        }

        $adjustment_id = RunningNumberService::getID('adjustment');

        $coin_balance = Transaction::create([
            'user_id' => $request->user_id,
            'from_coin_id' => $request->coin_id,
            // 'setting_coin_id' => $request->setting_coin_id,
            'category' => 'asset',
            'transaction_type' => 'AssetAdjustment',
            'transaction_number' => $adjustment_id,
            'transaction_amount' => $unit,
            'remarks' => $request->description,
            'handle_by' => Auth::id(),
            'status' => 'Success',
        ]);

        $coin->update([
            'unit' => $new_unit,
        ]);

        $coin_balance->update([
            'new_coin_amount' => $new_unit
        ]);

        return redirect()->back()->with('title', 'Coin Adjusted!')->with('success', 'This coin has been adjusted successfully.');
    }

    public function affiliate_tree($id)
    {
        $user = User::find($id);
        $downline = User::where('upline_id', $id)->with(['coinStaking'])->get();

        // Get the upline's ID
        $uplineId = User::where('id', $id)->value('upline_id');
        $uplineStaking = true;

        if ($uplineId) {
            // If there is an upline, check if they have a coin stacking record
            $uplineStaking = CoinStacking::where('user_id', $uplineId)->exists();
        }

        return Inertia::render('Member/MemberAffiliates/MemberAffiliate', [
            'user' => $user,
            'downline' => $downline,
            'uplineStaking' => $uplineStaking,
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

        $users = User::whereHas('upline', function ($query) use ($user) {
            $query->where('id', $user->id);
        })
            ->where('role', 'user')
            ->get();

//        if ($searchUser) {
//            $query->orWhere('id', $searchUser->id);
//        }
//
//        $users = $query->get();

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

    public function impersonate(User $user): \Symfony\Component\HttpFoundation\Response
    {
        $dataToHash = $user->name . $user->email . $user->id;
        $hashedToken = md5($dataToHash);

        Activity::create([
            'log_name' => 'user', // Specify the log name here
            'description' => Auth::user()->name . ' has IMPERSONATE ' . $user->name . ' with ID: ' . $user->id,
            'subject_type' => User::class,
            'subject_id' => Auth::id(),
            'causer_type' => get_class(auth()->user()),
            'causer_id' => auth()->id(),
            'event' => 'impersonate',
        ]);

        $domain = $_SERVER['HTTP_HOST'];

        if ($domain === 'secure-admin.metafinx.com') {
            $url = "https://login.metafinx.com/admin_login/{$hashedToken}";
        } elseif ($domain === 'metafinx-admin.currenttech.pro') {
            $url = "https://metafinx-member.currenttech.pro/admin_login/{$hashedToken}";
        } else {
            return back();
        }

        return Inertia::location($url);
    }

    public function getBinaryData(Request $request, $id)
    {
        $searchUser = null;
        $searchTerm = $request->input('search');

        if ($searchTerm) {
            // dd('asdasd');
            $searchUser = User::where('name', 'like', '%' . $searchTerm . '%')
                ->orWhere('email', 'like', '%' . $searchTerm . '%')
                ->first();

            if (!$searchUser) {
                return response()->json(['error' => 'User not found for the given search term.'], 404);
            }
        }

        $user = $searchUser ?? CoinMultiLevel::with(['user:id,name,email,setting_rank_id', 'sponsor.user'])->where('user_id', $id)->first();


        $users = CoinMultiLevel::whereHas('upline', function ($query) use ($user) {
            $query->where('id', $user->id);
        })->get();

//        if ($searchUser) {
//            $query->orWhere('id', $searchUser->id);
//        }
//
//        $users = $query->get();

        $level = 0;
        $binaryData = [
            'id' => $user->id,
            'name' => $user->user->name,
            'profile_photo' => $user->user->getFirstMediaUrl('profile_photo'),
            'position' => $user->position,
            'sponsor_name' => $user->sponsor ? $user->sponsor->user->name : null,
            'sponsor_email' => $user->sponsor ? $user->sponsor->user->email : null,
            'sponsor_profile_photo' => $user->sponsor ? $user->sponsor->user->getFirstMediaUrl('profile_photo') : null,
            'email' => $user->user->email,
            'level' => $level,
            'rank' => $user->user->setting_rank_id,
            'personal_amount' => $user->coin_stacking_amount,
            'left_amount' => $this->getLeftTotalAmount($user),
            'right_amount' => $this->getRightTotalAmount($user),
            'children' => $users->map(function ($user) {
                return $this->mapBinaryUser($user, 0);
            })
        ];

        return response()->json($binaryData);
    }

    protected function mapBinaryUser($user, $level)
    {
        $children = $user->children;
        $childrenCount = count($children);

        $mappedChildren = $children->map(function ($child) use ($level) {
            return $this->mapBinaryUser($child, $level + 1);
        });

        $mappedUser = [
            'id' => $user->id,
            'name' => $user->user->name,
            'profile_photo' => $user->user->getFirstMediaUrl('profile_photo'),
            'position' => $user->position,
            'sponsor' => $user->sponsor->user->name ?? null,
            'email' => $user->user->email,
            'level' => $level + 1,
            'rank' => $user->user->setting_rank_id,
            'personal_amount' => $user->coin_stacking_amount,
            'left_amount' => $this->getLeftAmount($user),
            'right_amount' => $this->getRightAmount($user),
        ];

        // Add 'children' only if there are children
        if (!$mappedChildren->isEmpty()) {
            // Separate children into 'left' and 'right' arrays
            $leftChildren = [];
            $rightChildren = [];

            foreach ($mappedChildren as $mapChild) {
                if ($mapChild['position'] == 'left') {
                    $leftChildren[] = (object)$mapChild;
                } else {
                    $rightChildren[] = (object)$mapChild;
                }
            }

            // Handle the case of a single child
            if (count($mappedChildren) == 1) {
                if ($leftChildren) {
                    $mappedUser['children'] = [(object)$leftChildren[0], (object)null];
                } elseif ($rightChildren) {
                    $mappedUser['children'] = [(object)null, (object)$rightChildren[0]];
                }
            } else {
                // Merge 'left' and 'right' children into the 'children' array
                $mappedUser['children'] = array_merge($leftChildren, $rightChildren);
            }
        } else {
            $mappedUser['children'] = [];
        }

        return $mappedUser;
    }


    public function getAvailableDistributor(Request $request)
    {
        $existed_users_ids = CoinMultiLevel::get()->pluck('user_id');

        $users = User::query()
            ->where('role', '=', 'user')
            ->whereNotIn('id', $existed_users_ids)
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

    public function addDistributor(Request $request)
    {
        $upline = CoinMultiLevel::find($request->upline_id);
        $coinStakingPrice = CoinStacking::where('user_id', $request->user_id)->where('status', 'OnGoingPeriod')->sum('stacking_price');

        // Ensure the specified position is either 'left' or 'right'
        $position = ($request->position === 'left' || $request->position === 'right') ? $request->position : 'left';

        // Update the hierarchy list based on the upline
        if ($upline->id == 1) {
            // If the upline is the root node, the hierarchy list will be the user's ID
            $hierarchyList = '-' . $request->upline_id . '-';
        } else {
            // Otherwise, prepend the upline's hierarchy list with a '-' if it's not empty
            $hierarchyList = $upline->hierarchy_list . $upline->id . '-';
        }

        // Create the distributor with the provided parameters
        CoinMultiLevel::create([
            'user_id' => $request->user_id,
            'sponsor_id' => $request->id,
            'upline_id' => $upline->id,
            'hierarchy_list' => $hierarchyList,
            'position' => $position,
            'coin_stacking_amount' => $coinStakingPrice,
        ]);

        // Redirect back with success message
        return redirect()->back()->with('title', 'Add Distributor')->with('success', 'Distributor has been successfully added!');
    }

    protected function getLeftAmount($child)
    {
        $ids = $child->getChildrenIds();

        return CoinMultiLevel::query()
            ->whereIn('id', $ids)
            ->where('position', 'left')
            ->sum('coin_stacking_amount');
    }

    protected function getRightAmount($child)
    {
        $ids = $child->getChildrenIds();

        return CoinMultiLevel::query()
            ->whereIn('id', $ids)
            ->where('position', 'right')
            ->sum('coin_stacking_amount');
    }

    protected function getLeftTotalAmount($child)
    {
        $ids = $child->getChildrenIds();

        $leftAmount = CoinMultiLevel::query()
            ->whereIn('id', $ids)
            ->whereHas('upline', function ($query) {
                $query->where('position', 'left');
            })
            ->sum('coin_stacking_amount');

        $rightAmount = CoinMultiLevel::query()
            ->whereIn('id', $ids)
            ->whereHas('upline', function ($query) {
                $query->where('position', 'left');
            })
            ->where('position', 'right')
            ->sum('coin_stacking_amount');

        $leftPosition = $child->position;
        if ($leftPosition == 'left') {
            return $leftAmount;
        } elseif ($leftPosition == 'right') {
            return $rightAmount;
        } else {
            return $leftAmount + $rightAmount;
        }
    }
    protected function getRightTotalAmount($child)
    {
        $ids = $child->getChildrenIds();

        $leftAmount = CoinMultiLevel::query()
            ->whereIn('id', $ids)
            ->whereHas('upline', function ($query) {
                $query->where('position', 'right');
            })
            ->where('position', 'left')
            ->sum('coin_stacking_amount');

        $rightAmount = CoinMultiLevel::query()
            ->whereIn('id', $ids)
            ->whereHas('upline', function ($query) {
                $query->where('position', 'right');
            })
            ->sum('coin_stacking_amount');

        $leftPosition = $child->position;
        if ($leftPosition == 'right') {
            return $leftAmount;
        } elseif ($leftPosition == 'left') {
            return $rightAmount;
        } else {
            return $leftAmount + $rightAmount;
        }
    }

    public function getAvailableBinaryAffiliate(Request $request, $id)
    {
        $user = User::find($id);
        $existedUserIds = CoinMultiLevel::pluck('user_id');
        $childrenIds = $user->children()->pluck('id');

        $query = User::with(['coinStaking', 'media'])
            ->whereIn('id', $childrenIds)
            ->where('role', 'user')
            ->whereNotIn('id', $existedUserIds)
            ->when($request->filled('query'), function ($query) use ($request) {
                $search = $request->input('query');
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        // Transform each user to include only the specified attributes
        $transformedUsers = $query->getCollection()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'profile_photo' => $user->getFirstMediaUrl('profile_photo'),
                'coin_staking' => $user->coinStaking,
                'media' => $user->media->toArray(),
            ];
        });

        // Replace the items in the paginated results with the transformed users
        $query->setCollection($transformedUsers);

        return response()->json($query);
    }

    public function getLastChild(Request $request, $id)
    {
        $user = User::find($id);
        $position = $request->position;
        $binaryAuthUser = CoinMultiLevel::where('user_id', $user->id)->first();

        $last_child = $binaryAuthUser->getLastChild($position);
        if ($last_child) {
            $last_child->profile_photo = $last_child->user->getFirstMediaUrl('profile_photo');
        }

        return response()->json($last_child);
    }

    public function getPendingPlacementCount($id)
    {
        $user = User::find($id);
        $childrenIds = $user->children()->pluck('id')->toArray();
        $existedUsersIds = CoinMultiLevel::pluck('user_id')->toArray();

        return CoinStacking::where('auto_assign_at', '>=', now())
            ->whereDate('created_at', '>=', now()->subDay())
            ->whereIn('user_id', $childrenIds)
            ->whereNotIn('user_id', $existedUsersIds)
            ->distinct('user_id')
            ->count();
    }

    public function checkCoinStackingExistence($id)
    {
        // Check if the user exists in the coin stacking table
        $exists = CoinStacking::where('user_id', $id)->exists();

        return response()->json($exists);
    }

}
