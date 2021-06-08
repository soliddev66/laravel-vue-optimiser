<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class CampaignVendorCreated implements ShouldBroadcastNow
{
    public $user_id;

    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('campaign.' . $this->user_id);
    }
}