<?php

namespace App\Http\Controllers;

use App\Models\SettingRank;
use App\Models\User;
use App\Models\InvestmentSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function listing()
    {
        $settingRanks = SettingRank::select('id', 'name')->get();
        return Inertia::render('Member/MemberListing', [
            'settingRanks' => $settingRanks
        ]);
    }

    public function getMemberDetails(Request $request, $settingRankId)
    {
        $members = User::query()
            ->where('setting_rank_id', $settingRankId)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $date = $request->input('date');
                $dateRange = explode(' - ', $date);
                $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
                $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();
                $query->whereBetween('created_at', [$start_date, $end_date]);
            })
            ->with(['media'])
            ->withSum('wallets', 'balance')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $members->getCollection()->transform(function ($member) {
            $member->total_children = count($member->getChildrenIds());
            return $member;
        });

        return response()->json($members);
    }
    
    public function addMember(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|regex:/^[a-zA-Z0-9\p{Han}. ]+$/u|max:255',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:8|unique:' . User::class,
            'email' => 'required|string|email|max:255|unique:' . User::class,
            'password' => ['required', Password::defaults()],
            'ranking' => 'required',
        
        ]);

        User::create([
            
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'setting_rank_id' => $validated['ranking'],
            'referral_code' => $request->refCode,
            'status' => 1,
            'country' => "Malaysia"
        ]);

        // User::create([
            
        //     'name' => $request->name,
        //     'phone' => $request->phone,
        //     'email' => $request->email,
        //     'password' => Hash::make($request->password),
        //     'setting_rank_id' => $request->rankID,
        //     'referral_code' => $request->refCode,
        //     'status' => 1,
        //     'country' => "Malaysia"
        // ]);
        
        return redirect()->back()->with('title', 'New member added!')->with('success', 'The new member has been added successfully.');
    }

    public function deleteMember(Request $request)
    {  
        $user = User::find($request->user_id);
        
        $user->delete();

        return redirect()->back();
    }

    public function verifyMember(Request $request)
    {  
        $user = User::find($request->user_id);
        
        $user->kyc_approval = "approved";

        $user->save();

        return redirect()->back()->with('title', 'Member verified!')->with('success', 'The member has been verified successfully.');
    }
    
    public function editMember(Request $request)
    {  
        // $validatedData = $request->validate([
        //     'name' => 'required|regex:/^[a-zA-Z0-9\p{Han}. ]+$/u|max:255',
        //     'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:8|unique:' . User::class,
        //     'email' => 'required|string|email|max:255|unique:' . User::class,
        //     'password' => ['required', Password::defaults()],
        //     'ranking' => 'required',
        // ]);

        // $user = new User();

        // $user->fill([
        //     'name' => $validatedData['name'],
        //     'phone' => $validatedData['phone'],
        //     'email' => $validatedData['email'],
        //     'password' => Hash::make($validatedData['password']),
        //     'setting_rank_id' => $validatedData['rankID'],
        //     'status' => 1,
        //     'country' => "Malaysia",
        // ]);

        // $user->save();

        $user = User::find($request->user_id);
        
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->email = $request->email;
        // $user->password = Hash::make($request->password);
        $user->setting_rank_id = $request->rankID;

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
}
