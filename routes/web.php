<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IpoSchemeController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\TransactionController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/getTotalMembers', [DashboardController::class, 'getTotalMembers'])->name('getTotalMembers');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /**
     * ==============================
     *        Member Listing
     * ==============================
     */
    Route::prefix('member')->group(function () {
        Route::get('/listing', [MemberController::class, 'listing'])->name('member.member_listing');
        Route::get('/getMemberDetails/{id}', [MemberController::class, 'getMemberDetails'])->name('member.getMemberDetails');

        // Route::delete('/delete_member', [MemberController::class, 'delete_member'])->name('member.delete_member');
    });

    /**
     * ==============================
     *       IPO Scheme Setting
     * ==============================
     */
    Route::prefix('ipo_scheme')->group(function () {
        Route::get('/setting', [IpoSchemeController::class, 'setting'])->name('ipo_scheme.setting');
        Route::get('/getSubscriptionDetails', [IpoSchemeController::class, 'getSubscriptionDetails'])->name('ipo_scheme_setting.getSubscriptionDetails');
        Route::get('/getSelectedPlans', [IpoSchemeController::class, 'getSelectedPlans'])->name('ipo_scheme_setting.getSelectedPlans');
        Route::post('/updateStatus', [IpoSchemeController::class, 'updateStatus'])->name('ipo_scheme_setting.updateStatus');
        Route::post('/addInvestmentPlan', [IpoSchemeController::class, 'addInvestmentPlan'])->name('ipo_scheme_setting.addInvestmentPlan');
        Route::post('/editInvestmentPlan', [IpoSchemeController::class, 'editInvestmentPlan'])->name('ipo_scheme_setting.editInvestmentPlan');
    });

    /**
     * ==============================
     *          Transaction
     * ==============================
     */
    Route::prefix('transaction')->group(function () {
        Route::get('/listing', [TransactionController::class, 'index'])->name('transaction.listing');
        Route::get('/getPendingTransaction/{type}', [TransactionController::class, 'getPendingTransaction'])->name('transaction.getPendingTransaction');
        Route::post('/approveTransaction', [TransactionController::class, 'approveTransaction'])->name('transaction.approveTransaction');
        Route::post('/rejectTransaction', [TransactionController::class, 'rejectTransaction'])->name('transaction.rejectTransaction');
        Route::get('/getTransactionHistory/{type}', [TransactionController::class, 'getTransactionHistory'])->name('transaction.getTransactionHistory');
    });
});

Route::get('/components/buttons', function () {
    return Inertia::render('Components/Buttons');
})->middleware(['auth', 'verified'])->name('components.buttons');

require __DIR__ . '/auth.php';
