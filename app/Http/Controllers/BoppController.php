<?php

namespace App\Http\Controllers;

use App\Models\Bopp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoppController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       if (!\App\Helpers\PermissionHelper::check('manage_master')) {
           abort(403, 'Unauthorized access to BOPP Master.');
       }
       return view('admin.bopp.index');
    }
    public function datatable(Request $request){
        $number = $request->value ?? 50;
        $query = Bopp::query();
        $query = auth()->user()->applyDataRestriction($query, 'user_id', 'manage_master');
        if($request->search){
            $query->where('name','like','%'.$request->search.'%')
            ->orWhere('company_name','like','%'.$request->search.'%')
            ->orWhere('phone_no','like','%'.$request->search.'%');
        }
        $bopp = $query->latest()->paginate($number);
        return view('admin.bopp.datatable',compact('bopp'));
    }
    public function bopp_master_list(Request $request){
        $bopp = Bopp::where('status',1)->get();
        return view('admin.bopp.list',compact('bopp'));
    }

    public function edit_modal($id){
        if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
            return '<div class="alert alert-danger m-3">Access Denied! You do not have permission to edit records.</div>';
        }
        $bopp = Bopp::find($id);
        return view('admin.bopp.modal',compact('bopp'));
    }

     public function change_status($id){
        if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
            return ['result' => -1, 'message' => 'Access Denied! You do not have permission to change status.'];
        }
        $bopp = Bopp::find($id);
        if($bopp){
            $bopp->status = $bopp->status == 1 ? 0 : 1;
            $bopp->save();
            $data = [
                'result' => 1,
                'message' => 'Status Changed Successfully'
            ];
        } else {
            $data = ['result' => 0, 'message' => 'BOPP NOT Found!'];
        }
        return $data;
    }

    public function delete($id)
    {
        if (auth()->user()->role_as != 'Admin') {
            return ['result' => -1, 'message' => 'Access Denied! Only Admin can delete records.'];
        }
        $bopp = Bopp::find($id);
        if($bopp) {
            $bopp->delete();
            $data = 
            [
                'result' => 1,
                'message' => 'Deleted Successfully',
            ];
        } else {
            $data = ['result' => 0, 'message' => 'BOPP NOT Found!'];
        }
        return $data;
    }
    public function store(Request $request)
    {
        $bopp = Bopp::find($request->bopp_id);
        if($bopp){
            // Update
            if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
                return ['result' => -1, 'message' => 'Access Denied! You do not have permission to edit records.'];
            }
            $check = Bopp::where('name',$request->name)->where('id','!=',$bopp->id)->first();
            if($check){
                $data = [
                    'result' => -1,
                    'message' => 'Bopp Name Already Used',
                    'from' => 'Bopp Master'
                ];
            }else{
                $bopp->name = $request->name;
                $bopp->company_name = $request->company_name;
                $bopp->phone_no = $request->phone_no;
                $bopp->alert_min_stock = $request->alert_min_stock;
                $bopp->alert_max_stock = $request->alert_max_stock;
                $bopp->order_qty = $request->order_qty;
                $bopp->save();
                $data = [
                    'result' => 1,
                    'message' => 'Bopp Master Updated Successfully',
                    'from' => 'Bopp Master'
                ];
            }
        }else{
            // Create
            if (!\App\Helpers\PermissionHelper::check('manage_master', 'add')) {
                return ['result' => -1, 'message' => 'Access Denied! You do not have permission to add records.'];
            }
            $check = Bopp::where('name',$request->name)->first();
            if($check){
                $data = [
                    'result' => -1,
                    'message' => 'Bopp Name Already Used',
                    'from' => 'Bopp Master'
                ];
            }else{
                $bopp = new Bopp();
                $bopp->name = $request->name;
                $bopp->company_name = $request->company_name;
                $bopp->phone_no = $request->phone_no;
                $bopp->alert_min_stock = $request->alert_min_stock;
                $bopp->alert_max_stock = $request->alert_max_stock;
                $bopp->order_qty = $request->order_qty;
                $bopp->user_id = auth()->user()->id;
                $bopp->status = 1;
                $bopp->save();
                $data = [
                    'result' => 1,
                    'message' => 'Bopp Master Added Successfully',
                    'from' => 'Bopp Master'
                ];
            }
        }
        return $data;
    }
}
