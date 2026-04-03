<?php

namespace App\Http\Controllers;

use App\Models\Dana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DanaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       if (!\App\Helpers\PermissionHelper::check('manage_master')) {
           abort(403, 'Unauthorized access to Dana Master.');
       }
       return view('admin.dana.index');
    }
    public function datatable(Request $request){
        $number = $request->value ?? 50;
        $query = Dana::query();
        $query = auth()->user()->applyDataRestriction($query, 'user_id', 'manage_master');
        if($request->search){
            $query->where('name','like','%'.$request->search.'%')
            ->orWhere('company_name','like','%'.$request->search.'%')
            ->orWhere('phone_no','like','%'.$request->search.'%');
        }
        $dana = $query->latest()->paginate($number);
        return view('admin.dana.datatable',compact('dana'));
    }
    public function dana_master_list(Request $request){
        $dana = Dana::where('status',1)->get();
        return view('admin.dana.list',compact('dana'));
    }

    public function edit_modal($id){
        if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
            return '<div class="alert alert-danger m-3">Access Denied! You do not have permission to edit records.</div>';
        }
        $dana = Dana::find($id);
        return view('admin.dana.modal',compact('dana'));
    }

     public function change_status($id){
        if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
            return ['result' => -1, 'message' => 'Access Denied! You do not have permission to change status.'];
        }
        $dana = Dana::find($id);
        if($dana){
            $dana->status = $dana->status == 1 ? 0 : 1;
            $dana->save();
            $data = [
                'result' => 1,
                'message' => 'Status Changed Successfully'
            ];
        } else {
            $data = ['result' => 0, 'message' => 'Dana NOT Found!'];
        }
        return $data;
    }

    public function delete($id)
    {
        if (auth()->user()->role_as != 'Admin') {
            return ['result' => -1, 'message' => 'Access Denied! Only Admin can delete records.'];
        }
        $dana = Dana::find($id);
        if($dana) {
            $dana->delete();
            $data = 
            [
                'result' => 1,
                'message' => 'Deleted Successfully',
            ];
        } else {
            $data = ['result' => 0, 'message' => 'Dana NOT Found!'];
        }
        return $data;
    }
    public function store(Request $request)
    {
        $dana = Dana::find($request->dana_id);
        if($dana){
            // Update
            if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
                return ['result' => -1, 'message' => 'Access Denied! You do not have permission to edit records.'];
            }
            $check = Dana::where('name',$request->name)->where('id','!=',$dana->id)->first();
            if($check){
                $data = [
                    'result' => -1,
                    'message' => 'Dana Name Already Used',
                    'from' => 'Dana Master'
                ];
            }else{
                $dana->name = $request->name;
                $dana->company_name = $request->company_name;
                $dana->phone_no = $request->phone_no;
                $dana->alert_min_stock = $request->alert_min_stock;
                $dana->alert_max_stock = $request->alert_max_stock;
                $dana->order_qty = $request->order_qty;
                $dana->save();
                $data = [
                    'result' => 1,
                    'message' => 'Dana Master Updated Successfully',
                    'from' => 'Dana Master'
                ];
            }
        }else{
            // Create
            if (!\App\Helpers\PermissionHelper::check('manage_master', 'add')) {
                return ['result' => -1, 'message' => 'Access Denied! You do not have permission to add records.'];
            }
            $check = Dana::where('name',$request->name)->first();
            if($check){
                $data = [
                    'result' => -1,
                    'message' => 'Dana Name Already Used',
                    'from' => 'Dana Master'
                ];
            }else{
                $dana = new Dana();
                $dana->name = $request->name;
                $dana->company_name = $request->company_name;
                $dana->phone_no = $request->phone_no;
                $dana->alert_min_stock = $request->alert_min_stock;
                $dana->alert_max_stock = $request->alert_max_stock;
                $dana->order_qty = $request->order_qty;
                $dana->user_id = auth()->user()->id;
                $dana->status = 1;
                $dana->save();
                $data = [
                    'result' => 1,
                    'message' => 'Dana Master Added Successfully',
                    'from' => 'Dana Master'
                ];
            }
        }
        return $data;
    }
}
