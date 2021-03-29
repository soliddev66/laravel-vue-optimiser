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
     * Fetch publishers.
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function getPublishers()
    {
        return $this->client->call('GET', 'publishers');
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
    public function getCampaignsByMarketerId($id, $offset)
    {
        return $this->client->call('GET', 'marketers/' . $id . '/campaigns?offset=' . $offset);
    }

    public function createBudget()
    {
        return $this->client->call('POST', 'marketers/' . request('selectedAdvertiser') . '/budgets', [
            'name' => request('campaignName') . '_' . Carbon::now(),
            'amount' => request('campaignBudget'),
            'startDate' => request('campaignStartDate'),
            'endDate' => request('campaignEndDate'),
            'runForever' => request('campaignEndDate') ? false : true,
            'type' => request('campaignBudgetType'),
            'pacing' => request('campaignPacing')
        ]);
    }

    public function updateBudget($budget_id)
    {
        return $this->client->call('PUT', 'budgets/' . $budget_id, [
            'name' => request('campaignName') . '_' . Carbon::now(),
            'amount' => request('campaignBudget'),
            'startDate' => request('campaignStartDate'),
            'endDate' => request('campaignEndDate'),
            'runForever' => request('campaignEndDate') ? false : true,
            'type' => request('campaignBudgetType'),
            'pacing' => request('campaignPacing')
        ]);
    }

    public function getBudget($budget_id)
    {
        return $this->client->call('GET', 'budgets/' . $budget_id);
    }

    public function updateBudgetAmount($budget_id, $amount)
    {
        return $this->client->call('PUT', 'budgets/' . $budget_id, [
            'amount' => $amount
        ]);
    }

    public function createCampaign($budget_data)
    {
        return $this->client->call('POST', 'campaigns', [
            'name' => request('campaignName'),
            'cpc' => request('campaignCostPerClick'),
            'enabled' => true,
            'budgetId' => $budget_data['id'],
            'targeting' => [
                'platform' => request('campaginPlatform'),
                'locations' => request('campaignLocation'),
                'operatingSystems' => request('campaignOperatingSystem'),
                'browsers' => request('campaignBrowser'),
                'useExtendedNetworkTraffic' => request('campaignUseNetworkExtendedTraffic'),
                'excludeAdBlockUsers' => request('campaignExcludeAdBlockUsers')
            ],
            'suffixTrackingCode' => request('campaignTrackingCode'),
            'onAirType' => request('campaignStartTime') ? 'StartHour' : 'Scheduled',
            'startHour' => strtoupper(request('campaignStartTime')),
            'objective' => request('campaignObjective')
        ]);
    }

    public function updateCampaign($campaign_id)
    {
        return $this->client->call('PUT', 'campaigns/' . $campaign_id . '?extraFields=CustomAudience,Locations,InterestsTargeting,BidBySections,BlockedSites,PlatformTargeting,CampaignOptimization,Scheduling', [
            'name' => request('campaignName'),
            'cpc' => request('campaignCostPerClick'),
            'targeting' => [
                'platform' => request('campaginPlatform'),
                'locations' => request('campaignLocation'),
                'operatingSystems' => request('campaignOperatingSystem'),
                'browsers' => request('campaignBrowser'),
                'useExtendedNetworkTraffic' => request('campaignUseNetworkExtendedTraffic'),
                'excludeAdBlockUsers' => request('campaignExcludeAdBlockUsers')
            ],
            'suffixTrackingCode' => request('campaignTrackingCode'),
            'onAirType' => request('campaignStartTime') ? 'StartHour' : 'Scheduled',
            'startHour' => strtoupper(request('campaignStartTime')),
            'objective' => request('campaignObjective')
        ]);
    }

    public function createAd($campaign_id, $ad)
    {
        return $this->client->call('POST', 'campaigns/' . $campaign_id . '/promotedLinks', $ad);
    }

    public function updateAd($campaign_id, $ad)
    {
        return $this->client->call('PUT', 'campaigns/' . $campaign_id . '/promotedLinks', $ad);
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

    public function updateCampaignData($campaign_id, $body)
    {
        return $this->client->call('PUT', 'campaigns/' . $campaign_id, $body);
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

    public function getPerformanceReport($campaign, $date)
    {
        return $this->client->call('GET', 'reports/marketers/' . $campaign->advertiser_id . '/campaigns?from=' . $date . '&to=' . $date . '&limit=100&offset=0&includeArchivedCampaigns=true&campaignId=' . $campaign->campaign_id . '&includeConversionDetails=true&conversionsByClickDate=true');
    }
}
