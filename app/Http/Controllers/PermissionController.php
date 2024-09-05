<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Http\Controllers\SidebarController;
use App\Models\Permission;
use App\Models\UserHasRole;
use App\Models\UserRole;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
  // function __construct()
  // {
  //   $this->middleware("auth");
  //   $this->middleware('access:settings,permission,view');
  // }

  /**
   * View of Permission Center
   */
  public function index()
  {
    $users = User::all();
    $roles = UserRole::where("name", "!=", "Super Admin")->get();

    return view("SettingModules.permission.index")->with([
      "users" => $users,
      "roles" => $roles,
      "modulesInfo" => new SidebarController(),
    ]);
  }

  public function createRole(Request $req)
  {
    if (!empty($req->name)) {
      $role = new UserRole();
      $role->name = $req->name;
      $role->guard_name = "web";
      return $role->save();
    }
  }

  public function deleteRole($id)
  {
    $info = [];
    $role = UserRole::find($id);
    if ($role) {
      if ($role->delete()) {
        UserHasRole::where("role_id", "=", $id)->delete();
        $info["error"] = false;
        $info["msg"] = "User Role Deleted !";
      } else {
        $info["error"] = true;
        $info["msg"] = "Failed to Deleted User Role !";
      }
    }
    echo json_encode($info);
  }

  public function putPermission(Request $req)
  {
    $wh = [
      "model_id" => $req->id,
      "model_type" => $req->model,
    ];
    $AccessArr = [];
    parse_str($req->permissionData, $AccessArr);
    //dd($AccessArr);
    $AccessStr = json_encode($AccessArr);

    $permission = Permission::where($wh);
    //dd($permission);
    if ($permission->count() != 0) {
      return Permission::where($wh)->update(["permission" => $AccessStr]);
    } else {
      $permission = new Permission($wh);
      $permission->permission = $AccessStr;
      return $permission->save();
    }
  }

  public function getPermission(Request $req)
  {
    $type = $req->model;
    $id = $req->id;
    $wh = ["model_type" => $type, "model_id" => $id];
    $permissions = Permission::where($wh)->first();
    if ($permissions) {
      return $permissions;
    } else {
      if ($type == "role") {
        return new Permission();
      } else {
        $user = User::find($id);
        $roleID = $user->roleID;
        $wh = ["model_type" => "role", "model_id" => $roleID];
        $permissions = Permission::where($wh)->first();
        if ($permissions) {
          return $permissions;
        }
      }
    }
    //dd($permissions);
  }
}
