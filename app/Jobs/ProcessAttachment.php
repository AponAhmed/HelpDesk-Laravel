<?php

namespace App\Jobs;

use App\Models\MailList;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAttachment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $ListID;
    protected MailList $list;

    public function __construct(int $id)
    {
        $this->ListID = $id;
    }

    public function handle(): void
    {
        $this->list = MailList::find($this->ListID);

        // Check if the list was found
        if ($this->list === null) {
            Log::error('MailList not found with ID: ' . $this->ListID);
            return; // Exit if the MailList is not found
        }

        // Set user ID to 0
        $this->list->setUser(0);

        // Optionally log the successful operation
        Log::info('User ID set to 0 for MailList ID: ' . $this->ListID);
    }
}
