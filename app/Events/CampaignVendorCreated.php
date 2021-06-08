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

    public $data;

    public function __construct($user_id, $data = null)
    {
        $this->user_id = $user_id;

        $this->data = $data;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('campaign.' . $this->user_id);
    }
}