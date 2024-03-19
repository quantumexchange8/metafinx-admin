<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use App\Providers\RouteServiceProvider;
use Spatie\Activitylog\Models\Activity;
use App\Http\Requests\Auth\LoginRequest;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        if (\Auth::user()->role === 'sub_admin') {
            $currentTime = Carbon::now()->toDateTimeString(); 
            activity()
                ->useLog('user')
                ->performedOn(\Auth::user())
                ->causedBy(\Auth::user())
                ->withProperties(['user_info' => \Auth::user()->toArray()])
                ->log(\Auth::user()->name . ' logged in at ' . $currentTime);
            }

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if (\Auth::user()->role === 'sub_admin') {
            $currentTime = Carbon::now()->toDateTimeString(); 
            activity()
                ->useLog('user')
                ->performedOn(\Auth::user())
                ->causedBy(\Auth::user())
                ->withProperties(['user_info' => \Auth::user()->toArray()])
                ->log(\Auth::user()->name . ' logged out at ' . $currentTime);
            }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
