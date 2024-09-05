<?php

namespace App\Http\Controllers;

use App\Models\ContactList;
use Illuminate\Http\Request;


class BulkActionController extends Controller
{
    //
    private string $modelName = ""; //Name of the Model to be used for Bulk Action
    private string $modelFullName = ""; //Full Name of the Model
    private object $model; //Instance of Model Class
    private array $ids = []; //Array of IDs to be used for Bulk Action

    /**
     *  Array of Language Keys for the Model
     */
    private $lngModel = [];

    // public function __construct()
    // {
    //     $this->middleware("auth");
    // }

    function PreSet($request)
    {
        $this->modelName = $request->get('model');
        $this->modelFullName = 'App\Models\/' . $this->modelName;
        $this->modelFullName = str_replace("/", "", $this->modelFullName);

        $this->model = new $this->modelFullName();
        $this->ids = $request->get('ids');
        //Replace Model Name By LangModel
        if (array_key_exists($this->modelName, $this->lngModel)) {
            $this->modelName = $this->lngModel[$this->modelName];
        }
    }


    function delete(Request $request)
    {
        $this->PreSet($request);
        $resp = $this->model->whereIn('id', $this->ids)->delete();
        if ($resp) {
            return response()->json(['success' => true, 'message' => 'Deleted Successfully ' . count($this->ids) . ' ' . $this->modelName]);
        } else {
            return response()->json(['success' => false, 'message' => 'Something went wrong']);
        }
    }

    /**
     * Bulk Active status change
     */
    function active(Request $request)
    {
        $this->PreSet($request);
        $resp = $this->model->whereIn('id', $this->ids)->update(['status' => '1']);
        if ($resp) {
            return response()->json(['success' => true, 'message' => 'Activated Successfully ' . count($this->ids) . ' ' . $this->modelName]);
        } else {
            return response()->json(['success' => false, 'message' => 'Something went wrong']);
        }
    }
    /**
     * Bulk Inactive status change
     */
    function inactive(Request $request)
    {
        $this->PreSet($request);
        $resp = $this->model->whereIn('id', $this->ids)->update(['status' => '0']);
        if ($resp) {
            return response()->json(['success' => true, 'message' => 'Inactivated Successfully ' . count($this->ids) . ' ' . $this->modelName]);
        } else {
            return response()->json(['success' => false, 'message' => 'Something went wrong']);
        }
    }

    /**
     * Item Per Page Settings Set
     */
    function itemPerPage($number = 22)
    {
        if ($number) {
            session(['itemPerPage' => $number]);
        }
        return session('itemPerPage');
    }
}
