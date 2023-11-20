<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementRequest;
use App\Http\Requests\DividendBonusRequest;
use App\Http\Requests\TicketBonusRequest;
use App\Models\Announcement;
use App\Models\User;
use App\Models\SettingBonus;
use App\Notifications\AnnouncementNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class ConfigurationController extends Controller
{
    public function index()
    {
        return Inertia::render('Configuration/Configuration');
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
        $settingBonus = SettingBonus::create([
            'name' => 'Dividend Bonus',
            'type' => 'dividend_bonuses',
            'amount' => $request->amount,
            'release_date' => $request->date,
        ]);

        return redirect()->back()->with('title', 'Dividend Bonus')->with('success', 'A dividend bonus of $ ' . $request->amount . ' will be released on ' . $request->date . '.');
    }

    public function addTicketBonus(TicketBonusRequest $request)
    {
        $settingBonus = SettingBonus::create([
            'name' => 'Ticket Bonus',
            'type' => 'ticket_bonuses',
            'amount' => $request->amount,
            'release_date' => $request->date,
        ]);

        return redirect()->back()->with('title', 'Ticket Bonus')->with('success', 'A ticket bonus of $ ' . $request->amount . ' will be released on ' . $request->date . '.');
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

        return response()->json($dividends);
    }

    public function getTicketBonus(Request $request)
    {
        $tickets = SettingBonus::query()
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
                $query->whereBetween('created_at', [$start_date, $end_date])
                ->orwhereBetween('release_date', [$start_date, $end_date]);
            })
            ->where('type', 'ticket_bonuses')
            ->latest()
            ->paginate(10);

        return response()->json($tickets);
    }
}
