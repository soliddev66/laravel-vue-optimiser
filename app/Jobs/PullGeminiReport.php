<?php

namespace App\Jobs;

use App\Imports\GeminiAdExtensionImport;
use App\Imports\GeminiAdjustmentImport;
use App\Imports\GeminiCallExtensionImport;
use App\Imports\GeminiCampaignBidPerformanceImport;
use App\Imports\GeminiConversionRulesImport;
use App\Imports\GeminiDomainPerformanceImport;
use App\Imports\GeminiKeywordImport;
use App\Imports\GeminiPerformanceImport;
use App\Imports\GeminiProductAdPerformanceImport;
use App\Imports\GeminiProductAdsImport;
use App\Imports\GeminiSearchImport;
use App\Imports\GeminiSitePerformanceImport;
use App\Imports\GeminiSlotPerformanceImport;
use App\Imports\GeminiStructuredSnippetExtensionPerformanceImport;
use App\Jobs\DeleteCompletedImportFile;
use App\Models\Campaign;
use App\Models\GeminiJob;
use App\Models\UserProvider;
use App\Vngodev\Token;
use GuzzleHttp\Client;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullGeminiReport implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $gemini_job;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(GeminiJob $gemini_job)
    {
        $this->gemini_job = $gemini_job;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        $campaign = Campaign::where('campaign_id', $this->gemini_job->campaign_id)->where('advertiser_id', $this->gemini_job->advertiser_id)->first();
        if ($campaign) {
            $user_info = UserProvider::where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first();
            $gemini_job_status = [];
            $gemini_job_id = $this->gemini_job->job_id;
            Token::refresh($user_info, function () use ($campaign, $user_info, $gemini_job_id, &$gemini_job_status) {
                $gemini_job_status = self::getJobStatus($user_info, $gemini_job_id, $campaign->advertiser_id);
            });
            $this->gemini_job->status = $gemini_job_status['response']['status'];
            $this->gemini_job->job_id = $gemini_job_status['response']['jobId'];
            $this->gemini_job->job_response = $gemini_job_status['response']['jobResponse'];
            $this->gemini_job->save();

            if ($gemini_job_status['response']['status'] === 'completed') {
                $report_file = file_get_contents($gemini_job_status['response']['jobResponse']);
                $file_name = $this->gemini_job->user_id . '_' . $this->gemini_job->campaign_id . '_' . $this->gemini_job->advertiser_id . '_' . $this->gemini_job->name . '_' . $this->gemini_job->job_id . '.csv';
                file_put_contents(public_path('reports/' . $file_name), $report_file);
                switch ($this->gemini_job->name) {
                    case 'performance_stats':
                        (new GeminiPerformanceImport)->queue(public_path('reports/' . $file_name))->onQueue('lowest');
                        break;
                    case 'slot_performance_stats':
                        (new GeminiSlotPerformanceImport)->queue(public_path('reports/' . $file_name))->onQueue('lowest');
                        break;
                    case 'site_performance_stats':
                        (new GeminiSitePerformanceImport)->queue(public_path('reports/' . $file_name))->onQueue('lowest');
                        break;
                    case 'campaign_bid_performance_stats':
                        (new GeminiCampaignBidPerformanceImport)->queue(public_path('reports/' . $file_name))->onQueue('lowest');
                        break;
                    case 'structured_snippet_extension':
                        (new GeminiStructuredSnippetExtensionPerformanceImport)->queue(public_path('reports/' . $file_name))->onQueue('lowest');
                        break;
                    case 'product_ad_performance_stats':
                        (new GeminiProductAdPerformanceImport)->queue(public_path('reports/' . $file_name))->onQueue('lowest');
                        break;
                    case 'adjustment_stats':
                        (new GeminiAdjustmentImport)->queue(public_path('reports/' . $file_name))->onQueue('lowest');
                        break;
                    case 'keyword_stats':
                        (new GeminiKeywordImport)->queue(public_path('reports/' . $file_name))->onQueue('lowest');
                        break;
                    case 'search_stats':
                        (new GeminiSearchImport)->queue(public_path('reports/' . $file_name))->onQueue('lowest');
                        break;
                    case 'ad_extension_details':
                        (new GeminiAdExtensionImport)->queue(public_path('reports/' . $file_name))->onQueue('lowest');
                        break;
                    case 'call_extension_stats':
                        (new GeminiCallExtensionImport)->queue(public_path('reports/' . $file_name))->onQueue('lowest');
                        break;
                    case 'user_stats':
                        // WON'T DO IT!!!!
                        // Excel::queueImport(new GeminiUserImpor$file_namet, public_path('reports/' . $file_name));
                        break;
                    case 'product_ads':
                        (new GeminiProductAdsImport)->queue(public_path('reports/' . $file_name))->onQueue('lowest');
                        break;
                    case 'conversion_rules_stats':
                        (new GeminiConversionRulesImport)->queue(public_path('reports/' . $file_name))->onQueue('lowest');
                        break;
                    case 'domain_performance_stats':
                        (new GeminiDomainPerformanceImport)->queue(public_path('reports/' . $file_name))->onQueue('lowest');
                        break;
                    default:
                        break;
                }
            }
        }
    }

    private static function getJobStatus($user_info, $gemini_job_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('GET', env('BASE_URL') . '/v3/rest/reports/custom/' . $gemini_job_id . '?advertiserId=' . $advertiser_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getGeminiJob()
    {
        return $this->gemini_job;
    }
}
