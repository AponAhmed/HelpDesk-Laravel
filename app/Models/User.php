<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\DataModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, DataModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ["roles", "roleID", "permission"];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function __construct()
    {
        $this->DefaultFilter();
    }

    /**
     * Append role Name as role Attribute
     */
    public function getrolesAttribute()
    {
        $role = $this->userRole();
        return $this->roles = $role->name;
    }

    public function getChannelID()
    {
        $role = $this->userRole();
        if (isset($role->name) && $role->name == "Super Admin" || $role->name == "Admin") {
            return "adminprev";
        }
        return "userPrev";
    }

    /**
     * Append role ID as roleID  Attribute
     */
    public function getroleIDAttribute()
    {
        $role = $this->userRole();
        return $this->roleID = $role->id;
    }
    /**
     * Append Permission Attribute
     */
    public function getpermissionAttribute()
    {
        return $this->permission = $this->user_permissions();
    }

    /**
     * User Role
     * @return UserRole Object
     */
    public function userRole()
    {
        $hasRole = $this->userHasRole();
        if (!empty($hasRole)) {
            $roleId = $hasRole->role_id;
            $userRole = UserRole::find($roleId);
            return $userRole;
        } else {
            return new UserRole(["name" => "None"]);
        }
    }

    /**
     * Get User Has Role Relational Data
     * @return UserHasRole Object
     */
    function userHasRole()
    {
        return $this->hasOne(UserHasRole::class, 'user_id', 'id')->first();
    }

    /**
     * User Permission
     * @return Array of Permission String
     */
    public function user_permissions()
    {
        $userAssignedPermisssion = Permission::where([
            "model_type" => "user",
            "model_id" => $this->id,
        ]);
        if ($userAssignedPermisssion->count() != 0) {
            $arr = json_decode($userAssignedPermisssion->first()->permission);
            if (isset($arr->permission)) {
                return $arr->permission;
            }
        } else {
            $role = $this->userRole();
            $RolePermisssion = Permission::where([
                "model_type" => "role",
                "model_id" => $role->id,
            ]);
            if ($RolePermisssion->count() != 0) {
                $arr = json_decode($RolePermisssion->first()->permission);
                if (isset($arr->permission)) {
                    return $arr->permission;
                }
            } else {
                return "";
            }
        }
    }

    function mailes()
    {
        return $this->hasMany(MailList::class, 'user');
    }

    public function canSend()
    {
        return true;
    }

    function canRelease()
    {
        return true;
    }
}
