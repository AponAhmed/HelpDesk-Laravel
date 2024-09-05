<?php

namespace App\Models;

use App\Traits\DataModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory;
    use DataModel;
    protected $fillable = [
        "name",
        "email"
    ];
    //protected $hidden = ['email'];
    protected $casts = [
        "created_at" => "date:F j, Y, g:i a",
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(MailList::class, "customer");
    }
}
