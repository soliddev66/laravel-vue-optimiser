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
        SubmitGeminiJobs::dispatch()->onQueue('highest');
    }

    public static function checkJobs()
    {
        PullGeminiReports::dispatch()->onQueue('high');
    }
}
