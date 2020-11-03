<?php

use Illuminate\Support\Facades\Route;

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
    Excel::queueImport(new \App\Imports\GeminiSitePerformanceImport, public_path('reports/1_399238543_1961466_site_performance_stats_afb02b1cec07cdc40af26da3e4b301b3189c67e735b4775330b04bf78033e9774ba8e886.csv'));
    return redirect('/home');
});

Route::get('/login/{provider}', [App\Http\Controllers\AccountController::class, 'redirectToProvider'])->name('redirect_to_provider');
Route::get('/login/{provider}/callback', [App\Http\Controllers\AccountController::class, 'handleProviderCallback'])->name('handle_provider_callback');

Auth::routes([
    'register' => false
]);

Route::group(['middleware' => 'auth', 'prefix' => 'general'], function() {
    Route::get('/languages', [App\Http\Controllers\GeneralController::class, 'languages'])->name('general.languages');
    Route::get('/countries', [App\Http\Controllers\GeneralController::class, 'countries'])->name('general.countries');
    Route::post('/preview', [App\Http\Controllers\GeneralController::class, 'preview'])->name('general.preview');
    Route::post('/upload-files', [App\Http\Controllers\GeneralController::class, 'uploadFiles'])->name('general.upload_files');
});

Route::group(['middleware' => 'auth', 'prefix' => 'campaigns'], function() {
    Route::get('/', [App\Http\Controllers\CampaignController::class, 'index'])->name('campaigns.index');
    Route::post('/', [App\Http\Controllers\CampaignController::class, 'store'])->name('campaigns.store');
    Route::post('/search', [App\Http\Controllers\CampaignController::class, 'search'])->name('campaigns.search');
    Route::get('/export-excel', [App\Http\Controllers\CampaignController::class, 'exportExcel'])->name('campaigns.exportexcel');
    Route::get('/export-csv', [App\Http\Controllers\CampaignController::class, 'exportCsv'])->name('campaigns.exportcsv');
    Route::get('/create/{campaign?}', [App\Http\Controllers\CampaignController::class, 'create'])->name('campaigns.create');
    Route::get('/edit/{campaign}', [App\Http\Controllers\CampaignController::class, 'edit'])->name('campaigns.edit');
    Route::post('/update/{campaign}', [App\Http\Controllers\CampaignController::class, 'update'])->name('campaigns.update');
    Route::post('/delete/{campaign}', [App\Http\Controllers\CampaignController::class, 'delete'])->name('campaigns.delete');
    Route::post('/status/{campaign}', [App\Http\Controllers\CampaignController::class, 'status'])->name('campaigns.status');
    Route::get('/queue', [App\Http\Controllers\CampaignController::class, 'queue'])->name('campaigns.queue');
    Route::get('/{campaign}', [App\Http\Controllers\CampaignController::class, 'show'])->name('campaigns.show');
    Route::get('/{campaign}/widgets/{start}/{end}/{tracker?}', [App\Http\Controllers\CampaignController::class, 'widgets'])->name('campaigns.widgets');
    Route::get('/{campaign}/domains', [App\Http\Controllers\CampaignController::class, 'domains'])->name('campaigns.domains');
    Route::get('/{campaign}/ad-groups/{ad_group_id}/ads/create', [App\Http\Controllers\CampaignController::class, 'createCampaignAd'])->name('campaigns.createCampaignAd');
    Route::post('/{campaign}/ad-groups/{ad_group_id}/ads/store-ad', [App\Http\Controllers\CampaignController::class, 'storeAd'])->name('campaigns.storeAd');
    Route::get('/{campaign}/ad-groups/{ad_group_id}/ads/{ad_id}', [App\Http\Controllers\CampaignController::class, 'ad'])->name('campaigns.ad');
    Route::post('/{campaign}/ad-groups/{ad_group_id}/ads/status/{ad_id}', [App\Http\Controllers\CampaignController::class, 'adStatus'])->name('campaigns.adStatus');
    Route::post('/{campaign}/ad-groups/{ad_group_id}/status', [App\Http\Controllers\CampaignController::class, 'adGroupStatus'])->name('campaigns.adGroupStatus');
    Route::post('/{campaign}/ad-groups/data', [App\Http\Controllers\CampaignController::class, 'adGroupData'])->name('campaigns.adGroupData');
});

Route::group(['middleware' => 'auth', 'prefix' => 'rules'], function() {
    Route::get('/', [App\Http\Controllers\RuleController::class, 'index'])->name('rules.index');
    Route::post('/', [App\Http\Controllers\RuleController::class, 'store'])->name('rules.store');
    Route::get('/create', [App\Http\Controllers\RuleController::class, 'create'])->name('rules.create');
    Route::post('/delete/{rule}', [App\Http\Controllers\RuleController::class, 'delete'])->name('rules.delete');
    Route::get('/data', [App\Http\Controllers\RuleController::class, 'data'])->name('rules.data');
});

Route::group(['middleware' => 'auth', 'prefix' => 'rule-groups'], function() {
    Route::post('/', [App\Http\Controllers\RuleGroupController::class, 'store'])->name('rulegroups.store');
    Route::get('/selection-data', [App\Http\Controllers\RuleGroupController::class, 'selectionData'])->name('rules.selectionData');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/account-wizard', [App\Http\Controllers\AccountController::class, 'wizard'])->name('account_wizard');
Route::get('/account/advertisers', [App\Http\Controllers\AccountController::class, 'advertisers'])->name('account_wizard');
Route::post('/account/sign-up', [App\Http\Controllers\AccountController::class, 'signUp'])->name('account_wizard');
Route::get('/traffic-sources', [App\Http\Controllers\AccountController::class, 'trafficSources'])->name('traffic_sources');
Route::get('/trackers', [App\Http\Controllers\AccountController::class, 'trackers'])->name('trackers');
