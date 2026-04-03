<?php

namespace App\Http\Controllers;

use App\Models\Loop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       if (!\App\Helpers\PermissionHelper::check('manage_master')) {
           abort(403, 'Unauthorized access to Loop Master.');
       }
       return view('admin.loop.index');
    }
    public function datatable(Request $request){
        if (!\App\Helpers\PermissionHelper::check('manage_master')) {
            return '<div class="alert alert-danger m-3">Access Denied!</div>';
        }
        $number = $request->value ?? 50;
        $query = Loop::query();
        $query = auth()->user()->applyDataRestriction($query, 'user_id', 'manage_master');
        if($request->search){
            $query->where('name','like','%'.$request->search.'%')
            ->orWhere('company_name','like','%'.$request->search.'%')
            ->orWhere('phone_no','like','%'.$request->search.'%');
        }
        $loop_colors = $query->latest()->paginate($number);
        return view('admin.loop.datatable',compact('loop_colors'));
    }
    public function loop_master_list(Request $request){
        $loop_colors = Loop::where('status',1)->get();
        return view('admin.loop.list',compact('loop_colors'));
    }

    public function edit_modal($id){
        $loop_color = Loop::find($id);
        return view('admin.loop.modal',compact('loop_color'));
    }

     public function change_status($id){
        if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
            return ['result' => -1, 'message' => 'Access Denied! You do not have permission to change status.'];
        }
        $loop_color = Loop::find($id);
        if($loop_color){
            $loop_color->status = $loop_color->status == 1 ? 0 : 1;
            $loop_color->save();
            $data = [
                'result' => 1,
                'message' => 'Status Changed Successfully'
            ];
        }
        return $data;
    }

    public function delete($id)
    {
        if (auth()->user()->role_as != 'Admin') {
            return ['result' => -1, 'message' => 'Access Denied! Only Admin can delete records.'];
        }
        $loop_color = Loop::find($id);
        $loop_color->delete();
        $data = 
        [
            'result' => 1,
            'message' => 'Deleted Successfully',
        ];
        return $data;
    }
    public function store(Request $request)
    {
        $loop_color = Loop::find($request->loop_id);
        if($loop_color){
            // Update
            if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
                return ['result' => -1, 'message' => 'Access Denied! You do not have permission to edit records.'];
            }
            $check = Loop::where('name',$request->name)->where('id','!=',$loop_color->id)->first();
            if($check){
                $data = [
                    'result' => -1,
                    'message' => 'Loop Name Already Used',
                    'from' => 'Loop Master'
                ];
            }else{
                $loop_color->name = $request->name;
                $loop_color->company_name = $request->company_name;
                $loop_color->phone_no = $request->phone_no;
                $loop_color->alert_min_stock = $request->alert_min_stock;
                $loop_color->alert_max_stock = $request->alert_max_stock;
                $loop_color->order_qty = $request->order_qty;
                $loop_color->save();
                $data = [
                    'result' => 1,
                    'message' => 'Loop Master Updated Successfully',
                    'from' => 'Loop Master'
                ];
            }
        }else{
            // Create
            if (!\App\Helpers\PermissionHelper::check('manage_master', 'add')) {
                return ['result' => -1, 'message' => 'Access Denied! You do not have permission to add records.'];
            }
            $check = Loop::where('name',$request->name)->first();
            if($check){
                $data = [
                    'result' => -1,
                    'message' => 'Loop Name Already Used',
                    'from' => 'Loop Master'
                ];
            }else{
                $loop_color = new Loop();
                $loop_color->name = $request->name;
                $loop_color->company_name = $request->company_name;
                $loop_color->phone_no = $request->phone_no;
                $loop_color->alert_min_stock = $request->alert_min_stock;
                $loop_color->alert_max_stock = $request->alert_max_stock;
                $loop_color->order_qty = $request->order_qty;
                $loop_color->user_id = auth()->user()->id;
                $loop_color->status = 1;
                $loop_color->save();
                $data = [
                    'result' => 1,
                    'message' => 'Loop Master Added Successfully',
                    'from' => 'Loop Master'
                ];
            }
        }
        return $data;
    }
}
