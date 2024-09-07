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
    protected $boxChannel;
    /**
     * Create a new event instance.
     */
    public function __construct(MailList $mailList, $action = 'assign', $boxChannel = "new")
    {
        //
        $this->mailList = $mailList;
        $this->action = $action;
        $this->boxChannel = $boxChannel;
    }

    public function broadcastWith(): array
    {

        if ($this->action == 'remove') {
            return [
                'listItem' => $this->mailList->toArray(),
                'adminprev' => 'remove',
                'userPrev' => 'remove',
            ];
        }

        if ($this->action == "refresh") {
            return [
                'listItem' => $this->mailList->toArray(),
                'adminprev' => 'refresh',
                'userPrev' => 'refresh',
            ];
        }
        if ($this->action == "new") {
            return [
                'listItem' => $this->mailList->toArray(),
                'adminprev' => 'new',
                'userPrev' => 'prepend',
            ];
        }

        $adminAction = $this->action == "assign" ? 'remove' : 'prepend';
        $userAction = $this->action == "assign" ? 'prepend' : 'remove';


        return [
            'listItem' => $this->mailList->toArray(),
            'adminprev' => $adminAction,
            'userPrev' => $userAction,
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
        if ($this->action === 'assign' && $this->mailList->user != 0) {
            $eventChannels[] =  new PrivateChannel('mail.' . $this->mailList->user . ".userPrev." . $this->boxChannel);
        }

        $adminUserIds = DB::table('users')
            ->join('user_has_role', 'users.id', '=', 'user_has_role.user_id')
            ->join('user_roles', 'user_has_role.role_id', '=', 'user_roles.id')
            ->whereIn('user_roles.name', ['Admin', 'Super Admin'])
            ->pluck('users.id');
        foreach ($adminUserIds as $userId) {
            if ($this->action === 'unAssign') {
                $eventChannels[] = new PrivateChannel("mail.{$userId}.adminprev.unassigned");
                $eventChannels[] = new PrivateChannel("mail.{$userId}.userPrev.new");
            } else {
                $eventChannels[] = new PrivateChannel("mail.{$userId}.adminprev.unassigned");
                $eventChannels[] = new PrivateChannel("mail.{$userId}.adminprev." . $this->boxChannel);
            }
        }
        return $eventChannels;
    }
}
