<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MailList;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailDetails extends Model
{
    use HasFactory;
    protected $table = "mail_details";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $hidden = [
        "id",
        "list_id",
        "created_at",
        "updated_at"
    ];
    protected $fillable = ["list_id", "msg_body", "header", "attachments"];

    public function mail_list(): BelongsTo
    {
        return $this->belongsTo(MailList::class, "list_id");
    }

    /**
     * Update the entire attachment data.
     */
    public function updateAttachmentData(array $newAttachments): void
    {
        // Set the new attachments data
        $this->attachments = json_encode($newAttachments);
        $this->save();
    }

    /**
     * Set processed state for inline attachments.
     *
     * @param bool $state
     * @return void
     */
    public function setAttachmentProcessed(bool $state): void
    {
        // Decode existing inline attachments
        $inlineData = json_decode($this->inlineAttachments, true) ?? [];

        // Update the processed property for each inline attachment
        foreach ($inlineData as &$attachment) {
            $attachment['processed'] = $state;
        }

        // Encode back to JSON and save
        $this->inlineAttachments = json_encode($inlineData);
        $this->save();
    }
}
