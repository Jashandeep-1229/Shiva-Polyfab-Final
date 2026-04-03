<?php

namespace App\Http\Controllers;

use App\Models\BlockageReason;
use Illuminate\Http\Request;

class BlockageReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!\App\Helpers\PermissionHelper::check('master_management') && !\App\Helpers\PermissionHelper::check('blockage_reason')) {
            abort(403, 'Unauthorized access to Blockage Reason Master.');
        }
        $type = $request->type ?? 'printing';
        return view('admin.blockage_reason.index',compact('type'));
    }
    public function datatable(Request $request)
    {
        $number = $request->value ?? 50;
        $query = BlockageReason::where('type',$request->type);
        $query = auth()->user()->applyDataRestriction($query, 'user_id', 'master_management');
        if($request->search){
            $query->where(function($q) use ($request){
                $q->where('name','like','%'.$request->search.'%');
            });
        }
        $blockage_reason = $query->latest('id')->paginate($number);
        return view('admin.blockage_reason.datatable',compact('blockage_reason'));
    }

    public function edit_modal($id){
        if (!\App\Helpers\PermissionHelper::check('blockage_reason', 'edit') && !\App\Helpers\PermissionHelper::check('master_management', 'edit')) {
            return '<div class="alert alert-danger m-3">Access Denied!</div>';
        }
        $blockage_reason = BlockageReason::find($id);
        return view('admin.blockage_reason.modal',compact('blockage_reason'));
    }
    public function change_status($id){
        $blockage_reason = BlockageReason::find($id);
        if($blockage_reason){
            $blockage_reason->status = $blockage_reason->status == 1 ? 0 : 1;
            $blockage_reason->save();
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
        $blockage_reason = BlockageReason::find($id);
        $blockage_reason->delete();
        $data = 
        [
            'result' => 1,
            'message' => 'Deleted Successfully',
        ];
        return $data;
    }
    public function store(Request $request)
    {
        $blockage_reason = BlockageReason::find($request->blockage_reason_id);
        if($blockage_reason){
            if (!\App\Helpers\PermissionHelper::check('blockage_reason', 'edit') && !\App\Helpers\PermissionHelper::check('master_management', 'edit')) {
                return ['result' => -1, 'message' => 'Access Denied!'];
            }
            $check = BlockageReason::where('name',$request->name)->where('type',$request->type)->where('id','!=',$blockage_reason->id)->first();
            if($check){
                $data = [
                    'result' => -1,
                    'message' => 'Blockage Reason Already Used',
                    'from' => 'Blockage Reason Master'
                ];
            }else{
                $blockage_reason->name = $request->name;
                $blockage_reason->save();
                $data = [
                    'result' => 1,
                    'message' => 'Blockage Reason Updated Successfully',
                    'from' => 'Blockage Reason Master'
                ];
            }
        }else{
            if (!\App\Helpers\PermissionHelper::check('blockage_reason', 'add') && !\App\Helpers\PermissionHelper::check('master_management', 'add')) {
                return ['result' => -1, 'message' => 'Access Denied!'];
            }
            $check = BlockageReason::where('name',$request->name)->where('type',$request->type)->first();
            if($check){
                $data = [
                    'result' => -1,
                    'message' => 'Blockage Reason Already Used',
                    'from' => 'Blockage Reason Master'
                ];
            }else{
                $blockage_reason = new BlockageReason();
                $blockage_reason->type = $request->type;
                $blockage_reason->name = $request->name;
                $blockage_reason->user_id = auth()->user()->id;
                $blockage_reason->status = 1;
                $blockage_reason->save();
                $data = [
                    'result' => 1,
                    'message' => 'Blockage Reason Added Successfully',
                    'from' => 'Blockage Reason Master'
                ];
            }
        }
        return $data;
    }
}
