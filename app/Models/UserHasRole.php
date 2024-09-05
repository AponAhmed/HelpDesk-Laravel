<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHasRole extends Model
{
    use HasFactory;
    protected $table = "user_has_role";
    protected $fillable = ["user_id", "role_id"];
    //public $timestamps = false;
}
