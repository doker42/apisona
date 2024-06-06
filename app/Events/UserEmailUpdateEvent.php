<?php

namespace App\Events;

use App\Models\EmailSet;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserEmailUpdateEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var User  */
    public User $user;

    /** @var EmailSet  */
    public EmailSet $emailSet;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, EmailSet $emailSet)
    {
        $this->user = $user;
        $this->emailSet = $emailSet;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
