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
        TweetFields::FULL_TEXT,
        TweetFields::NULLCAST,
        TweetFields::TRIM_USER,
        TweetFields::TWEET_MODE,
        TweetFields::VIDEO_CTA,
        TweetFields::VIDEO_CTA_VALUE,
        TweetFields::VIDEO_TITLE,
        TweetFields::VIDEO_DESCRIPTION
    ];

    /** Writable */
    protected $text;
    protected $as_user_id;
    protected $full_text;
    protected $nullcast;
    protected $trim_user;
    protected $tweet_mode;
    protected $video_cta;
    protected $video_cta_value;
    protected $video_title;
    protected $video_description;

    public function getId()
    {
        return $this->id;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    public function getDeleted()
    {
        return $this->deleted;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getAsUserId()
    {
        return $this->as_user_id;
    }

    public function setAsUserId($as_user_id)
    {
        $this->as_user_id = $as_user_id;
    }

    public function getFullText()
    {
        return $this->full_text;
    }

    public function setFullText($full_text)
    {
        $this->full_text = $full_text;
    }

    public function getNullCast()
    {
        return $this->nullcast;
    }

    public function setNullCast($nullcast)
    {
        $this->nullcast = $nullcast;
    }

    public function getTrimUser()
    {
        return $this->trim_user;
    }

    public function setTrimUser($trim_user)
    {
        $this->trim_user = $trim_user;
    }

    public function getTweetMode()
    {
        return $this->tweet_mode;
    }

    public function setTweetMode($tweet_mode)
    {
        $this->tweet_mode = $tweet_mode;
    }

    public function getVideoCTA()
    {
        return $this->video_cta;
    }

    public function setVideoCTA($video_cta)
    {
        $this->video_cta = $video_cta;
    }

    public function getVideoCTAValue()
    {
        return $this->video_cta_value;
    }

    public function setVideoCTAValue($video_cta_value)
    {
        $this->video_cta_value = $video_cta_value;
    }

    public function getVideoTitle()
    {
        return $this->video_title;
    }

    public function setVideoTitle($video_title)
    {
        $this->video_title = $video_title;
    }

    public function getVideoDescription()
    {
        return $this->video_description;
    }

    public function setVideoDescription($video_description)
    {
        $this->video_description = $video_description;
    }
}
