<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserHasRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\UserRole;
use App\Traits\DataFilter;

class UserController extends Controller
{
    use DataFilter;

    // function __construct()
    // {
    //     $this->middleware("auth");
    //     $this->middleware('access:settings,user,view');
    // }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$users = User::orderBy("created_at", "asc")->paginate(5);
        return view("SettingModules.user.list");
    }

    /**
     * To get Data in json JSON
     * @return JSON string
     */
    public function listData()
    {
        $this->itemPerPage();
        $data = User::select(['users.id', 'users.name', 'users.email', 'users.status', 'users.created_at'])
            ->orderBy("users.created_at", "DESC")
            ->join('user_has_role', 'users.id', '=', 'user_has_role.user_id')
            ->join('user_roles', 'user_has_role.role_id', '=', 'user_roles.id')
            ->where('user_roles.name', '!=', 'Super Admin');

        $data = $this->customFilter($data);
        $data = $this->customSearch($data, ['users.name', 'users.email', 'user_roles.name']);

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
        $roles = UserRole::allRoles();
        $data = [];
        if (!empty($roles)) {
            $data["roles"] = $roles;
        }
        if ($id) {
            $userData = User::find($id);
        } else {
            $userData = new User();
        }
        $data["userData"] = $userData;
        return view("SettingModules.user.create")->with($data);
    }

    /**
     * Get a validator for an incoming registration request.
     * @param  object $data Posted Data from Request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator($data)
    {

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validator->errors()
            ], 401);
        } else {
            return true;
        }
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
        $validate = $this->validator($data);
        if ($validate) {
            try {
                $user = new User();
                $user->name = $data["name"];
                $user->email = $data["email"];
                $user->password = Hash::make($data["password"]);
                //$user->assignRole($data["userRole"]);
                if ($user->save()) {
                    UserHasRole::create(['user_id' => $user->id, 'role_id' => $data["userRole"]]);
                    echo 1;
                }
            } catch (\Exception $e) {
                // do task when error
                echo $e->getMessage(); // insert query
            }
        } else {
            //Return Error of as Response
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            //
            $user = User::find($id);
            $user->name = $request->name;
            $user->email = $request->email;
            if (!empty($request->password)) {
                $user->password = Hash::make($request->password);
            }
            UserHasRole::where("user_id", "=", $user->id)->delete();
            UserHasRole::create(['user_id' => $user->id, 'role_id' => $request->userRole]);
            if ($user->save()) {
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $info = [];
        if ($id) {
            $user = User::find($id);
            if ($user) {
                $hasRole = $user->userHasRole();
                $name = $user->name;
                if ($user->delete()) {
                    $hasRole->delete();
                    $info["error"] = false;
                    $info["msg"] = "'$name' Removed from User !";
                }
            } else {
                $info["error"] = true;
                $info["msg"] = "User Not Found !";
            }
        } else {
            $info["error"] = true;
            $info["msg"] = "Primary Key should not be Empty";
        }
        echo json_encode($info);
    }
}
