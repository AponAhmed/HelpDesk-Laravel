<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
  use HasFactory;
  protected $table = "user_roles";
  protected $fillable = ["name", "guard_name"];

  /**
   * Get All User Roles
   * @return UserRole cullection
   *
   */
  public static function allRoles()
  {
    return UserRole::where("name", "!=", "Super Admin")->get();
  }
  
}
