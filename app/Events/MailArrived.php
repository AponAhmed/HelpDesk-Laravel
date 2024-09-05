<?php

namespace App\Events;

use App\Models\MailList;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class MailArrived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected MailList $mailList;
    protected string $action;
    /**
     * Create a new event instance.
     */
    public function __construct(MailList $mailList, $action = 'assign')
    {
        //
        $this->mailList = $mailList;
        $this->action = $action;
    }

    public function broadcastWith(): array
    {
        return [
            'listItem' => $this->mailList->toArray(),
            'adminprev' => $this->action == "assign" ? 'remove' : 'prepend',
            'userPrev' => $this->action == "assign" ? 'prepend' : 'remove',
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $eventChannels = [];
        if ($this->action === 'assign') {
            $eventChannels[] =  new PrivateChannel('mail.' . $this->mailList->user . ".userPrev.new");
        }

        $adminUserIds = DB::table('users')
            ->join('user_has_role', 'users.id', '=', 'user_has_role.user_id')
            ->join('user_roles', 'user_has_role.role_id', '=', 'user_roles.id')
            ->whereIn('user_roles.name', ['Admin', 'Super Admin'])
            ->pluck('users.id');
        foreach ($adminUserIds as $userId) {
            $eventChannels[] = new PrivateChannel("mail.{$userId}.adminprev.unassigned");
            $eventChannels[] = new PrivateChannel("mail.{$userId}.adminprev.new");
        }


        return $eventChannels;
    }
}
