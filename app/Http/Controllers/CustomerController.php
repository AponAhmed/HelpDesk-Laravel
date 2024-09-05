<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Traits\DataFilter;

class CustomerController extends Controller
{
    use DataFilter;
    // public function __construct()
    // {
    //     $this->middleware("auth");
    //     $this->middleware("access:settings,customer,view");
    // }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return view("SettingModules.customer.list");
    }

    /**
     * Get Data With pagination
     * @return JSON data
     */
    public function listData()
    {
        $this->itemPerPage();
        $data = Customer::orderBy("created_at", "DESC");

        $data = $this->customFilter($data);
        $data = $this->customSearch($data, ['name', 'email']);

        $data = $data->paginate($this->MAX_IN_PAGE);
        $data = $this->addExtraData($data);
        return response()->json($data);
        //dd(new Customer());

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id = false)
    {
        //
        $data = new Customer();
        if ($id) {
            $data = Customer::find($id);
        }
        return View("SettingModules.customer.create")->with(["customer" => $data]);
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
            $customer = new Customer();
            $customer->name = $data["name"];
            $customer->email = $data["email"];
            if ($customer->save()) {
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
            $customer = Customer::find($id);
            $customer->name = $data["name"];
            $customer->email = $data["email"];
            if ($customer->save()) {
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
            $Customer = Customer::find($id);
            if ($Customer) {
                if ($Customer->delete()) {
                    $info["error"] = false;
                    $info["msg"] = "Customer Deleted !";
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
