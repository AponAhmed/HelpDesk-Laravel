<?php

namespace App\Models;

use App\Traits\DataModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewFilter extends Model
{
    use HasFactory;
    use DataModel;
    protected $fillable = ["role", "keys", "user", "department", "status"];
    //protected $hidden = ["user", "keys", "created_at", "department"];
    protected $appends = ["department_name", "created_by"];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        //$this->directory = $this->setDirectory();
        //$this->deepartment_name = "adssdas";
        $this->DefaultFilter();
    }

    function department()
    {
        $dep = $this->hasOne(Department::class, "id", "department");
        if ($dep->count() > 0) {
            return $dep->first();
        } else {
            return new Department();
        }
    }
    function user()
    {
        $user = $this->hasOne(User::class, "id", "user");
        if ($user->count() > 0) {
            return $user->first();
        } else {
            return new User();
        }
    }

    function getDepartmentNameAttribute()
    {
        return $this->department_name = $this->department()->name;
    }

    function getCreatedByAttribute()
    {
        if (Auth()->user()->id == $this->user) {
            return $this->created_by = "Me";
        }
        return $this->created_by = $this->user()->name;
    }
}
