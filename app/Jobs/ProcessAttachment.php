<?php

namespace App\Jobs;

use App\Models\MailList;
use App\Services\AttachmentProcessor;
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
            return; // Exit if the MailList is not found
        }

        // Set user ID to 0
        //$this->list->MailDetails->setAttachmentProcessed(true);
        if (AttachmentProcessor::ProcessAllFiles($this->list->MailDetails->getAttachmentData())) {
            Log::info('All attachments processed successfully and found vulnerable for MailList ID: ' . $this->ListID);
            $this->list->MailDetails->setAttachmentProcessed('vulnerable');
        } else {
            $this->list->MailDetails->setAttachmentProcessed(true);
            Log::info('All attachments processed successfully and not found any vulnerable for MailList ID: ' . $this->ListID);
        }
    }
}
