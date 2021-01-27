<?php

namespace App\Jobs;

use DB;

use App\Models\Ad;
use App\Models\AdGroup;
use App\Models\Campaign;
use App\Models\GeminiAdExtensionStat;
use App\Models\GeminiAdjustmentStat;
use App\Models\GeminiCallExtensionStat;
use App\Models\GeminiCampaignBidPerformanceStat;
use App\Models\GeminiConversionRulesStat;
use App\Models\GeminiDomainPerformanceStat;
use App\Models\GeminiKeywordStat;
use App\Models\GeminiPerformanceStat;
use App\Models\GeminiProductAdPerformanceStat;
use App\Models\GeminiProductAdsStat;
use App\Models\GeminiSearchStat;
use App\Models\GeminiSitePerformanceStat;
use App\Models\GeminiSlotPerformanceStat;
use App\Models\GeminiStructuredSnippetExtensionPerformanceStat;
use App\Models\GeminiUserStat;
use App\Models\OutbrainReport;
use App\Models\RedtrackContentStat;
use App\Models\RedtrackDomainStat;
use App\Models\RedtrackPublisherStat;
use App\Models\RedtrackReport;
use App\Models\TaboolaReport;
use App\Models\TwitterReport;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Artisan;

class UnlinkTrafficSource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $provider_id;

    private $open_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($provider_id, $open_id)
    {
        $this->provider_id = $provider_id;
        $this->open_id = $open_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        do {
            $total = RedtrackReport::where('provider_id', request('providerId'))->where('open_id', request('openId'))->limit(1000)->delete();
        } while($total >= 1000);

        do {
            $total = RedtrackDomainStat::where('provider_id', request('providerId'))->where('open_id', request('openId'))->limit(1000)->delete();
        } while($total >= 1000);

        do {
            $total = RedtrackContentStat::where('provider_id', request('providerId'))->where('open_id', request('openId'))->limit(1000)->delete();
        } while($total >= 1000);

        do {
            $total = RedtrackPublisherStat::where('provider_id', request('providerId'))->where('open_id', request('openId'))->limit(1000)->delete();
        } while($total >= 1000);

        $campaign_ids = Campaign::where('provider_id', request('providerId'))->where('open_id', request('openId'))->pluck('id');

        foreach ($campaign_ids as $id) {
            do {
                $total = GeminiAdExtensionStat::where('campaign_id', $id)->limit(1000)->delete();
            } while($total >= 1000);
            do {
                $total = GeminiAdjustmentStat::where('campaign_id', $id)->limit(1000)->delete();
            } while($total >= 1000);
            do {
                $total = GeminiCallExtensionStat::where('campaign_id', $id)->limit(1000)->delete();
            } while($total >= 1000);
            do {
                $total = GeminiCampaignBidPerformanceStat::where('campaign_id', $id)->limit(1000)->delete();
            } while($total >= 1000);
            do {
                $total = GeminiConversionRulesStat::where('campaign_id', $id)->limit(1000)->delete();
            } while($total >= 1000);
            do {
                $total = GeminiDomainPerformanceStat::where('campaign_id', $id)->limit(1000)->delete();
            } while($total >= 1000);
            do {
                $total = GeminiKeywordStat::where('campaign_id', $id)->limit(1000)->delete();
            } while($total >= 1000);
            do {
                $total = GeminiPerformanceStat::where('campaign_id', $id)->limit(1000)->delete();
            } while($total >= 1000);
            do {
                $total = GeminiProductAdPerformanceStat::where('campaign_id', $id)->limit(1000)->delete();
            } while($total >= 1000);
            do {
                $total = GeminiProductAdsStat::where('campaign_id', $id)->limit(1000)->delete();
            } while($total >= 1000);
            do {
                $total = GeminiSearchStat::where('campaign_id', $id)->limit(1000)->delete();
            } while($total >= 1000);
            do {
                $total = GeminiSitePerformanceStat::where('campaign_id', $id)->limit(1000)->delete();
            } while($total >= 1000);
            do {
                $total = GeminiSlotPerformanceStat::where('campaign_id', $id)->limit(1000)->delete();
            } while($total >= 1000);
            do {
                $total = GeminiStructuredSnippetExtensionPerformanceStat::where('campaign_id', $id)->limit(1000)->delete();
            } while($total >= 1000);
            do {
                $total = GeminiUserStat::where('campaign_id', $id)->limit(1000)->delete();
            } while($total >= 1000);
            do {
                $total = OutbrainReport::where('campaign_id', $id)->limit(1000)->delete();
            } while($total >= 1000);
            do {
                $total = TaboolaReport::where('campaign_id', $id)->limit(1000)->delete();
            } while($total >= 1000);
            do {
                $total = TwitterReport::where('campaign_id', $id)->limit(1000)->delete();
            } while($total >= 1000);
        }

        AdGroup::where('provider_id', request('providerId'))->where('open_id', request('openId'))->delete();
        Ad::where('provider_id', request('providerId'))->where('open_id', request('openId'))->delete();
        Campaign::where('provider_id', request('providerId'))->where('open_id', request('openId'))->delete();

        // Restart the queue
        Artisan::call('queue:restart');
    }
}
