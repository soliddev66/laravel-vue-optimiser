<?php

namespace App\Vngodev;

use App\Jobs\PullGeminiReports;
use App\Jobs\SubmitGeminiJobs;
use App\Models\Campaign;
use App\Models\GeminiJob;
use App\Models\UserProvider;
use App\Vngodev\Token;
use Carbon\Carbon;
use GuzzleHttp\Client;

/**
 * Gemini
 */
class Gemini
{
    public function __construct()
    {
        //
    }

    public static function crawl()
    {
        $date = Carbon::now()->subDay()->format('Y-m-d');
        Campaign::where('provider_id', 1)->whereNotIn('campaign_id', GeminiJob::where('status', '!=', 'completed')->groupBy('campaign_id')->pluck('campaign_id'))->chunk(10, function ($campaigns) use ($date) {
            foreach ($campaigns as $key => $campaign) {
                SubmitGeminiJobs::dispatch($campaign, $date)->onQueue('high');
            }
        });
    }

    public static function crawlRange($start_date, $end_date)
    {
        Campaign::where('provider_id', 1)->whereNotIn('campaign_id', GeminiJob::where('status', '!=', 'completed')->groupBy('campaign_id')->pluck('campaign_id'))->chunk(10, function ($campaigns) use ($start_date, $end_date) {
            foreach ($campaigns as $key => $campaign) {
                SubmitGeminiJobs::dispatch($campaign, $start_date, $end_date)->onQueue('high');
            }
        });
    }

    public static function checkJobs()
    {
        PullGeminiReports::dispatch()->onQueue('highest');
    }
}
