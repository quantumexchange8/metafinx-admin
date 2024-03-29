<?php

use App\Models\Role;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\MXTController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IpoSchemeController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ConfigurationController;

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

$roles = Role::allUniqueNames();
$rolesString = implode('|', $roles);

Route::middleware(['auth', 'role:'.$rolesString])->group(function () {
    // Route::get('/xlc_setting', function () {
    //     return Inertia::render('XLCSetting/XLCSetting');
    // });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/getTotalMembers', [DashboardController::class, 'getTotalMembers'])->name('getTotalMembers');
    Route::get('/getTotalMembersByDays', [DashboardController::class, 'getTotalMembersByDays'])->name('getTotalMembersByDays');
    Route::get('/getTotalTransactionByDays', [DashboardController::class, 'getTotalTransactionByDays'])->name('getTotalTransactionByDays');
    Route::get('/getTotalTransactionByMonths', [DashboardController::class, 'getTotalTransactionByMonths'])->name('getTotalTransactionByMonths');
    Route::get('/getTotalWalletBalanceByDays', [DashboardController::class, 'getTotalWalletBalanceByDays'])->name('getTotalWalletBalanceByDays');
    Route::get('/getTotalWalletBalanceByMonths', [DashboardController::class, 'getTotalWalletBalanceByMonths'])->name('getTotalWalletBalanceByMonths');
    Route::get('/getTotalInvestmentByDays', [DashboardController::class, 'getTotalInvestmentByDays'])->name('getTotalInvestmentByDays');
    Route::get('/getTotalInvestmentByMonths', [DashboardController::class, 'getTotalInvestmentByMonths'])->name('getTotalInvestmentByMonths');
    Route::get('/getPendingKyc', [DashboardController::class, 'getPendingKyc'])->name('getPendingKyc');

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
        Route::get('/getMemberDetails', [MemberController::class, 'getMemberDetails'])->name('member.getMemberDetails');

        Route::post('/addMember', [MemberController::class, 'addMember'])->name('member.addMember');
        Route::get('/member_details/{id}', [MemberController::class, 'viewMemberDetails'])->name('member.viewMemberDetails');
        Route::get('/member_details_type/{type}', [MemberController::class, 'viewMemberDetailsStatus'])->name('member.viewMemberDetailsStatus');
        Route::get('/getMemberInformation/{id}', [MemberController::class, 'getMemberInformation'])->name('member.getMemberInformation');
        Route::get('/member_affiliates/{id}', [MemberController::class, 'affiliate_tree'])->name('member.affiliate_tree');
        Route::get('/getTreeData/{id}', [MemberController::class, 'getTreeData'])->name('member.getTreeData');
        Route::get('/getAllUsers', [MemberController::class, 'getAllUsers'])->name('member.getAllUsers');
        Route::delete('/deleteMember', [MemberController::class, 'deleteMember'])->name('member.deleteMember');
        Route::post('/verifyMember', [MemberController::class, 'verifyMember'])->name('member.verify_member');
        Route::patch('/editMember', [MemberController::class, 'editMember'])->name('member.edit_member');
        Route::delete('/unsubscribePlan', [MemberController::class, 'unsubscribePlan'])->name('member.unsubscribe_plan');
        Route::post('/wallet_adjustment', [MemberController::class, 'wallet_adjustment'])->name('member.wallet_adjustment');
        Route::post('/internal_transfer', [MemberController::class, 'internal_transfer'])->name('member.internal_transfer');
        Route::post('/coin_adjustment', [MemberController::class, 'coin_adjustment'])->name('member.coin_adjustment');

        Route::get('/impersonate/{user}', [MemberController::class, 'impersonate'])->name('member.impersonate');

        Route::get('getBinaryData/{id}', [MemberController::class, 'getBinaryData'])->name('member.getBinaryData');
        Route::get('getAvailableDistributor', [MemberController::class, 'getAvailableDistributor'])->name('member.getAvailableDistributor');
        Route::get('getAvailableBinaryAffiliate/{id}', [MemberController::class, 'getAvailableBinaryAffiliate'])->name('member.getAvailableBinaryAffiliate');
        Route::post('addDistributor', [MemberController::class, 'addDistributor'])->name('member.addDistributor');
        Route::get('getLastChild/{id}', [MemberController::class, 'getLastChild'])->name('member.getLastChild');
        Route::get('getPendingPlacementCount/{id}', [MemberController::class, 'getPendingPlacementCount'])->name('member.getPendingPlacementCount');
        Route::get('checkCoinStackingExistence/{id}', [MemberController::class, 'checkCoinStackingExistence'])->name('member.checkCoinStackingExistence');
        Route::get('getDistributorDetail', [MemberController::class, 'getDistributorDetail'])->name('affiliate.getDistributorDetail');
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
        Route::get('/getPendingSubscription', [IpoSchemeController::class, 'getPendingSubscription'])->name('ipo_scheme_setting.getPendingSubscription');
        Route::post('/approveEbmi', [IpoSchemeController::class, 'approveEbmi'])->name('ipo_scheme_setting.approveEbmi');
        Route::post('/updateStatus', [IpoSchemeController::class, 'updateStatus'])->name('ipo_scheme_setting.updateStatus');
        Route::post('/addInvestmentPlan', [IpoSchemeController::class, 'addInvestmentPlan'])->name('ipo_scheme_setting.addInvestmentPlan');
        Route::post('/editInvestmentPlan', [IpoSchemeController::class, 'editInvestmentPlan'])->name('ipo_scheme_setting.editInvestmentPlan');
    });

    /**
     * ==============================
     *       MXT Setting
     * ==============================
     */
     Route::prefix('mxt_setting')->group(function () {
        Route::get('/mxt_setting', [MXTController::class, 'mxt_setting'])->name('mxt.setting');
        Route::get('/getCoinPaymentDetails', [MXTController::class, 'getCoinPaymentDetails'])->name('mxt.getCoinPaymentDetails');
        Route::get('/getStackingDetails', [MXTController::class, 'getStackingDetails'])->name('mxt.getStackingDetails');
        Route::get('/getTotalMXTCoinByDays', [MXTController::class, 'getTotalMXTCoinByDays'])->name('mxt.getTotalMXTCoinByDays');
        Route::get('/getTotalMXTCoinByMonth', [MXTController::class, 'getTotalMXTCoinByMonth'])->name('mxt.getTotalMXTCoinByMonth');

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
        Route::get('/getBalanceHistory/{type}', [TransactionController::class, 'getBalanceHistory'])->name('transaction.getBalanceHistory');
    });

    /**
     * ==============================
     *         Configuration
     * ==============================
     */
    Route::prefix('configuration')->group(function () {
        Route::get('/index', [ConfigurationController::class, 'index'])->name('configuration.index');

        //Announcement
        Route::get('/getAnnouncement', [ConfigurationController::class, 'getAnnouncement'])->name('configuration.getAnnouncement');
        Route::post('/addAnnouncement', [ConfigurationController::class, 'addAnnouncement'])->name('configuration.addAnnouncement');
        Route::post('/upload/tmp_img', [ConfigurationController::class, 'upload']);
        Route::post('/upload/image-revert', [ConfigurationController::class, 'image_revert']);
        //T&C Setting
        Route::get('/getTnCSetting', [ConfigurationController::class, 'getTnCSetting'])->name('configuration.getTnCSetting');
        Route::post('/addTnCSetting', [ConfigurationController::class, 'addTnCSetting'])->name('configuration.addTnCSetting');
        Route::put('/editTnCSetting/{id}', [ConfigurationController::class, 'editTnCSetting'])->name('configuration.editTnCSetting');
        //Dividend Bonus
        Route::post('/addDividendBonus', [ConfigurationController::class, 'addDividendBonus'])->name('configuration.addDividendBonus');
        Route::put('/editDividendBonus/{id}', [ConfigurationController::class, 'editDividendBonus'])->name('configuration.editDividendBonus');
        Route::get('/getDividendBonus', [ConfigurationController::class, 'getDividendBonus'])->name('configuration.getDividendBonus');
        //Master Setting
        Route::post('/editMasterSetting', [ConfigurationController::class, 'editMasterSetting'])->name('configuration.editMasterSetting');
        Route::get('/getMasterSetting', [ConfigurationController::class, 'getMasterSetting'])->name('configuration.getMasterSetting');
        //Affiliate Setting
        Route::get('/getSettingRank', [ConfigurationController::class, 'getSettingRank'])->name('configuration.getSettingRank');
        Route::post('/affiliateSetting', [ConfigurationController::class, 'affiliateSetting'])->name('configuration.affiliateSetting');
        //Coin Setting
        Route::get('/getCoinSetting', [ConfigurationController::class, 'getCoinSetting'])->name('configuration.getCoinSetting');
        Route::get('/getDays', [ConfigurationController::class, 'getDays'])->name('configuration.getDays');
        Route::post('/updateCoinPrice', [ConfigurationController::class, 'updateCoinPrice'])->name('configuration.updateCoinPrice');
        Route::post('/updateCoinMarketTime', [ConfigurationController::class, 'updateCoinMarketTime'])->name('configuration.updateCoinMarketTime');
        Route::put('/editCoinPrice/{id}', [ConfigurationController::class, 'editCoinPrice'])->name('configuration.editCoinPrice');
        //Staking Reward
        Route::get('/getStakingReward', [ConfigurationController::class, 'getStakingReward'])->name('configuration.getStakingReward');
        Route::post('/addStakingReward', [ConfigurationController::class, 'addStakingReward'])->name('configuration.addStakingReward');
        Route::put('/editStakingReward/{id}', [ConfigurationController::class, 'editStakingReward'])->name('configuration.editStakingReward');
    });

    /**
     * ==============================
     *          Admin User
     * ==============================
     */
    Route::prefix('admin_user')->group(function () {
        Route::get('/admin_listing', [AdminUserController::class, 'admin_listing'])->name('admin_user.admin_listing');
        Route::get('/add_sub_admin', [AdminUserController::class, 'add_sub_admin'])->name('admin_user.add_sub_admin');
        Route::get('/edit_sub_admin/{id}', [AdminUserController::class, 'edit_sub_admin'])->name('admin_user.edit_sub_admin');
        Route::post('/addSubAdmin', [AdminUserController::class, 'addSubAdmin'])->name('admin_user.addSubAdmin');
        Route::post('/editSubAdmin', [AdminUserController::class, 'editSubAdmin'])->name('admin_user.editSubAdmin');
        Route::delete('/deleteSubAdmin', [AdminUserController::class, 'deleteSubAdmin'])->name('admin_user.deleteSubAdmin');
    });

    /**
     * ========================
     *          Report
     * ========================
     */
    Route::prefix('report')->group(function () {
        Route::get('/view', [ReportController::class, 'index'])->name('report.view');
        Route::get('/getPayoutDetails', [ReportController::class, 'getPayoutDetails'])->name('report.getPayoutDetails');
        Route::get('/getMonthlyReturnPayoutDetails', [ReportController::class, 'getMonthlyReturnPayoutDetails'])->name('report.getMonthlyReturnPayoutDetails');
        Route::get('/getAffiliateEarningPayoutDetails', [ReportController::class, 'getAffiliateEarningPayoutDetails'])->name('report.getAffiliateEarningPayoutDetails');
        Route::get('/getDividendEarningPayoutDetails', [ReportController::class, 'getDividendEarningPayoutDetails'])->name('report.getDividendEarningPayoutDetails');

        Route::get('/getTotalPayoutByDays/{type}/{category}', [ReportController::class, 'getTotalPayoutByDays'])->name('report.getTotalPayoutByDays');
        Route::get('/getTotalPayoutByMonths/{type}/{category}', [ReportController::class, 'getTotalPayoutByMonths'])->name('report.getTotalPayoutByMonths');

        Route::get('/getEarningPayoutDetails/{type}/{category}', [ReportController::class, 'getEarningPayoutDetails'])->name('report.getEarningPayoutDetails');
    });
});

Route::get('/components/buttons', function () {
    return Inertia::render('Components/Buttons');
})->middleware(['auth', 'verified'])->name('components.buttons');

require __DIR__ . '/auth.php';
