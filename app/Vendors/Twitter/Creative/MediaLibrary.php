<?php

namespace App\Vendors\Twitter\Creative;

use Hborras\TwitterAdsSDK\TwitterAds\Resource;
use Hborras\TwitterAdsSDK\TwitterAds\Fields\MediaLibraryFields;

/**
 * Class WebsiteCard
 * @package Hborras\TwitterAdsSDK\TwitterAds\Creative
 */
class MediaLibrary extends Resource
{
    const RESOURCE_COLLECTION = 'accounts/{account_id}/media_library';
    const RESOURCE = 'accounts/{account_id}/media_library/{media_key}';

    /** Read Only */
    protected $id;
    protected $created_at;
    protected $updated_at;
    protected $deleted;

    protected $properties = [
        MediaLibraryFields::MEDIA_KEY
    ];

    /** Writable */
    protected $media_key;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $website_title
     */
    public function setMediaKey($media_key)
    {
        $this->media_key = $media_key;
    }

    /**
     * @return mixed
     */
    public function getMediaKey()
    {
        return $this->media_key;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @return mixed
     */
    public function getDeleted()
    {
        return $this->deleted;
    }
}
