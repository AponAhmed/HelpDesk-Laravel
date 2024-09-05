<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\GoogleService\Drive;
use App\Http\Controllers\GoogleService\Gmail;
use App\Traits\DataFilter;
use App\Http\Controllers\GoogleService\OAuth;

//

class DepartmentController extends Controller
{
    use DataFilter;
    function __construct()
    {
        //$this->middleware("auth");
        //$this->middleware("access:settings,department,view");
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->getCode2AccessToken();
        return view("SettingModules.depertments.list");
    }

    function getCode2AccessToken()
    {
        if (
            isset($_GET["code"]) &&
            session()->has("OAuthLogin") &&
            !empty(session()->has("OAuthLogin"))
        ) {
            $departmentID = session()->get("OAuthLogin");
            //session()->forget("OAuthLogin");
            $code = $_GET["code"];
            $department = Department::find($departmentID);
            //Auth code to API token
            $auth = new OAuth(config('app.google_app_credentials'), $department->oauth_token());
            $auth->accesTokenByAuthCode($code);

            $department->oauth_token($auth->token);
            $department->save();
        }
    }

    /**
     * Get Data With pagination
     * @return JSON data
     */
    public function listData()
    {
        $this->itemPerPage();
        $data = Department::orderBy("created_at", "DESC");

        $data = $this->customFilter($data);
        $data = $this->customSearch($data, ['name', 'email']);

        $data = $data->paginate($this->MAX_IN_PAGE);
        $data = $this->addExtraData($data);
        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id = false)
    {
        //
        if ($id) {
            $department = Department::find($id);
            // $auth = new OAuth(config('app.google_app_credentials'), $department->oauth_token());
            // $auth->tokenCheck();
            // if ($auth->connect) {
            //     $drive = new Drive($auth->client);
            //     $gmail = new Gmail($auth->client);

            //     dd($gmail->get(1));
            // }
        } else {
            $department = new Department();
        }
        return view("SettingModules.depertments.create")->with([
            "department" => $department,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        try {
            $department = new Department();
            $department->name = $data["name"];
            $department->email = $data["email"];
            $department->signature = $data["signature"];
            $department->prefix = $data["prefix"];
            if ($department->save()) {
                echo 1;
            }
        } catch (\Exception $e) {
            // do task when error
            echo $e->getMessage(); // insert query
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Department  $department
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            //
            $department = Department::find($id);
            $department->name = $request->name;
            if ($department->email != $request->email) {
                $department->oauth_token = "";
            }
            $department->email = $request->email;
            $department->prefix = $request->prefix;
            $department->signature = $request->signature;
            if ($department->save()) {
                echo 1;
            }
        } catch (\Exception $e) {
            // do task when error
            echo $e->getMessage(); // insert query
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  department IID
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $info = [];
        if ($id) {
            $department = Department::find($id);
            if ($department) {
                if ($department->delete()) {
                    $info["error"] = false;
                    $info["msg"] = "Department Deleted !";
                }
            } else {
                $info["error"] = true;
                $info["msg"] = "Primary Key should be Empty";
            }
        } else {
            $info["error"] = true;
            $info["msg"] = "Primary Key should be Empty";
        }
        echo json_encode($info);
    }

    /**
     * Department OAuth validation
     * @param $id Department ID
     * @return Void {true,false,expired}
     */
    public function OAuthVal($id)
    {
        $department = Department::find($id);
        if ($department) {
            $auth = new OAuth(config('app.google_app_credentials'), $department->oauth_token());
            $auth->tokenCheck();
            if (!$auth->connect) {
                session()->put("OAuthLogin", $id);
                return ['authUrl' => $auth->login()];
            } else {
                return ['authUrl' => false];
            }
        }
        return ['authUrl' => false];
    }
}
