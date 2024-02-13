<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Setting;
use App\Models\CoinPrice;
use App\Models\SettingCoin;
use App\Models\SettingRank;
use App\Models\Announcement;
use App\Models\SettingBonus;
use Illuminate\Http\Request;
use App\Models\CoinMarketTime;
use App\Models\ConversionRate;
use App\Models\SettingEarning;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\SettingWithdrawalFee;
use App\Models\InvestmentSubscription;
use App\Http\Requests\CoinPriceRequest;
use App\Models\SettingAffiliateEarning;
use App\Http\Requests\MarketTimeRequest;
use App\Http\Requests\TicketBonusRequest;
use App\Http\Requests\AnnouncementRequest;
use App\Http\Requests\DividendBonusRequest;
use Illuminate\Validation\ValidationException;
use App\Notifications\AnnouncementNotification;

class ConfigurationController extends Controller
{
    public function index()
    {
        $coin_market_time = CoinMarketTime::latest()->first();
        $masterSettings = Setting::select('name', 'value', 'slug')
        ->whereIn('id', function ($query) {
            $query->select(DB::raw('MAX(id)'))
                ->from('settings')
                ->groupBy('name', 'slug');
        })
        ->get();

        // Check if $coin_market_time is not null to avoid errors
        if ($coin_market_time) {
            $openTime = Carbon::parse($coin_market_time->open_time);

            $coin_market_time->open_time_hr = $openTime->format('h');
            $coin_market_time->open_time_min = $openTime->format('i');
            $coin_market_time->open_time_meridiem = $openTime->format('A');

            // Similarly for close_time
            $closeTime = Carbon::parse($coin_market_time->close_time);

            $coin_market_time->close_time_hr = $closeTime->format('h');
            $coin_market_time->close_time_min = $closeTime->format('i');
            $coin_market_time->close_time_meridiem = $closeTime->format('A');
        }

        return Inertia::render('Configuration/Configuration', [
            'settingRanks' => SettingRank::select('id', 'name')->get(),
            'settingCoin' => SettingCoin::latest()->first(),
            'totalCoinSupply' => Coin::sum('unit'),
            'conversionRate' => ConversionRate::latest()->first(),
            'coinMarketTime' => $coin_market_time,
            'masterSetting' => $masterSettings,
        ]);
    }

    public function getAnnouncement(Request $request)
    {
        $announcements = Announcement::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('subject', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $date = $request->input('date');
                $dateRange = explode(' - ', $date);
                $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
                $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();
                $query->whereBetween('created_at', [$start_date, $end_date]);
            })
            ->latest()
            ->paginate(10);

        $announcements->each(function ($announcement) {
            $announcement->image = $announcement->getFirstMediaUrl('announcement');
        });

        return response()->json($announcements);
    }

    public function addAnnouncement(AnnouncementRequest $request)
    {
        $announcement = Announcement::create([
            'receiver_type' => $request->receiver_type,
            'subject' => $request->subject,
            'details' => $request->details,
        ]);

        $this->processImage($request, $announcement);

        if ($announcement->receiver_type == 'specific_member') {
            $receivers = $request->receiver;

            $specific_members = [];
            foreach ($receivers as $receiver) {
                $specific_members[] = $receiver['value'];
            }

            $announcement->update([
                'receiver' => $specific_members,
            ]);

            // Notify specific users
            $users = User::whereIn('id', $specific_members)->get();
            \Notification::send($users, new AnnouncementNotification($announcement));
        } elseif ($announcement->receiver_type == 'everyone') {
            // Notify all users
            $users = User::all();
            \Notification::send($users, new AnnouncementNotification($announcement));
        }

        return redirect()->back()->with('title', 'Announcement uploaded')->with('success', 'This announcement has been uploaded successfully.');
    }

    public function upload(Request $request)
    {

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            $originalFilename = $file->getClientOriginalName();
            return $file->storeAs('uploads/announcement', $originalFilename, 'public');
        }

        return '';
    }

    public function image_revert(Request $request)
    {

        if ($image = $request->get('image')) {

            $path = storage_path('/app/public/' . $image);
            if (file_exists($path)) {
                unlink($path);
            }
        }

    }

    protected function processImage(Request $request, $announcement): void
    {
        if ($image = $request->get('image')) {
            $path = storage_path('/app/public/' . $image);
            if (file_exists($path)) {
                $announcement->clearMediaCollection('announcement');
                $announcement->addMedia($path)->toMediaCollection('announcement');
            }
        }
    }

    public function addDividendBonus(DividendBonusRequest $request)
    {
        $existingDate = SettingBonus::where('release_date', $request->date)->first();

        if ($existingDate) {
            throw ValidationException::withMessages(['date' => ['Release date already exists']]);
        }

        $settingBonus = SettingBonus::create([
            'name' => 'Dividend Bonus',
            'type' => 'dividend_bonuses',
            'amount' => $request->amount,
            'release_date' => $request->date,
        ]);

        return redirect()->back()->with('title', 'Dividend Bonus')->with('success', 'A dividend bonus of $ ' . $request->amount . ' will be released on ' . $request->date . '.');
    }

    public function editDividendBonus(DividendBonusRequest $request, $id)
    {
        $existingDividendBonus = SettingBonus::findOrFail($id);

        $existingDate = SettingBonus::where('release_date', $request->date)
        ->where('id', '!=', $id)
        ->first();

        if ($existingDate) {
            throw ValidationException::withMessages(['date' => ['Release date already exists']]);
        }

        $existingDividendBonus->update([
            'amount' => $request->amount,
            'release_date' => $request->date,
        ]);

        return redirect()->back()->with('title', 'Dividend Bonus Updated')->with('success', 'The dividend bonus has been updated successfully.');
    }

    public function editMasterSetting(TicketBonusRequest $request)
    {
        $symbol = '';

        if ($request->slug == 'withdrawal-fee')
        {
            $symbol = '$ ';
        }if ($request->slug == 'gas-fee' || 'stacking-fee') {
            $symbol = '% ';
        }

        Setting::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'value' => $request->value,
            'updated_by' => \Auth::id(),
        ]);

        $formattedValue = ($request->slug == 'gas-fee' || $request->slug == 'stacking-fee')
        ? $request->value . $symbol
        : $symbol . ' ' . $request->value;

        return redirect()
            ->back()
            ->with('title', $request->name)
            ->with('success', $request->name . ' ' . $formattedValue . ' has been updated successfully.');
    }

    public function editCoinPrice(CoinPriceRequest $request, $id)
    {
        $existingCoinPrice = CoinPrice::findOrFail($id);

        $existingDate = CoinPrice::where('price_date', $request->price_date)
        ->where('id', '!=', $id)
        ->first();

        if ($existingDate) {
            throw ValidationException::withMessages(['date' => ['Release date already exists']]);
        }

        $existingCoinPrice->update([
            'price' => $request->price,
            'price_date' => $request->date,
        ]);

        return redirect()->back()->with('title', 'Coin Price Updated')->with('success', 'The coin price has been updated successfully.');
    }


    public function getDividendBonus(Request $request)
    {
        $dividends = SettingBonus::query()
            ->where('type', 'dividend_bonuses')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('amount', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $date = $request->input('date');
                $dateRange = explode(' - ', $date);
                $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
                $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();
                $query->where(function ($innerQuery) use ($start_date, $end_date) {
                    $innerQuery->whereBetween('created_at', [$start_date, $end_date])
                        ->orWhereBetween('release_date', [$start_date, $end_date]);
                });
            })
            ->latest()
            ->paginate(10);

            $totalMemberCount = InvestmentSubscription::distinct('user_id')->count();

            $dividends->transform(function ($dividend) use ($totalMemberCount) {
                $dividend->total_Member = $totalMemberCount;
                return $dividend;
            });

        return response()->json($dividends);
    }

    public function getMasterSetting(Request $request)
    {
        $settings = Setting::query()
            ->with('user:id,name')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('slug', 'like', '%' . $search . '%')
                ->orWhere('value', 'like', '%' . $search . '%');
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $date = $request->input('date');
                $dateRange = explode(' - ', $date);
                $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
                $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();
                $query->whereBetween('created_at', [$start_date, $end_date]);
            })
            ->latest()
            ->paginate(10);

        return response()->json($settings);
    }


    public function getSettingRank(Request $request)
    {
        $rank_id = $request->rank_id;

        $settingRank = SettingRank::find($rank_id);

        $referralEarning = SettingEarning::where('setting_rank_id', $rank_id)->where('type', 'ReferralEarning')->first();
        $dividendEarning = SettingEarning::where('setting_rank_id', $rank_id)->where('type', 'DividendEarning')->get();
        $affiliateSettings = SettingAffiliateEarning::where('setting_rank_id', $rank_id)->get();

        return response()->json([
            'settingRank' => $settingRank,
            'referralEarning' => $referralEarning,
            'dividendEarning' => $dividendEarning,
            'affiliateSettings' => $affiliateSettings,
        ]);
    }

    public function affiliateSetting(Request $request)
    {
        $settingRank = SettingRank::find($request->id);

        $settingRank->update([
            'self_deposit' => $request->self_deposit,
            'valid_direct_referral' => $request->valid_direct_referral,
            'valid_affiliate_deposit' => $request->valid_affiliate_deposit,
            'capping_per_line' => $request->capping_per_line,
        ]);

        $settingEarnings = SettingEarning::where('setting_rank_id', $request->id)->get();

        $dividendEarnings = $request->DividendEarning;
        foreach ($settingEarnings as $earning) {
            if ($earning->type == 'DividendEarning' && !empty($dividendEarnings)) {
                $earning->delete();
            } elseif ($earning->type == 'ReferralEarning') {
                $earning->update([
                    'value' => $request->ReferralEarning
                ]);
            }
        }

        foreach ($dividendEarnings as $dividendEarning) {
            SettingEarning::create([
                'setting_rank_id' => $request->id,
                'name' => 'Dividend Earnings',
                'type' => 'DividendEarning',
                'value' => $dividendEarning
            ]);
        }

        $settingAffliateEarnings = SettingAffiliateEarning::where('setting_rank_id', $request->id)->get();

        $affiliateSettings = $request->affiliate_settings;
        if (!empty($request->affiliate_settings)) {

            foreach ($settingAffliateEarnings as $affliateEarning) {
                $affliateEarning->delete();
            }

            foreach ($affiliateSettings as $index => $affiliateSetting) {
                SettingAffiliateEarning::create([
                    'setting_rank_id' => $request->id,
                    'name' => 'L' . $index + 1,
                    'value' => $affiliateSetting
                ]);
            }
        }

        return redirect()->back()->with('title', 'Setting Updated')->with('success', 'This Rank Setting has been updated successfully.');
    }

    public function getCoinSetting(Request $request)
    {
        $coin_prices = CoinPrice::query()
            ->with('user:id,name')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%');
                    });
                });
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $date = $request->input('date');
                $dateRange = explode(' - ', $date);
                $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
                $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();
                $query->whereBetween('price_date', [$start_date, $end_date]);
            })
            ->orderByDesc('price_date')
            ->paginate(10);

        return response()->json($coin_prices);
    }

    public function getDays()
    {
        $days = [];
        for ($i = 1; $i <= 7; $i++) {
            $days[] = [
                'value' => $i,
                'label' => date('l', strtotime("Sunday +{$i} days")),
            ];
        }

        return response()->json($days);
    }

    public function updateCoinPrice(CoinPriceRequest $request)
    {
        $price_date = CoinPrice::orderByDesc('price_date')->first();
        $current_conversion_rate = ConversionRate::latest()->first();

        $date = now()->parse($price_date->price_date)->addDays(2);
        $expectedNextDate = date_format($date, 'Y-m-d');

        // Check if the provided date is not the expected next date
        if ($request->date > $expectedNextDate) {
            throw ValidationException::withMessages([
                'date' => "Please fill in the missing date '{$expectedNextDate}'.",
            ]);
        } elseif ($request->date < $expectedNextDate) {
            throw ValidationException::withMessages([
                'date' => "Cannot fill in a previous date. Expected date '{$expectedNextDate}'.",
            ]);
        }

        CoinPrice::create([
            'setting_coin_id' => $request->setting_coin_id,
            'updated_by' => \Auth::id(),
            'price' => $request->price,
            'price_date' => $request->date,
        ]);

        if ($request->conversion_rate != $current_conversion_rate->price) {
            ConversionRate::create([
                'currency' => 'MYR',
                'price' => $request->conversion_rate
            ]);
        }

        return redirect()->back()->with('title', 'Setting Updated')->with('success', 'The MXT Coin price and date has been updated successfully.');
    }

    public function updateCoinMarketTime(MarketTimeRequest $request)
    {
        $marketTime = CoinMarketTime::find($request->market_time_id);
        $openTimeString = $request->open_time_hr . ':' . $request->open_time_min . ' ' . $request->open_time_meridiem;
        $openTime = Carbon::parse($openTimeString);

        $closeTimeString = $request->close_time_hr . ':' . $request->close_time_min . ' ' . $request->close_time_meridiem;
        $closeTime = Carbon::parse($closeTimeString);

        // Check if all days from 1 to 7 are present in the 'frequency' array
        $allDaysPresent = collect(range(1, 7))
            ->every(function ($day) use ($request) {
                return in_array((string) $day, array_column($request->frequency, 'value'));
            });

        // Check if values 6 and 7 are missing in the 'frequency' array
        $missingDays67 = collect(['6', '7'])
            ->diff(array_column($request->frequency, 'value'))
            ->isEmpty();

        // Determine the 'frequency_type' based on the conditions
        if ($allDaysPresent) {
            $frequencyType = 'daily';
        } elseif ($missingDays67) {
            $frequencyType = 'weekday';
        } else {
            $frequencyType = 'selected_days';
        }

        $marketTime->update([
            'open_time' => $openTime,
            'close_time' => $closeTime,
            'frequency_type' => $frequencyType,
            'frequency' => $request->frequency
        ]);

        return redirect()->back()->with('title', 'Market Time Updated')->with('success', 'The MXT Coin market time has been updated successfully.');
    }
}
