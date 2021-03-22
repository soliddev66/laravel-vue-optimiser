<?php

namespace App\Utils\AdVendors;

use App\Endpoints\YahooJPAPI;
use App\Jobs\PullCampaign;
use App\Models\Ad;
use App\Models\AdGroup;
use App\Models\Campaign;
use App\Models\GeminiDomainPerformanceStat;
use App\Models\GeminiSitePerformanceStat;
use App\Models\Provider;
use App\Models\RedtrackContentStat;
use App\Models\RedtrackReport;
use App\Models\UserProvider;
use App\Models\UserTracker;
use App\Models\YahooJapanReport;
use App\Vngodev\AdVendorInterface;
use App\Vngodev\Helper;
use Carbon\Carbon;
use DB;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Yahoojp extends Root implements AdVendorInterface
{
    use \App\Utils\AdVendors\Attributes\Yahoojp;

    private function api()
    {
        $provider = Provider::where('slug', request('provider'))->orWhere('id', request('provider'))->first();

        return new YahooJPAPI(auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first());
    }

    public function advertisers()
    {
        $advertisers = $this->api()->getAdvertisers()['rval']['values'];

        $result = [];

        foreach ($advertisers as $advertiser) {
            $result[] = [
                'id' => $advertiser['account']['accountId'],
                'name' => $advertiser['account']['accountName']
            ];
        }

        return $result;
    }

    public function campaignGoals()
    {
        $goal = $this->api()->getCampaignGoals(request('advertiser'))['rval']['values'][0];

        $result = [];

        foreach ($goal['accountAuthority']['authorities'] as $authory) {
            $result[] = [
                'id' => $authory,
                'text' => $authory
            ];
        }

        return $result;
    }

    public function signUp()
    {
        return $this->api()->createAdvertiser(request('name'));
    }

    public function languages()
    {
        return $this->api()->getLanguages();
    }

    public function countries()
    {
        return $this->api()->getCountries();
    }

    public function getCampaignInstance(Campaign $campaign)
    {
        try {
            $api = new YahooJPAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());

            $instance = $api->getCampaign($campaign->advertiser_id, $campaign->campaign_id)['rval']['values'][0]['campaign'];

            $instance['provider'] = $campaign->provider->slug;
            $instance['provider_id'] = $campaign['provider_id'];
            $instance['open_id'] = $campaign['open_id'];
            $instance['instance_id'] = $campaign['id'];
            $instance['id'] = $instance['campaignId'];
            $instance['adGroups'] = $api->getAdGroups($campaign->campaign_id, $campaign->advertiser_id)['rval']['values'];

            if (count($instance['adGroups']) > 0) {
                $instance['ads'] = $api->getAds([$instance['adGroups'][0]['adGroup']['adGroupId']], $campaign->advertiser_id)['rval']['values'];
                $instance['attributes'] = $api->getTargets($campaign->advertiser_id, [$instance['adGroups'][0]['adGroup']['adGroupId']], $campaign->campaign_id)['rval']['values'];
            }

            return $instance;
        } catch (Exception $e) {
            return [];
        }
    }

    public function cloneCampaignName(&$instance)
    {
        $instance['campaignName'] = $instance['campaignName'] . ' - Copy';
    }

    public function store()
    {
        $api = $this->api();

        try {
            $campaign_data = $api->createCampaign();

            $errors = $this->getErrors($campaign_data);

            if (count($errors)) {
                throw new Exception(json_encode($errors));
            }

            $campaign_id = $campaign_data['rval']['values'][0]['campaign']['campaignId'];

            try {
                $ad_group_data = $api->createAdGroup($campaign_id);

                $errors = $this->getErrors($ad_group_data);

                if (count($errors)) {
                    throw new Exception(json_encode($errors));
                }

                $ad_group_id = $ad_group_data['rval']['values'][0]['adGroup']['adGroupId'];
            } catch (Exception $e) {
                $api->deleteCampaign(request('selectedAdvertiser'), $campaign_id);
                throw $e;
            }

            $ads = [];

            try {
                foreach (request('contents') as $content) {
                    if ($content['adType'] == 'RESPONSIVE_IMAGE_AD') {
                        foreach ($content['images'] as $image) {
                            if ($image['existing']) {
                                $media_id = $image['mediaId'];
                            } else {
                                $file = storage_path('app/public/images/') . $image['image'];
                                $data = file_get_contents($file);
                                $ext = explode('.', $image['image']);

                                $media = $api->createMedia([
                                    'accountId' => request('selectedAdvertiser'),
                                    'operand' => [[
                                        'accountId' => request('selectedAdvertiser'),
                                        'imageMedia' => [
                                            'data' => base64_encode($data)
                                        ],
                                        'mediaName' => md5($image['image'] . time()) . '.' . end($ext),
                                        'mediaTitle' => md5($image['image'] . time()),
                                        'userStatus' => 'ACTIVE'
                                    ]]
                                ]);

                                $media_id = $media['rval']['values'][0]['mediaRecord']['mediaId'] ?? null;

                                if ($media_id == null && isset($media['rval']['values'][0]['errors'][0]['details'][0]['requestValue']) && $media['rval']['values'][0]['errors'][0]['details'][0]['requestKey'] == 'mediaId') {
                                    $media_id = $media['rval']['values'][0]['errors'][0]['details'][0]['requestValue'];
                                }

                                if (!$media_id) {
                                    throw new Exception(json_encode($media));
                                }
                            }

                            foreach ($content['headlines'] as $headlines) {
                                $ads[] = [
                                    'accountId' => request('selectedAdvertiser'),
                                    'ad' => [
                                        'adType' => $content['adType'],
                                        'responsiveImageAd' => [
                                            'buttonText' => 'FOR_MORE_INFO',
                                            'description' => $content['description'],
                                            'displayUrl' => $content['displayUrl'],
                                            'headline' => $headlines['headline'],
                                            'principal' => $content['principal'],
                                            'url' => $content['targetUrl'],
                                        ]
                                    ],
                                    'adGroupId' => $ad_group_id,
                                    'campaignId' => $campaign_id,
                                    'adName' => $headlines['headline'],
                                    'mediaId' => $media_id,
                                    'userStatus' => request('campaignStatus')
                                ];
                            }
                        }
                    } else if ($content['adType'] == 'RESPONSIVE_VIDEO_AD') {
                        foreach ($content['videos'] as $video) {
                            if ($video['existing']) {
                                $media_id = $video['mediaId'];
                            } else {
                                $file = storage_path('app/public/images/') . $video['videoPath'];
                                $data = file_get_contents($file);
                                $ext = explode('.', $video['videoPath']);

                                $file_name = md5($video['videoPath'] . time()) . '.' . end($ext);

                                $media = $api->uploadVideo([
                                    'accountId' => request('selectedAdvertiser'),
                                    'videoName' => $file_name,
                                    'videoTitle' => md5($video['videoPath'] . time()),
                                    'userStatus' => 'ACTIVE'
                                ], $file, $file_name);

                                $media_id = $media['rval']['values'][0]['uploadData']['mediaId'] ?? null;

                                if (!$media_id) {
                                    throw new Exception(json_encode($media));
                                }

                                $file = storage_path('app/public/images/') . $video['videoThumbnailPath'];
                                $data = file_get_contents($file);
                                $ext = explode('.', $video['videoThumbnailPath']);

                                $file_name = md5($video['videoThumbnailPath'] . time()) . '.' . end($ext);

                                $media = $api->createMedia([
                                    'accountId' => request('selectedAdvertiser'),
                                    'operand' => [[
                                        'accountId' => request('selectedAdvertiser'),
                                        'imageMedia' => [
                                            'data' => base64_encode($data)
                                        ],
                                        'mediaName' => md5($video['videoThumbnailPath'] . time()) . '.' . end($ext),
                                        'mediaTitle' => md5($video['videoThumbnailPath'] . time()),
                                        'thumbnailFlg' => 'TRUE',
                                        'userStatus' => 'ACTIVE'
                                    ]]
                                ]);

                                $thumbnail_media_id = $media['rval']['values'][0]['mediaRecord']['mediaId'] ?? null;

                                if ($thumbnail_media_id == null && isset($media['rval']['values'][0]['errors'][0]['details'][0]['requestValue']) && $media['rval']['values'][0]['errors'][0]['details'][0]['requestKey'] == 'mediaId') {
                                    $thumbnail_media_id = $media['rval']['values'][0]['errors'][0]['details'][0]['requestValue'];
                                }

                                if (!$thumbnail_media_id) {
                                    throw new Exception(json_encode($media));
                                }
                            }

                            foreach ($content['headlines'] as $headlines) {
                                $ads[] = [
                                    'accountId' => request('selectedAdvertiser'),
                                    'ad' => [
                                        'adType' => $content['adType'],
                                        'responsiveVideoAd' => [
                                            'buttonText' => 'FOR_MORE_INFO',
                                            'description' => $content['description'],
                                            'displayUrl' => $content['displayUrl'],
                                            'headline' => $headlines['headline'],
                                            'principal' => $content['principal'],
                                            'url' => $content['targetUrl'],
                                            'thumbnailMediaId' => $thumbnail_media_id
                                        ]
                                    ],
                                    'adGroupId' => $ad_group_id,
                                    'campaignId' => $campaign_id,
                                    'adName' => $headlines['headline'],
                                    'mediaId' => $media_id,
                                    'userStatus' => request('campaignStatus')
                                ];
                            }
                        }
                    }
                }

                $ad_data = $api->createAd([
                    'accountId' => request('selectedAdvertiser'),
                    'operand' => $ads
                ]);

                $errors = $this->getErrors($ad_data);

                if (count($errors)) {
                    throw new Exception(json_encode($errors));
                }

                if (count(request('campaignAges')) || count(request('campaignGenders')) || count(request('campaignDevices'))) {
                    $target_data = $api->createTargets($campaign_id, $ad_group_id);

                    $errors = $this->getErrors($target_data);

                    if (count($errors)) {
                        throw new Exception(json_encode($errors));
                    }
                }
            } catch (Exception $e) {
                $api->deleteCampaign(request('selectedAdvertiser'), $campaign_id);
                $api->deleteAdGroup($campaign_id, $ad_group_id);
                throw $e;
            }

            Helper::pullCampaign();

            return [];
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    private function getErrors($data)
    {
        $errors = [];
        if ($data['errors'] && count($data['errors'])) {
            $errors[] = $data['errors'];
        }

        if (isset($data['rval']['values']) && count($data['rval']['values'])) {
            foreach ($data['rval']['values'] as $value) {
                if (isset($value['errors']) && count($value['errors'])) {
                    $errors[] = $value['errors'];
                }
            }
        }

        return $errors;
    }

    public function storeAd(Campaign $campaign, $ad_group_id)
    {
        $api = $this->api();
        $ads = [];

        try {
            foreach (request('contents') as $content) {
                if ($content['adType'] == 'RESPONSIVE_IMAGE_AD') {
                    foreach ($content['images'] as $image) {
                        $file = storage_path('app/public/images/') . $image['image'];
                        $data = file_get_contents($file);
                        $ext = explode('.', $image['image']);
                        $media = $api->createMedia([
                            'accountId' => request('selectedAdvertiser'),
                            'operand' => [[
                                'accountId' => request('selectedAdvertiser'),
                                'imageMedia' => [
                                    'data' => base64_encode($data)
                                ],
                                'mediaName' => md5($image['image'] . time()) . '.' . end($ext),
                                'mediaTitle' => md5($image['image'] . time()),
                                'userStatus' => 'ACTIVE'
                            ]]
                        ]);

                        $media_id = $media['rval']['values'][0]['mediaRecord']['mediaId'] ?? null;

                        if ($media_id == null && isset($media['rval']['values'][0]['errors'][0]['details'][0]['requestValue']) && $media['rval']['values'][0]['errors'][0]['details'][0]['requestKey'] == 'mediaId') {
                            $media_id = $media['rval']['values'][0]['errors'][0]['details'][0]['requestValue'];
                        }

                        if (!$media_id) {
                            throw new Exception(json_encode($media));
                        }

                        foreach ($content['headlines'] as $headlines) {
                            $ads[] = [
                                'accountId' => request('selectedAdvertiser'),
                                'ad' => [
                                    'adType' => 'RESPONSIVE_IMAGE_AD',
                                    'responsiveImageAd' => [
                                        'buttonText' => 'FOR_MORE_INFO',
                                        'description' => $content['description'],
                                        'displayUrl' => $content['displayUrl'],
                                        'headline' => $headlines['headline'],
                                        'principal' => $content['principal'],
                                        'url' => $content['targetUrl'],
                                    ]
                                ],
                                'adGroupId' => $ad_group_id,
                                'campaignId' => $campaign->campaign_id,
                                'adName' => $headlines['headline'],
                                'mediaId' => $media_id,
                                'userStatus' => $campaign->status
                            ];
                        }
                    }
                } else if ($content['adType'] == 'RESPONSIVE_VIDEO_AD') {
                    foreach ($content['videos'] as $video) {
                        $file = storage_path('app/public/images/') . $video['videoPath'];
                        $data = file_get_contents($file);
                        $ext = explode('.', $video['videoPath']);

                        $file_name = md5($video['videoPath'] . time()) . '.' . end($ext);

                        $media = $api->uploadVideo([
                            'accountId' => request('selectedAdvertiser'),
                            'videoName' => $file_name,
                            'videoTitle' => md5($video['videoPath'] . time()),
                            'userStatus' => 'ACTIVE'
                        ], $file, $file_name);

                        $media_id = $media['rval']['values'][0]['uploadData']['mediaId'] ?? null;

                        if (!$media_id) {
                            throw new Exception(json_encode($media));
                        }

                        $file = storage_path('app/public/images/') . $video['videoThumbnailPath'];
                        $data = file_get_contents($file);
                        $ext = explode('.', $video['videoThumbnailPath']);

                        $file_name = md5($video['videoThumbnailPath'] . time()) . '.' . end($ext);

                        $media = $api->createMedia([
                            'accountId' => request('selectedAdvertiser'),
                            'operand' => [[
                                'accountId' => request('selectedAdvertiser'),
                                'imageMedia' => [
                                    'data' => base64_encode($data)
                                ],
                                'mediaName' => md5($video['videoThumbnailPath'] . time()) . '.' . end($ext),
                                'mediaTitle' => md5($video['videoThumbnailPath'] . time()),
                                'thumbnailFlg' => 'TRUE',
                                'userStatus' => 'ACTIVE'
                            ]]
                        ]);

                        $thumbnail_media_id = $media['rval']['values'][0]['mediaRecord']['mediaId'] ?? null;

                        if ($thumbnail_media_id == null && isset($media['rval']['values'][0]['errors'][0]['details'][0]['requestValue']) && $media['rval']['values'][0]['errors'][0]['details'][0]['requestKey'] == 'mediaId') {
                            $thumbnail_media_id = $media['rval']['values'][0]['errors'][0]['details'][0]['requestValue'];
                        }

                        if (!$thumbnail_media_id) {
                            throw new Exception(json_encode($media));
                        }

                        foreach ($content['headlines'] as $headlines) {
                            $ads[] = [
                                'accountId' => request('selectedAdvertiser'),
                                'ad' => [
                                    'adType' => $content['adType'],
                                    'responsiveVideoAd' => [
                                        'buttonText' => 'FOR_MORE_INFO',
                                        'description' => $content['description'],
                                        'displayUrl' => $content['displayUrl'],
                                        'headline' => $headlines['headline'],
                                        'principal' => $content['principal'],
                                        'url' => $content['targetUrl'],
                                        'thumbnailMediaId' => $thumbnail_media_id
                                    ]
                                ],
                                'adGroupId' => $ad_group_id,
                                'campaignId' => $campaign_id,
                                'adName' => $headlines['headline'],
                                'mediaId' => $media_id,
                                'userStatus' => request('campaignStatus')
                            ];
                        }
                    }
                }
            }

            $ad_data = $api->createAd([
                'accountId' => request('selectedAdvertiser'),
                'operand' => $ads
            ]);

            $errors = [];

            if ($ad_data['errors'] && count($ad_data['errors'])) {
                foreach ($ad_data['errors'] as $error) {
                    $message = $error['message'];

                    $keys = [];

                    foreach ($error['details'] as $detail) {
                        if (substr($detail['requestKey'], -6) == 'adName') {
                            $keys[] = $detail['requestValue'];
                        }
                    }

                    if (count($keys)) {
                        $message .= ' ' . implode(', ', $keys);
                    }

                    $errors[] = $message;
                }
            }

            if (isset($ad_data['rval']['values']) && count($ad_data['rval']['values'])) {
                foreach ($ad_data['rval']['values'] as $value) {
                    if (isset($value['errors']) && count($value['errors'])) {
                        foreach ($value['errors'] as $error) {
                            $message = $error['message'];

                            $keys = [];

                            foreach ($error['details'] as $detail) {
                                if ($detail['requestKey'] == 'mediaId') {
                                    $keys[] = $detail['requestValue'];
                                }
                            }

                            if (count($keys)) {
                                $message .= ' ' . implode(', ', $keys);
                            }

                            $errors[] = $message;
                        }
                    }
                }
            }

            if (count($errors)) {
                return [
                    'creatorError' => implode(', ', $errors)
                ];
            }

            Helper::pullAd();

            return $ad_data;
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function update(Campaign $campaign)
    {
        $api = $this->api();

        try {
            $campaign_data = $api->updateCampaign($campaign->campaign_id);
            $ad_group_data = $api->updateAdGroup($campaign->campaign_id);

            $ad_group_id = $ad_group_data['rval']['values'][0]['adGroup']['adGroupId'];

            $ads = [];

            $update_ads = [];

            foreach (request('contents') as $content) {
                if ($content['adType'] == 'RESPONSIVE_IMAGE_AD') {
                    foreach ($content['images'] as $image) {
                        if ($image['existing']) {
                            $media_id = $image['mediaId'];
                        } else {
                            $file = storage_path('app/public/images/') . $image['image'];
                            $data = file_get_contents($file);
                            $ext = explode('.', $image['image']);
                            $media = $api->createMedia([
                                'accountId' => request('selectedAdvertiser'),
                                'operand' => [[
                                    'accountId' => request('selectedAdvertiser'),
                                    'imageMedia' => [
                                        'data' => base64_encode($data)
                                    ],
                                    'mediaName' => md5($image['image'] . time()) . '.' . end($ext),
                                    'mediaTitle' => md5($image['image'] . time()),
                                    'userStatus' => 'ACTIVE'
                                ]]
                            ]);

                            $media_id = $media['rval']['values'][0]['mediaRecord']['mediaId'] ?? null;

                            if ($media_id == null && isset($media['rval']['values'][0]['errors'][0]['details'][0]['requestValue']) && $media['rval']['values'][0]['errors'][0]['details'][0]['requestKey'] == 'mediaId') {
                                $media_id = $media['rval']['values'][0]['errors'][0]['details'][0]['requestValue'];
                            }

                            if (!$media_id) {
                                throw new Exception(json_encode($media));
                            }
                        }

                        foreach ($content['headlines'] as $headlines) {
                            if ($headlines['existing']) {
                                $update_ads[] = [
                                    'accountId' => request('selectedAdvertiser'),
                                    'ad' => [
                                        'adType' => 'RESPONSIVE_IMAGE_AD',
                                        'responsiveImageAd' => [
                                            'buttonText' => 'FOR_MORE_INFO',
                                            'description' => $content['description'],
                                            'displayUrl' => $content['displayUrl'],
                                            'headline' => $headlines['headline'],
                                            'principal' => $content['principal'],
                                            'url' => $content['targetUrl'],
                                        ]
                                    ],
                                    'adGroupId' => $ad_group_id,
                                    'campaignId' => $campaign->campaign_id,
                                    'adId' => $content['id'],
                                    'adName' => $headlines['headline'],
                                    'mediaId' => $media_id,
                                    'userStatus' => request('campaignStatus')
                                ];
                            } else {
                                $ads[] = [
                                    'accountId' => request('selectedAdvertiser'),
                                    'ad' => [
                                        'adType' => 'RESPONSIVE_IMAGE_AD',
                                        'responsiveImageAd' => [
                                            'buttonText' => 'FOR_MORE_INFO',
                                            'description' => $content['description'],
                                            'displayUrl' => $content['displayUrl'],
                                            'headline' => $headlines['headline'],
                                            'principal' => $content['principal'],
                                            'url' => $content['targetUrl'],
                                        ]
                                    ],
                                    'adGroupId' => $ad_group_id,
                                    'campaignId' => $campaign->campaign_id,
                                    'adName' => $headlines['headline'],
                                    'mediaId' => $media_id,
                                    'userStatus' => request('campaignStatus')
                                ];
                            }
                        }
                    }
                } else if ($content['adType'] == 'RESPONSIVE_VIDEO_AD') {
                    foreach ($content['videos'] as $video) {
                        if ($video['existing']) {
                            $media_id = $video['mediaId'];
                            $thumbnail_media_id = $video['videoThumbnailId'];
                        } else {
                            $file = storage_path('app/public/images/') . $video['videoPath'];
                            $data = file_get_contents($file);
                            $ext = explode('.', $video['videoPath']);

                            $file_name = md5($video['videoPath'] . time()) . '.' . end($ext);

                            $media = $api->uploadVideo([
                                'accountId' => request('selectedAdvertiser'),
                                'videoName' => $file_name,
                                'videoTitle' => md5($video['videoPath'] . time()),
                                'userStatus' => 'ACTIVE'
                            ], $file, $file_name);

                            $media_id = $media['rval']['values'][0]['uploadData']['mediaId'] ?? null;

                            if (!$media_id) {
                                throw new Exception(json_encode($media));
                            }

                            $file = storage_path('app/public/images/') . $video['videoThumbnailPath'];
                            $data = file_get_contents($file);
                            $ext = explode('.', $video['videoThumbnailPath']);

                            $file_name = md5($video['videoThumbnailPath'] . time()) . '.' . end($ext);

                            $media = $api->createMedia([
                                'accountId' => request('selectedAdvertiser'),
                                'operand' => [[
                                    'accountId' => request('selectedAdvertiser'),
                                    'imageMedia' => [
                                        'data' => base64_encode($data)
                                    ],
                                    'mediaName' => md5($video['videoThumbnailPath'] . time()) . '.' . end($ext),
                                    'mediaTitle' => md5($video['videoThumbnailPath'] . time()),
                                    'thumbnailFlg' => 'TRUE',
                                    'userStatus' => 'ACTIVE'
                                ]]
                            ]);

                            $thumbnail_media_id = $media['rval']['values'][0]['mediaRecord']['mediaId'] ?? null;

                            if ($thumbnail_media_id == null && isset($media['rval']['values'][0]['errors'][0]['details'][0]['requestValue']) && $media['rval']['values'][0]['errors'][0]['details'][0]['requestKey'] == 'mediaId') {
                                $thumbnail_media_id = $media['rval']['values'][0]['errors'][0]['details'][0]['requestValue'];
                            }

                            if (!$thumbnail_media_id) {
                                throw new Exception(json_encode($media));
                            }
                        }

                        foreach ($content['headlines'] as $headlines) {
                            if ($headlines['existing']) {
                                $update_ads[] = [
                                    'accountId' => request('selectedAdvertiser'),
                                    'ad' => [
                                        'adType' => 'RESPONSIVE_VIDEO_AD',
                                        'responsiveVideoAd' => [
                                            'buttonText' => 'FOR_MORE_INFO',
                                            'description' => $content['description'],
                                            'displayUrl' => $content['displayUrl'],
                                            'headline' => $headlines['headline'],
                                            'principal' => $content['principal'],
                                            'url' => $content['targetUrl'],
                                            'thumbnailMediaId' => $thumbnail_media_id
                                        ]
                                    ],
                                    'adGroupId' => $ad_group_id,
                                    'campaignId' => $campaign->campaign_id,
                                    'adId' => $content['id'],
                                    'adName' => $headlines['headline'],
                                    'mediaId' => $media_id,
                                    'userStatus' => request('campaignStatus')
                                ];
                            } else {
                                $update_ads[] = [
                                    'accountId' => request('selectedAdvertiser'),
                                    'ad' => [
                                        'adType' => 'RESPONSIVE_VIDEO_AD',
                                        'responsiveVideoAd' => [
                                            'buttonText' => 'FOR_MORE_INFO',
                                            'description' => $content['description'],
                                            'displayUrl' => $content['displayUrl'],
                                            'headline' => $headlines['headline'],
                                            'principal' => $content['principal'],
                                            'url' => $content['targetUrl'],
                                            'thumbnailMediaId' => $thumbnail_media_id
                                        ]
                                    ],
                                    'adGroupId' => $ad_group_id,
                                    'campaignId' => $campaign->campaign_id,
                                    'adName' => $headlines['headline'],
                                    'mediaId' => $media_id,
                                    'userStatus' => request('campaignStatus')
                                ];
                            }
                        }
                    }
                }
            }

            if (count($ads)) {
                $ad_data = $api->createAd([
                    'accountId' => request('selectedAdvertiser'),
                    'operand' => $ads
                ]);
            }

            if (count($update_ads)) {
                $update_ad_data = $api->updateAd([
                    'accountId' => request('selectedAdvertiser'),
                    'operand' => $update_ads
                ]);
            }

            $target_data = $api->createTargets($campaign->campaign_id, $ad_group_id, true);

            return [];
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function delete(Campaign $campaign)
    {
        try {
            $api = new YahooJPAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());
            $data = $api->deleteCampaign($campaign->advertiser_id, $campaign->campaign_id);
            $campaign->delete();

            return [];
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function status(Campaign $campaign)
    {
        try {
            $api = new YahooJPAPI(UserProvider::where(['provider_id' => $campaign->provider_id, 'open_id' => $campaign->open_id])->first());
            $campaign->status = $campaign->status == Campaign::STATUS_ACTIVE ? Campaign::STATUS_PAUSED : Campaign::STATUS_ACTIVE;

            $data_campaigns = $api->updateCampaignStatus($campaign);

            $ad_groups = $api->getAdGroups($campaign->campaign_id, $campaign->advertiser_id)['rval']['values'];

            $ad_group_body = [
                'accountId' => $campaign->advertiser_id,
                'operand' => []
            ];

            $ad_group_ids = [];

            foreach ($ad_groups as $ad_group) {
                $ad_group_body['operand'][] = [
                    'accountId' => $campaign->advertiser_id,
                    'campaignId' => $campaign->campaign_id,
                    'adGroupId' => $ad_group['adGroup']['adGroupId'],
                    'userStatus' => $campaign->status
                ];

                $ad_body = [
                    'accountId' => $campaign->advertiser_id,
                    'operand' => []
                ];

                $ads = $api->getAds([$ad_group['adGroup']['adGroupId']], $campaign->advertiser_id)['rval']['values'];

                foreach ($ads as $ad) {
                    $ad_body['operand'][] = [
                        'accountId' => $campaign->advertiser_id,
                        'campaignId' => $campaign->campaign_id,
                        'adGroupId' => $ad_group['adGroup']['adGroupId'],
                        'adId' => $ad['adGroupAd']['adId'],
                        'userStatus' => $campaign->status
                    ];
                }

                $data_ads = $api->updateAdStatus($ad_body);
            }

            $data_ad_groups = $api->updateAdGroups($ad_group_body);
            $campaign->save();

            return [];
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function adGroupData(Campaign $campaign)
    {
        //
    }

    public function adStatus(Campaign $campaign, $ad_group_id, $ad_id, $status = null)
    {
        try {
            $api = new YahooJPAPI(UserProvider::where(['provider_id' => $campaign->provider_id, 'open_id' => $campaign->open_id])->first());

            if ($status == null) {
                $status = request('status') == Campaign::STATUS_ACTIVE ? Campaign::STATUS_PAUSED : Campaign::STATUS_ACTIVE;
            }

            $data_ads = $api->updateAdStatus([
                'accountId' => $campaign->advertiser_id,
                'operand' => [[
                    'accountId' => $campaign->advertiser_id,
                    'campaignId' => $campaign->campaign_id,
                    'adGroupId' => $ad_group_id,
                    'adId' => $ad_id,
                    'userStatus' => $status
                ]]
            ]);

            $ad = Ad::where('ad_id', $ad_id)->first();
            $ad->status = $status;
            $ad->save();

            return [];
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function adGroupStatus(Campaign $campaign, $ad_group_id)
    {
        try {
            $api = new YahooJPAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());
            $status = request('status') == Campaign::STATUS_ACTIVE ? Campaign::STATUS_PAUSED : Campaign::STATUS_ACTIVE;

            $ad_group = $api->updateAdGroups([
                'accountId' => $campaign->advertiser_id,
                'operand' => [[
                    'accountId' => $campaign->advertiser_id,
                    'campaignId' => $campaign->campaign_id,
                    'adGroupId' => $ad_group_id,
                    'userStatus' => $status
                ]]
            ]);

            $ad_body = [
                'accountId' => $campaign->advertiser_id,
                'operand' => []
            ];

            $ads = $api->getAds([$ad_group_id], $campaign->advertiser_id)['rval']['values'];

            foreach ($ads as $ad) {
                $ad_body['operand'][] = [
                    'accountId' => $campaign->advertiser_id,
                    'campaignId' => $campaign->campaign_id,
                    'adGroupId' => $ad_group_id,
                    'adId' => $ad['adGroupAd']['adId'],
                    'userStatus' => $campaign->status
                ];

                $ad = Ad::where('ad_id', $ad['adGroupAd']['adId'])->first();
                $ad->status = $status;
                $ad->save();
            }

            $data_ads = $api->updateAdStatus($ad_body);

            $ad_group = AdGroup::where('ad_group_id', $ad_group_id)->first();
            $ad_group->status = $status;
            $ad_group->save();

            return [];
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function pullCampaign($user_provider)
    {
        $api = new YahooJPAPI($user_provider);
        $campaign_ids = [];

        foreach ($user_provider->advertisers as $key => $advertiser) {
            $campaigns_by_account_response = $api->getCampaignsByAccountId($advertiser);
            $campaigns_by_account = $campaigns_by_account_response['rval']['values'];
            if (is_array($campaigns_by_account)) {
                foreach ($campaigns_by_account as $campaign) {
                    $campaign = $campaign['campaign'];
                    $db_campaign = Campaign::firstOrNew([
                        'campaign_id' => $campaign['campaignId'],
                        'provider_id' => $user_provider->provider_id,
                        'open_id' => $user_provider->open_id,
                        'user_id' => $user_provider->user_id
                    ]);

                    $db_campaign->name = $campaign['campaignName'];
                    $db_campaign->status = $campaign['userStatus'];
                    $db_campaign->budget = $campaign['budget']['amount'];
                    $db_campaign->advertiser_id = $advertiser;
                    $db_campaign->save();
                    $campaign_ids[] = $db_campaign->id;
                }
            }
        }

        Campaign::where([
            'user_id' => $user_provider->user_id,
            'provider_id' => $user_provider->provider_id,
            'open_id' => $user_provider->open_id
        ])->whereNotIn('id', $campaign_ids)->delete();
    }

    public function pullAdGroup($user_provider)
    {
        $ad_group_ids = [];
        Campaign::where('user_id', $user_provider->user_id)->where('provider_id', 5)->chunk(10, function ($campaigns) use ($user_provider, &$ad_group_ids) {
            foreach ($campaigns as $key => $campaign) {
                $ad_groups_response = (new YahooJPAPI($user_provider))->getAdGroups($campaign->campaign_id, $campaign->advertiser_id);
                $ad_groups = $ad_groups_response['rval']['values'];
                if (is_array($ad_groups) && count($ad_groups)) {
                    foreach ($ad_groups as $key => $ad_group) {
                        $ad_group = $ad_group['adGroup'];
                        $db_ad_group = AdGroup::firstOrNew([
                            'ad_group_id' => $ad_group['adGroupId'],
                            'user_id' => $user_provider->user_id,
                            'provider_id' => $user_provider->provider_id,
                            'campaign_id' => $campaign->campaign_id,
                            'advertiser_id' => $campaign->advertiser_id,
                            'open_id' => $user_provider->open_id
                        ]);

                        $db_ad_group->name = $ad_group['adGroupName'];
                        $db_ad_group->status = $ad_group['userStatus'];
                        $db_ad_group->save();
                        $ad_group_ids[] = $db_ad_group->id;
                    }
                }
            }
        });

        AdGroup::where([
            'user_id' => $user_provider->user_id,
            'provider_id' => $user_provider->provider_id,
            'open_id' => $user_provider->open_id
        ])->whereNotIn('id', $ad_group_ids)->delete();
    }

    public function pullAd($user_provider)
    {
        $ad_ids = [];
        AdGroup::where('user_id', $user_provider->user_id)->where('provider_id', 5)->chunk(10, function ($ad_groups) use ($user_provider, &$ad_ids) {
            foreach ($ad_groups as $key => $ad_group) {
                $ads_response = (new YahooJPAPI($user_provider))->getAds([$ad_group->ad_group_id], $ad_group->advertiser_id);
                $ads = $ads_response['rval']['values'];
                if (is_array($ads) && count($ads)) {
                    foreach ($ads as $key => $ad) {
                        $ad = $ad['adGroupAd'];
                        $db_ad = Ad::firstOrNew([
                            'ad_id' => $ad['adId'],
                            'user_id' => $user_provider->user_id,
                            'provider_id' => $user_provider->provider_id,
                            'campaign_id' => $ad_group->campaign_id,
                            'advertiser_id' => $ad_group->advertiser_id,
                            'ad_group_id' => $ad_group->ad_group_id,
                            'open_id' => $user_provider->open_id
                        ]);

                        $db_ad->name = $ad['adName'];
                        $db_ad->status = $ad['userStatus'];
                        $db_ad->save();
                        $ad_ids[] = $db_ad->id;
                    }
                }
            }
        });

        Ad::where([
            'user_id' => $user_provider->user_id,
            'provider_id' => $user_provider->provider_id,
            'open_id' => $user_provider->open_id
        ])->whereNotIn('id', $ad_ids)->delete();
    }

    public function pullRedTrack($campaign, $target_date = null)
    {
        $tracker = UserTracker::where('provider_id', $campaign->provider_id)->where('provider_open_id', $campaign->open_id)->first();
        if ($tracker) {
            $client = new Client();
            $date = Carbon::now()->format('Y-m-d');
            if ($target_date) {
                $date = $target_date;
            }
            $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=hour_of_day&sub6=' . $campaign->campaign_id . '&tracks_view=true';
            $response = $client->get($url);

            $data = json_decode($response->getBody(), true);
            if (count($data)) {
                foreach ($data as $i => $value) {
                    $value['date'] = $date;
                    $value['user_id'] = $campaign->user_id;
                    $value['campaign_id'] = $campaign->id;
                    $value['provider_id'] = $campaign->provider_id;
                    $value['open_id'] = $campaign->open_id;
                    $value['advertiser_id'] = $campaign->advertiser_id;
                    $redtrack_report = RedtrackReport::firstOrNew([
                        'date' => $date,
                        'sub6' => $campaign->campaign_id,
                        'hour_of_day' => $value['hour_of_day']
                    ]);
                    foreach (array_keys($value) as $array_key) {
                        $redtrack_report->{$array_key} = $value[$array_key];
                    }
                    $redtrack_report->save();
                }

                // Content stats
                $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=sub5&sub6=' . $campaign->campaign_id . '&tracks_view=true';
                $response = $client->get($url);

                $data = json_decode($response->getBody(), true);

                foreach ($data as $i => $value) {
                    $value['date'] = $date;
                    $value['user_id'] = $campaign->user_id;
                    $value['campaign_id'] = $campaign->id;
                    $value['provider_id'] = $campaign->provider_id;
                    $value['open_id'] = $campaign->open_id;
                    $value['advertiser_id'] = $campaign->advertiser_id;
                    $redtrack_report = RedtrackContentStat::firstOrNew([
                        'date' => $date,
                        'sub5' => $value['sub5']
                    ]);
                    foreach (array_keys($value) as $array_key) {
                        $redtrack_report->{$array_key} = $value[$array_key];
                    }
                    $redtrack_report->save();
                }
            }
        }
    }

    public function getSummaryDataQuery($data, $campaign = null)
    {
        $summary_data_query = YahooJapanReport::select(
            DB::raw('SUM(cost) as total_cost'),
            DB::raw('"N/A" as total_revenue'),
            DB::raw('"N/A" as total_net'),
            DB::raw('"N/A" as avg_roi')
        );
        $summary_data_query->leftJoin('campaigns', function ($join) use ($data) {
            $join->on('campaigns.campaign_id', '=', 'yahoo_japan_reports.campaign_id');
            if ($data['provider']) {
                $join->where('campaigns.provider_id', $data['provider']);
            }
            if ($data['account']) {
                $join->where('campaigns.open_id', $data['account']);
            }
            if ($data['advertiser']) {
                $join->where('campaigns.advertiser_id', $data['advertiser']);
            }
        });
        $summary_data_query->whereBetween('date', [request('start'), request('end')]);

        return $summary_data_query;
    }

    public function getCampaignQuery($data)
    {
        $campaigns_query = Campaign::select([
            DB::raw('MAX(campaigns.id) AS id'),
            DB::raw('campaigns.campaign_id AS campaign_id'),
            DB::raw('MAX(campaigns.name) AS name'),
            DB::raw('MAX(campaigns.status) AS status'),
            DB::raw('MAX(campaigns.budget) AS budget'),
            DB::raw('SUM(click_cnt) as clicks'),
            DB::raw('ROUND(SUM(cost), 2) as cost')
        ]);
        $campaigns_query->leftJoin('yahoo_japan_reports', function ($join) use ($data) {
            $join->on('yahoo_japan_reports.campaign_id', '=', 'campaigns.id')->whereBetween('yahoo_japan_reports.date', [$data['start'], $data['end']]);
        });
        if ($data['provider']) {
            $campaigns_query->where('campaigns.provider_id', $data['provider']);
        }
        if ($data['account']) {
            $campaigns_query->where('campaigns.open_id', $data['account']);
        }
        if ($data['advertiser']) {
            $campaigns_query->where('campaigns.advertiser_id', $data['advertiser']);
        }
        if ($data['search']) {
            $campaigns_query->where('name', 'LIKE', '%' . $data['search'] . '%');
        }
        $campaigns_query->groupBy('campaigns.campaign_id');

        return $campaigns_query;
    }

    public function getWidgetQuery($campaign, $data)
    {
        $widgets_query = GeminiSitePerformanceStat::select([
            '*',
            DB::raw('CONCAT(external_site_name, "|", device_type) as widget_id'),
            DB::raw('ROUND(spend / clicks, 2) as calc_cpc'),
            DB::raw('null as tr_conv'),
            DB::raw('null as tr_rev'),
            DB::raw('null as tr_net'),
            DB::raw('null as tr_roi'),
            DB::raw('null as tr_epc'),
            DB::raw('null as epc'),
            DB::raw('null as tr_cpa'),
            DB::raw('clicks as ts_clicks'),
            DB::raw('null as trk_clicks'),
            DB::raw('null as lp_clicks'),
            DB::raw('null as lp_ctr'),
            DB::raw('CONCAT(ROUND(clicks / impressions * 100, 2), "%") as ctr'),
            DB::raw('null as tr_cvr'),
            DB::raw('ROUND(spend / impressions * 1000, 2) as ecpm'),
            DB::raw('null as lp_cr'),
            DB::raw('null as lp_cpc')
        ]);
        $widgets_query->where('campaign_id', $campaign->campaign_id);
        $widgets_query->whereBetween('day', [$data['start'], $data['end']]);
        $widgets_query->where(DB::raw('CONCAT(external_site_name, "|", device_type)'), 'LIKE', '%' . $data['search'] . '%');

        return $widgets_query;
    }

    public function getContentQuery($campaign, $data)
    {
        $contents_query = Ad::select([
            DB::raw('MAX(ads.id) as id'),
            DB::raw('MAX(ads.campaign_id) as campaign_id'),
            DB::raw('MAX(ads.ad_group_id) as ad_group_id'),
            DB::raw('MAX(ads.ad_id) as ad_id'),
            DB::raw('MAX(ads.name) as name'),
            DB::raw('MAX(ads.status) as status'),
            DB::raw('ROUND(SUM(total_revenue)/SUM(total_conversions), 2) as payout'),
            DB::raw('SUM(clicks) as clicks'),
            DB::raw('SUM(lp_views) as lp_views'),
            DB::raw('SUM(lp_clicks) as lp_clicks'),
            DB::raw('SUM(total_conversions) as conversions'),
            DB::raw('SUM(total_conversions) as total_actions'),
            DB::raw('ROUND((SUM(total_conversions)/SUM(clicks)) * 100, 2) as total_actions_cr'),
            DB::raw('ROUND((SUM(total_conversions)/SUM(clicks)) * 100, 2) as cr'),
            DB::raw('ROUND(SUM(total_revenue), 2) as total_revenue'),
            DB::raw('ROUND(SUM(cost), 2) as cost'),
            DB::raw('ROUND(SUM(profit), 2) as profit'),
            DB::raw('ROUND((SUM(profit)/SUM(cost)) * 100, 2) as roi'),
            DB::raw('ROUND(SUM(cost)/SUM(clicks), 2) as cpc'),
            DB::raw('ROUND(SUM(cost)/SUM(total_conversions), 2) as cpa'),
            DB::raw('ROUND(SUM(total_revenue)/SUM(clicks), 2) as epc'),
            DB::raw('ROUND((SUM(lp_clicks)/SUM(lp_views)) * 100, 2) as lp_ctr'),
            DB::raw('ROUND((SUM(total_conversions)/SUM(lp_views)) * 100, 2) as lp_views_cr'),
            DB::raw('ROUND((SUM(total_conversions)/SUM(lp_clicks)) * 100, 2) as lp_clicks_cr'),
            DB::raw('ROUND(SUM(cost)/SUM(lp_clicks), 2) as lp_cpc')
        ]);
        $contents_query->leftJoin('redtrack_content_stats', function ($join) use ($data) {
            $join->on('redtrack_content_stats.sub5', '=', 'ads.ad_id')->whereBetween('redtrack_content_stats.date', [$data['start'], $data['end']]);
        });
        $contents_query->where('ads.campaign_id', $campaign->campaign_id);
        $contents_query->where('name', 'LIKE', '%' . $data['search'] . '%');
        $contents_query->groupBy('ads.ad_id');

        return $contents_query;
    }

    public function getAdGroupQuery($campaign, $data)
    {
        //
    }

    public function getDomainQuery($campaign, $data)
    {
        $domains_query = GeminiDomainPerformanceStat::select(
            DB::raw('MAX(id) as id'),
            DB::raw('MAX(coalesce(top_domain, package_name)) as top_domain'),
            DB::raw('SUM(clicks) as clicks'),
            DB::raw('SUM(spend) as cost'),
            DB::raw('SUM(impressions) as total_view')
        );
        $domains_query->where('campaign_id', $campaign->campaign_id);
        $domains_query->whereBetween('day', [$data['start'], $data['end']]);
        $domains_query->where('top_domain', 'LIKE', '%' . $data['search'] . '%');
        $domains_query->groupBy('top_domain');

        return $domains_query;
    }

    public function getPerformanceQuery($campaign, $data)
    {
        //
    }

    public function getPerformanceData($campaign, $time_range)
    {
        return $campaign->yahooJapanReports()->whereBetween('date', [$time_range[0]->format('Y-m-d'), $time_range[1]->format('Y-m-d')])->get();
    }

    public function getDomainData($campaign, $time_range)
    {
        return [];
    }

    public function addSiteBlock($campaign, $data)
    {
        //
    }

    public function targets(Campaign $campaign)
    {
        //
    }

    public function blockWidgets(Campaign $campaign, $widgets)
    {
        //
    }

    public function unblockWidgets(Campaign $campaign, $widgets)
    {
        //
    }

    public function changeBugget(Campaign $campaign, $budget)
    {
        $api = new YahooJPAPI(UserProvider::where([
            'provider_id' => $campaign->provider->id,
            'open_id' => $campaign->open_id
        ])->first());

        $api->updateCampaignData([
            'accountId' => $campaign->advertiser_id,
            'operand' => [[
                'accountId' => $campaign->advertiser_id,
                'campaignId' => $campaign->campaign_id,
                'budget' => [
                    'amount' => $budget
                ]
            ]]
        ]);
    }

    public function changeCampaignBid(Campaign $campaign, $data)
    {
        $api = new YahooJPAPI(UserProvider::where([
            'provider_id' => $campaign->provider->id,
            'open_id' => $campaign->open_id
        ])->first());

        $campaign_data = $api->getCampaign($campaign->advertiser_id, $campaign->campaign_id)['rval']['values'][0]['campaign'];

        $campaign_bidding_strategy = [
            'campaignBiddingStrategyType' => $campaign_data['campaignBiddingStrategy']['campaignBiddingStrategyType']
        ];

        switch ($campaign_bidding_strategy['campaignBiddingStrategyType']) {
            case 'MAX_CPC':
                $campaign_bidding_strategy['maxCpcBidValue'] = $data->bid;
                break;

            case 'MAX_VCPM':
                $campaign_bidding_strategy['maxVcpmBidValue'] = $data->bid;
                break;

            case 'MAX_CPV':
                $campaign_bidding_strategy['maxCpvBidValue'] = $data->bid;
                break;

            case 'MAX_VCPM':
                $campaign_bidding_strategy['targetCpaBidValue'] = $data->bid;
                break;
        }

        $api->updateCampaignData([
            'accountId' => $campaign->advertiser_id,
            'operand' => [[
                'accountId' => $campaign->advertiser_id,
                'campaignId' => $campaign->campaign_id,
                'campaignBiddingStrategy' => $campaign_bidding_strategy
            ]]
        ]);
    }
}
