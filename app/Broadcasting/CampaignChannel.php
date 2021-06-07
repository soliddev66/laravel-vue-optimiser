<?php

namespace App\Broadcasting;

use App\Models\User;

class CampaignChannel
{
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     *
     * @param  \App\Models\User  $user
     * @return array|bool
     */
    public function join(User $user, $userId)
    {
        file_put_contents('aaaa', $userId);
        return (int) $user->id === (int) $userId;
    }
}
