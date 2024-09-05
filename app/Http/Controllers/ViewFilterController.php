<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\ViewFilter;
use App\Traits\DataFilter;
use Illuminate\Http\Request;

class ViewFilterController extends Controller
{
    use DataFilter;

    public $filterRoles;

    public function __construct()
    {
        $this->filterRoles = [
            "web" => "Web",
            "mobile" => "Mobile",
            "email" => "Email",
            "word" => "Word",
        ];
    }
    function index()
    {
        $data = ViewFilter::all();
        //dd($data);
        return view("settingModules.viewFilter.list");
    }

    public function create($id = false)
    {
        if ($id) {
            $viewFilter = ViewFilter::find($id);
        } else {
            $viewFilter = new ViewFilter();
        }
        $departments = Department::all();
        return view("settingModules.viewFilter.create")->with([
            "ViewFilter" => $viewFilter,
            "departments" => $departments,
            "filterRoles" => $this->filterRoles,
        ]);
    }

    public function listData()
    {
        $this->itemPerPage();
        $data = ViewFilter::orderBy("created_at", "DESC");

        $data = $this->customFilter($data);
        $data = $this->customSearch($data, ['role', 'keys']);

        $data = $data->paginate($this->MAX_IN_PAGE);
        $data = $this->addExtraData($data);
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $data = $request->all();
        try {
            $ViewFilter = new ViewFilter();
            $ViewFilter->role = $data["role"];
            $ViewFilter->keys = $data["keys"];
            $ViewFilter->department = $data["department"];
            $ViewFilter->user = Auth()->user()->id;
            if ($ViewFilter->save()) {
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $data = $request->all();
        try {
            $ViewFilter = ViewFilter::find($id);
            $ViewFilter->role = $data["role"];
            $ViewFilter->keys = $data["keys"];
            $ViewFilter->department = $data["department"];
            if ($ViewFilter->save()) {
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
            $ViewFilter = ViewFilter::find($id);
            if ($ViewFilter) {
                if ($ViewFilter->delete()) {
                    $info["error"] = false;
                    $info["msg"] = "Filter Deleted !";
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
}
