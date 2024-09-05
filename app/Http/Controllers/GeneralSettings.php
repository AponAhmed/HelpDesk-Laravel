<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Option;
use App\Models\User;
use App\Models\UserRole;

class GeneralSettings extends Controller
{
    //
    private $defaultValues = [
        "time_zone" => 6,
        "data_per_page" => 15,
        "ip_restricted" => 0,
        "release_control" => 1,
    ];
    public function index()
    {
        $roles = UserRole::where("name", "!=", "Merchandiser")->get();
        return view("SettingModules.general")->with([
            "settings" => $this,
            "users" => User::where('status', '1')->get(),
            "roles" => $roles,
        ]);
    }

    function releaseRoles()
    {
        $stepsStr = $this->get_option('releaseStep', true);
        $steps = json_decode($stepsStr, true);
        $stepsArray = array();
        if (is_array($steps) && count($steps) > 0) {
            foreach ($steps as $k => $step) {
                $stepsArray[$k] = $step;
                if ($step['type'] == 'role') {
                    $role = UserRole::find($step['id']);
                    if ($role) {
                        $stepsArray[$k]['name'] = $role->name;
                    } else {
                        $stepsArray[$k]['name'] = 'Unassigned';
                    }
                } else if ($step['type'] == 'user') {
                    $user = User::find($step['id']);
                    if ($user) {
                        $stepsArray[$k]['name'] = $user->name;
                    } else {
                        $stepsArray[$k]['name'] = 'Unassigned';
                    }
                }
            }
        }
        return $stepsArray;
    }

    public function store(Request $req)
    {
        $options = [];
        parse_str($req->settings, $options);
        if (isset($options["option"])) {
            $optionArr = $options["option"];
            foreach ($optionArr as $k => $val) {
                $this->add_option($k, $val);
            }
        }
        if (isset($options["optionGlobal"])) {
            foreach ($options["optionGlobal"] as $k => $val) {
                $this->add_option($k, $val, true);
            }
        }

        echo 1;
    }

    /**
     * get Option value by key
     * @param string of Option Key
     * @return string of option Value
     */
    public function get_option(string $k, $global = false)
    {
        $userId = Auth()->user()->id;
        if ($global) {
            $userId = 0;
        }
        $opt = Option::where(["key" => $k, "user" => $userId]);
        if ($opt->count() > 0) {
            return $opt->first()->val;
        } else {
            if (array_key_exists($k, $this->defaultValues)) {
                return $this->defaultValues[$k];
            } else {
                return "";
            }
        }
    }
    /**
     * Set Option value by key
     * @param key string
     * @param Value String
     * @param bollean true for set a option as global;
     * @return boolean
     */
    public function add_option(string $key, $val, $global = false)
    {
        $userId = Auth()->user()->id;
        if ($global) {
            $userId = 0;
        }
        $exist = Option::where(["key" => $key, "user" => $userId]);
        if (is_array($val)) {
            $val = json_encode($val);
        }
        if ($exist->count() > 0) {
            //Option already Exist
            return Option::where(["key" => $key, "user" => $userId])->update([
                "val" => $val,
            ]);
        } else {
            //not Exist
            return Option::create([
                "key" => $key,
                "val" => $val,
                "user" => $userId,
            ]);
        }
    }

    /**
     * Get Allowed IP address
     * @return array of IP address
     */
    public function allowed_ip()
    {
        $ipString = $this->get_option("restricted_ip", true);
        if (!empty($ipString)) {
            return json_decode($ipString, true);
        }
        return [];
    }
}
