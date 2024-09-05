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
}
