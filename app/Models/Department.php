<?php

namespace App\Models;

use App\Http\Controllers\GoogleService\OAuth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DataModel;

class Department extends Model
{
    use HasFactory;
    use DataModel;

    protected $hidden = ["oauth_token"];
    protected $fillable = [];
    protected $appends = ["OAuth"];

    public function __construct()
    {
        parent::__construct();
        $this->DefaultFilter();
    }

    public function getOAuthAttribute()
    {
        if (empty($this->oauth_token)) {
            return false;
        } else {
            $auth = new OAuth(config('app.google_app_credentials'), $this->oauth_token());
            $auth->tokenCheck();
            if ($auth->tokenStatus == "refreshed") {
                $this->oauth_token($auth->token);
                $this->save();
            }
            if ($auth->connect) {
                return true;
            } else {
                return false;
            }
        }
    }


    function oauth_token($token = false)
    {
        if (!$token) {
            $token = json_decode($this->oauth_token, true);
            if (!is_array($token)) {
                return [];
            }
            return $token;
        } else {
            $this->oauth_token = json_encode($token);
        }
    }
}
