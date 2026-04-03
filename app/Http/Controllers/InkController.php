<?php

namespace App\Http\Controllers;

use App\Models\Ink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       if (!\App\Helpers\PermissionHelper::check('manage_master')) {
           abort(403, 'Unauthorized access to Ink Master.');
       }
       return view('admin.ink.index');
    }
    public function datatable(Request $request){
        if (!\App\Helpers\PermissionHelper::check('manage_master')) {
            return '<div class="alert alert-danger m-3">Access Denied!</div>';
        }
        $number = $request->value ?? 50;
        $query = Ink::query();
        $query = auth()->user()->applyDataRestriction($query, 'user_id', 'manage_master');
        if($request->search){
            $query->where('name','like','%'.$request->search.'%')
            ->orWhere('company_name','like','%'.$request->search.'%')
            ->orWhere('phone_no','like','%'.$request->search.'%');
        }
        $ink = $query->latest()->paginate($number);
        return view('admin.ink.datatable',compact('ink'));
    }
    public function ink_master_list(Request $request){
        $ink = Ink::where('status',1)->get();
        return view('admin.ink.list',compact('ink'));
    }

    public function edit_modal($id){
        $ink = Ink::find($id);
        return view('admin.ink.modal',compact('ink'));
    }

     public function change_status($id){
        $ink = Ink::find($id);
        if($ink){
            $ink->status = $ink->status == 1 ? 0 : 1;
            $ink->save();
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
        $ink = Ink::find($id);
        $ink->delete();
        $data = 
        [
            'result' => 1,
            'message' => 'Deleted Successfully',
        ];
        return $data;
    }
    public function store(Request $request)
    {
        $ink = Ink::find($request->ink_id);
        if($ink){
            $check = Ink::where('name',$request->name)->where('id','!=',$ink->id)->first();
            if($check){
                $data = [
                    'result' => -1,
                    'message' => 'Ink Name Already Used',
                    'from' => 'Ink Master'
                ];
            }else{
                $ink->name = $request->name;
                $ink->company_name = $request->company_name;
                $ink->phone_no = $request->phone_no;
                $ink->alert_min_stock = $request->alert_min_stock;
                $ink->alert_max_stock = $request->alert_max_stock;
                $ink->order_qty = $request->order_qty;
                $ink->save();
                $data = [
                    'result' => 1,
                    'message' => 'Ink Master Updated Successfully',
                    'from' => 'Ink Master'
                ];
            }
        }else{
            $check = Ink::where('name',$request->name)->first();
            if($check){
                $data = [
                    'result' => -1,
                    'message' => 'Ink Name Already Used',
                    'from' => 'Ink Master'
                ];
            }else{
                $ink = new Ink();
                $ink->name = $request->name;
                $ink->company_name = $request->company_name;
                $ink->phone_no = $request->phone_no;
                $ink->alert_min_stock = $request->alert_min_stock;
                $ink->alert_max_stock = $request->alert_max_stock;
                $ink->order_qty = $request->order_qty;
                $ink->user_id = auth()->user()->id;
                $ink->status = 1;
                $ink->save();
                $data = [
                    'result' => 1,
                    'message' => 'Ink Master Added Successfully',
                    'from' => 'Ink Master'
                ];
            }
        }
        return $data;
    }
}
