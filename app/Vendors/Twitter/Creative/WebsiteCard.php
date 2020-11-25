<?php

namespace App\Vendors\Twitter\Creative;

use App\Vendors\Twitter\Field\WebsiteCardFields;

use Hborras\TwitterAdsSDK\TwitterAds\Creative\WebsiteCard as HborrasWebsiteCard;

class WebsiteCard extends HborrasWebsiteCard
{
    protected $media_key;

    protected $properties = [
        WebsiteCardFields::NAME,
        WebsiteCardFields::WEBSITE_TITLE,
        WebsiteCardFields::WEBSITE_URL,
        WebsiteCardFields::MEDIA_KEY,
        WebsiteCardFields::IMAGE_MEDIA_ID,
    ];

    public function getMediaKey()
    {
        return $this->media_key;
    }

    public function setMediaKey($media_key)
    {
        $this->media_key = $media_key;
    }
}
