<?php

namespace App\Endpoints;

use App\Helpers\OutbrainClient;
use GuzzleHttp\Exception\GuzzleException;

class OutbrainAPI
{
    /**
     * @var OutbrainClient $outbrain
     */
    private $outbrain;

    /**
     * OutbrainAPI constructor.
     * @param $userProvider
     */
    public function __construct($userProvider)
    {
        $this->outbrain = new OutbrainClient($userProvider);
    }

    /**
     * Fetch marketers.
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function getMarketers()
    {
        return $this->outbrain->call('GET', "marketers");
    }

    /**
     * Fetch campaigns from API using MarketerID.
     *
     * @param  $id
     * @return mixed
     * @throws GuzzleException
     */
    public function getCampaignsByMarketerId($id)
    {
        return $this->outbrain->call('GET', "marketers/$id/campaigns");
    }
}
