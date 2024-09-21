<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Option extends Model
{
    use HasFactory;
    protected $fillable = ['key', 'val', 'user'];
    public $timestamps = false;


    public static function set($name, $value, $global = false)
    {
        $userId = Auth::user()->id;
        if ($global) {
            $userId = 0;
        }
        return self::updateOrCreate(['key' => $name, 'user' => $userId], ['val' => $value]);
    }

    public static function get($name, $default = null, $global = false)
    {
        $userId = Auth::user()->id;
        if ($global) {
            $userId = 0;
        }
        $setting = self::where('key', $name)->where('user', $userId)->first();
        return $setting ? $setting->val : $default;
    }
}
