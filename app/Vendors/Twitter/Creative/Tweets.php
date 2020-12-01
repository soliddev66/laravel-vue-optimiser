<?php

namespace App\Vendors\Twitter\Creative;

use App\Vendors\Twitter\Field\TweetFields;
use Hborras\TwitterAdsSDK\TwitterAds\Analytics;

/**
 * Class LineItem
 * @package Hborras\TwitterAdsSDK\TwitterAds\Campaign
 */
class Tweets extends Analytics
{
    const RESOURCE_COLLECTION = 'accounts/{account_id}/tweets';

    /** Read Only */
    protected $id;
    protected $created_at;
    protected $updated_at;
    protected $deleted;

    protected $properties = [
        TweetFields::TEXT,
        TweetFields::AS_USER_ID,
    ];

    /** Writable */
    protected $text;
    protected $as_user_id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getAsUserId()
    {
        return $this->as_user_id;
    }

    /**
     * @param mixed $as_user_id
     */
    public function setAsUserId($as_user_id)
    {
        $this->as_user_id = $as_user_id;
    }
}
