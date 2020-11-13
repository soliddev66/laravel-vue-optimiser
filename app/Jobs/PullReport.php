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
use App\Imports\GeminiUserImport;
use App\Models\Campaign;
use App\Models\GeminiJob;
use App\Models\User;
use App\Vngodev\Token;
use Excel;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $db_job;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($job)
    {
        $this->db_job = GeminiJob::find($job->id);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $campaign = Campaign::where('campaign_id', $this->db_job->campaign_id)->first();
        $user = User::find($this->db_job->user_id);
        $user_info = $user->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first();
        $job_status = [];
        $job_id = $this->db_job->job_id;
        Token::refresh($user_info, function () use ($campaign, $user_info, $job_id, &$job_status) {
            $job_status = self::getJobStatus($user_info, $job_id, $campaign->advertiser_id);
        });

        $this->db_job->status = $job_status['response']['status'];
        $this->db_job->job_response = $job_status['response']['jobResponse'];
        $this->db_job->save();

        if ($this->db_job->status === 'completed') {
            $report_file = file_get_contents($this->db_job->job_response);
            $file_name = $this->db_job->user_id . '_' . $this->db_job->campaign_id . '_' . $this->db_job->advertiser_id . '_' . $this->db_job->name . '_' . $this->db_job->job_id . '.csv';
            file_put_contents(public_path('reports/' . $file_name), $report_file);
            switch ($this->db_job->name) {
                case 'performance_stats':
                    Excel::queueImport(new GeminiPerformanceImport, public_path('reports/' . $file_name));
                    break;
                case 'slot_performance_stats':
                    Excel::queueImport(new GeminiSlotPerformanceImport, public_path('reports/' . $file_name));
                    break;
                case 'site_performance_stats':
                    Excel::queueImport(new GeminiSitePerformanceImport, public_path('reports/' . $file_name));
                    break;
                case 'campaign_bid_performance_stats':
                    Excel::queueImport(new GeminiCampaignBidPerformanceImport, public_path('reports/' . $file_name));
                    break;
                case 'structured_snippet_extension':
                    Excel::queueImport(new GeminiStructuredSnippetExtensionPerformanceImport, public_path('reports/' . $file_name));
                    break;
                case 'product_ad_performance_stats':
                    Excel::queueImport(new GeminiProductAdPerformanceImport, public_path('reports/' . $file_name));
                    break;
                case 'adjustment_stats':
                    Excel::queueImport(new GeminiAdjustmentImport, public_path('reports/' . $file_name));
                    break;
                case 'keyword_stats':
                    Excel::queueImport(new GeminiKeywordImport, public_path('reports/' . $file_name));
                    break;
                case 'search_stats':
                    Excel::queueImport(new GeminiSearchImport, public_path('reports/' . $file_name));
                    break;
                case 'ad_extension_details':
                    Excel::queueImport(new GeminiAdExtensionImport, public_path('reports/' . $file_name));
                    break;
                case 'call_extension_stats':
                    Excel::queueImport(new GeminiCallExtensionImport, public_path('reports/' . $file_name));
                    break;
                case 'user_stats':
                    // WON'T DO IT!!!!
                    // Excel::queueImport(new GeminiUserImport, public_path('reports/' . $file_name));
                    break;
                case 'product_ads':
                    Excel::queueImport(new GeminiProductAdsImport, public_path('reports/' . $file_name));
                    break;
                case 'conversion_rules_stats':
                    Excel::queueImport(new GeminiConversionRulesImport, public_path('reports/' . $file_name));
                    break;
                case 'domain_performance_stats':
                    Excel::queueImport(new GeminiDomainPerformanceImport, public_path('reports/' . $file_name));
                    break;
                default:
                    break;
            }
            $this->db_job->delete();
        }
    }

    private static function getJobStatus($user_info, $job_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('GET', env('BASE_URL') . '/v3/rest/reports/custom/' . $job_id . '?advertiserId=' . $advertiser_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }
}
