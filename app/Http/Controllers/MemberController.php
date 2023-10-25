<?php

namespace App\Http\Controllers;

use App\Http\Requests\KycApprovalRequest;
use App\Models\SettingRank;
use App\Models\User;
use App\Models\InvestmentSubscription;
use App\Notifications\KycApprovalNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function listing()
    {
        return Inertia::render('Member/MemberListing', [
            'pendingKycCount' => User::where('kyc_approval', '=', 'pending')->count(),
            'unverifiedKycCount' => User::where('kyc_approval', '=', 'unverified')->count(),
        ]);
    }

    public function getMemberDetails(Request $request)
    {
        $members = User::query()
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

    public function addMember(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|regex:/^[a-zA-Z0-9\p{Han}. ]+$/u|max:255',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:8|unique:' . User::class,
            'email' => 'required|string|email|max:255|unique:' . User::class,
            'password' => ['required', Password::defaults()],
            'ranking' => 'required',

        ]);

        $attributes = [
            'name' => 'Name',
            'phone' => 'Phone Number',
            'email' => 'Email',
            'password' => 'Password',
            'ranking' => 'Ranking',
        ];

        $validator->setAttributeNames($attributes);
        $validatedData = $validator->validate();

        User::create([

            'name' => $validatedData['name'],
            'phone' => $validatedData['phone'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'setting_rank_id' => $validatedData['ranking'],
            'referral_code' => $request->refCode,
            'status' => 1,
            'country' => "Malaysia"
        ]);

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

    public function editMember(Request $request)
    {
        $user = User::find($request->user_id);
        if ($request->password){
            $passwordCheck = ['required', Password::defaults()];
        }
        else {
            $passwordCheck = '';
        }

        if ($user->phone = $request->phone){
            $phoneCheck = '';
        }
        else {
            $phoneCheck = 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:8|unique:' . User::class;
        }

        if ($user->email = $request->email){
            $emailCheck = '';
        }
        else {
            $emailCheck = 'required|string|email|max:255|unique:' . User::class;
        }

        $validator = Validator::make($request->all(),[
            'name' => 'required|regex:/^[a-zA-Z0-9\p{Han}. ]+$/u|max:255',
            'phone' => $phoneCheck,
            'email' => $emailCheck,
            'password' => $passwordCheck,
            'ranking' => 'required',

        ]);


        $attributes = [
            'name' => 'Name',
            'phone' => 'Phone Number',
            'email' => 'Email',
            'password' => 'Password',
            'ranking' => 'Ranking',
        ];

        $validator->setAttributeNames($attributes);
        $validatedData = $validator->validate();

        $user->fill([
            'name' => $validatedData['name'],
            'phone' => $validatedData['phone'],
            'email' => $validatedData['email'],
            'setting_rank_id' => $validatedData['ranking'],
            'status' => 1,
            'country' => "Malaysia",
        ]);
        if ($user->password !== $request->password && $request->password){
            $user->password = Hash::make($validatedData['password']);
        }

        $user->save();

        return redirect()->back();
    }

    public function viewMemberDetails($id)
    {
        $user = User::with('media')->find($id);
        $upline = User::with('media')
        ->where('id', $user->upline_id)
        ->select('name','id')
        ->first();

        $investment = InvestmentSubscription::query()
        ->with('investment_plan:id,name,investment_period')
        ->where('user_id', $id)
        ->get();

        return Inertia::render('Member/MemberDetails/MemberDetail', [
            'member_details' => $user,
            'upline_member' => $upline,
            'investments' => $investment
        ]);
    }

    public function unsubscribePlan(Request $request)
    {
        $investment_plan = InvestmentSubscription::find($request->investment_id);

        $investment_plan->delete();

        return redirect()->back();
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
}
