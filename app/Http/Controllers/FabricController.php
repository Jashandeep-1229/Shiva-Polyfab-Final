<?php

namespace App\Http\Controllers;

use App\Models\Fabric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FabricController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       if (!\App\Helpers\PermissionHelper::check('manage_master')) {
           abort(403, 'Unauthorized access to Fabric Master.');
       }
       return view('admin.fabric.index');
    }
    public function datatable(Request $request){
        $number = $request->value ?? 50;
        $query = Fabric::query();
        $query = auth()->user()->applyDataRestriction($query, 'user_id', 'manage_master');
        if($request->search){
            $query->where(function($q) use ($request){
                $q->where('name','like','%'.$request->search.'%')
                ->orWhere('company_name','like','%'.$request->search.'%')
                ->orWhere('phone_no','like','%'.$request->search.'%');
            });
        }
        $fabric = $query->latest('id')->paginate($number);
        return view('admin.fabric.datatable',compact('fabric'));
    }
    public function fabric_master_list(Request $request){
        $fabric = Fabric::select('id', 'name')->where('status',1)->get();
        return view('admin.fabric.list',compact('fabric'));
    }

    public function edit_modal($id){
        if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
            return '<div class="alert alert-danger m-3">Access Denied! You do not have permission to edit records.</div>';
        }
        $fabric = Fabric::find($id);
        return view('admin.fabric.modal',compact('fabric'));
    }

     public function change_status($id){
        if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
            return ['result' => -1, 'message' => 'Access Denied! You do not have permission to change status.'];
        }
        $fabric = Fabric::find($id);
        if($fabric){
            $fabric->status = $fabric->status == 1 ? 0 : 1;
            $fabric->save();
            $data = [
                'result' => 1,
                'message' => 'Status Changed Successfully'
            ];
        } else {
            $data = ['result' => 0, 'message' => 'Fabric NOT Found!'];
        }
        return $data;
    }

    public function delete($id)
    {
        if (auth()->user()->role_as != 'Admin') {
            return ['result' => -1, 'message' => 'Access Denied! Only Admin can delete records.'];
        }
        $fabric = Fabric::find($id);
        $fabric->delete();
        $data = 
        [
            'result' => 1,
            'message' => 'Deleted Successfully',
        ];
        return $data;
    }
    public function store(Request $request)
    {
        $fabric = Fabric::find($request->fabric_id);
        if($fabric){
            // Update
            if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
                return ['result' => -1, 'message' => 'Access Denied! You do not have permission to edit records.'];
            }
            $check = Fabric::where('name',$request->name)->where('id','!=',$fabric->id)->first();
            if($check){
                $data = [
                    'result' => -1,
                    'message' => 'Fabric Name Already Used',
                    'from' => 'Fabric Master'
                ];
            }else{
                $fabric->name = $request->name;
                $fabric->company_name = $request->company_name;
                $fabric->phone_no = $request->phone_no;
                $fabric->alert_min_stock = $request->alert_min_stock;
                $fabric->alert_max_stock = $request->alert_max_stock;
                $fabric->order_qty = $request->order_qty;
                $fabric->save();
                $data = [
                    'result' => 1,
                    'message' => 'Fabric Master Updated Successfully',
                    'from' => 'Fabric Master'
                ];
            }
        }else{
            // Create
            if (!\App\Helpers\PermissionHelper::check('manage_master', 'add')) {
                return ['result' => -1, 'message' => 'Access Denied! You do not have permission to add records.'];
            }
            $check = Fabric::where('name',$request->name)->first();
            if($check){
                $data = [
                    'result' => -1,
                    'message' => 'Fabric Name Already Used',
                    'from' => 'Fabric Master'
                ];
            }else{
                $fabric = new Fabric();
                $fabric->name = $request->name;
                $fabric->company_name = $request->company_name;
                $fabric->phone_no = $request->phone_no;
                $fabric->alert_min_stock = $request->alert_min_stock;
                $fabric->alert_max_stock = $request->alert_max_stock;
                $fabric->order_qty = $request->order_qty;
                $fabric->user_id = auth()->user()->id;
                $fabric->status = 1;
                $fabric->save();
                $data = [
                    'result' => 1,
                    'message' => 'Fabric Master Added Successfully',
                    'from' => 'Fabric Master'
                ];
            }
        }
        return $data;
    }
}
