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
     * @param $user_provider
     */
    public function __construct($user_provider)
    {
        $this->outbrain = new OutbrainClient($user_provider);
    }

    /**
     * Fetch Amplify GeoLocations.
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function getCountries()
    {
        return $this->outbrain->call('GET', 'locations/search?term=&limit=&geoType=country');
    }

    /**
     * Fetch marketers.
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function getMarketers()
    {
        return $this->outbrain->call('GET', 'marketers');
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
        return $this->outbrain->call('GET', 'marketers/' . $id . '/campaigns');
    }
}
