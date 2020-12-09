<?php

namespace App\Endpoints;

use App\Helpers\OutbrainClient;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;

class OutbrainAPI
{
    /**
     * @var OutbrainClient $client
     */
    private $client;

    /**
     * OutbrainAPI constructor.
     * @param $user_provider
     */
    public function __construct($user_provider)
    {
        $this->client = new OutbrainClient($user_provider);
    }

    /**
     * Fetch Amplify GeoLocations.
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function getCountries()
    {
        return $this->client->call('GET', 'locations/search?term=Japan');
    }

    /**
     * Fetch marketers.
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function getMarketers()
    {
        return $this->client->call('GET', 'marketers');
    }

    /**
     * Fetch budgets.
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function getBudgets()
    {
        return $this->client->call('GET', 'budgets');
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
        return $this->client->call('GET', 'marketers/' . $id . '/campaigns');
    }

    public function createBudget()
    {
        return $this->client->call('POST', 'marketers/' . request('selectedAdvertiser') . '/budgets', [
            'name' => request('campaignName'),
            'amount' => request('campaignBudget'),
            'startDate' => request('campaignStartDate'),
            'endDate' => request('campaignEndDate'),
            'runForever' => request('campaignEndDate') ? false : true,
            'type' => request('campaignBudgetType'),
            'pacing' => request('campaignPacing')
        ]);
    }

    public function createAdCampaign($budget_data)
    {
        return $this->client->call('POST', 'campaigns', [
            'marketerId' => request('selectedAdvertiser'),
            'name' => request('campaignName'),
            'cpc' => request('campaignCostPerClick'),
            'enabled' => true,
            'budgetId' => $budget_data['id'],
            'targeting' => [
                'platform' => request('campaginPlatform'),
                'locations' => request('campaignLocation'),
                'operatingSystems' => request('campaignOperatingSystem'),
                'browsers' => request('campaignBrowser')
            ],
            'suffixTrackingCode' => request('campaignTrackingCode'),
            'onAirType' => request('campaignStartTime') ? 'StartHour' : 'Scheduled',
            'startHour' => request('campaignStartTime'),
            'objective' => request('campaignObjective')
        ]);
    }

    public function updateCampaign($campaign)
    {
        return $this->client->call('PUT', 'campaigns/' . $campaign->campaign_id, [
            'name' => request('campaignName'),
            'cpc' => request('campaignCostPerClick'),
            'targeting' => [
                'platform' => request('campaginPlatform'),
                'locations' => request('campaignLocation'),
                'operatingSystems' => request('campaignOperatingSystem'),
                'browsers' => request('campaignBrowser')
            ],
            'suffixTrackingCode' => request('campaignTrackingCode'),
            'onAirType' => request('campaignStartTime') ? 'StartHour' : 'Scheduled',
            'startHour' => request('campaignStartTime'),
            'objective' => request('campaignObjective')
        ]);
    }

    public function createAd($campaign_data)
    {
        return $this->client->call('POST', 'campaigns/' . $campaign_data['id'] . '/promotedLinks', [
            'text' => request('title'),
            'url' => request('targetUrl'),
            'enabled' => true,
            'cpc' => request('cpc'),
            'imageMetadata' => [
                'url' => request('imageUrl')
            ]
        ]);
    }

    public function deleteCampaign($campaign_id)
    {
        return $this->client->call('DELETE', 'campaigns/' . $campaign_id);
    }

    public function deleteBudget($budget_data)
    {
        return $this->client->call('DELETE', 'budgets/' . $budget_data['id']);
    }

    public function getCampaign($campaign_id)
    {
        return $this->client->call('GET', 'campaigns/' . $campaign_id);
    }

    public function updateCampaignStatus($campaign_id, $enabled)
    {
        return $this->client->call('PUT', 'campaigns/' . $campaign_id, [
            'enabled' => $enabled
        ]);
    }

    public function getPromotedLinks($campaign_id)
    {
        return $this->client->call('GET', 'campaigns/' . $campaign_id . '/promotedLinks?extraFields=ImageMetaData');
    }

    public function updatePromotedLinkStatus($promoted_link_ids, $enabled)
    {
        return $this->client->call('PUT', 'promotedLinks/' . $promoted_link_ids, [
            'enabled' => $enabled
        ]);
    }

    public function getPerformanceReport($campaign, $promoted_link, $date)
    {
        return $this->client->call('GET', 'reports/marketers/' . $campaign->advertiser_id . '/periodic?from=' . $date . '&to=' . $date . '&limit=100&offset=0&includeArchivedCampaigns=true&campaignId=' . $campaign->campaign_id . '&promotedLinkId=' . $promoted_link['id'] . '&breakdown=daily&includeConversionDetails=true&conversionsByClickDate=true');
    }
}
