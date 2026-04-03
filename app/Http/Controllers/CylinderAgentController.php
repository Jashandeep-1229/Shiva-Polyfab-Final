<?php

namespace App\Http\Controllers;

use App\Models\CylinderAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CylinderAgentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       if (!\App\Helpers\PermissionHelper::check('cylinder_agent')) {
           abort(403, 'Unauthorized access to Cylinder Agent Master.');
       }
       return view('admin.cylinder_agent.index');
    }
    public function datatable(Request $request){
        $number = $request->value ?? 50;
        $query = CylinderAgent::query();
        $query = auth()->user()->applyDataRestriction($query, 'user_id', 'cylinder_agent');
        if($request->search){
            $query->where(function($q) use ($request){
                $q->where('name','like','%'.$request->search.'%');
            });
        }
        $cylinder_agent = $query->latest('id')->paginate($number);
        return view('admin.cylinder_agent.datatable',compact('cylinder_agent'));
    }
    public function cylinder_agent_master_list(Request $request){
        $cylinder_agent = CylinderAgent::select('id', 'name')->where('status',1)->get();
        return view('admin.cylinder_agent.list',compact('cylinder_agent'));
    }

    public function edit_modal($id){
        if (!\App\Helpers\PermissionHelper::check('cylinder_agent', 'edit')) {
            return '<div class="alert alert-danger m-3">Access Denied! You do not have permission to edit records.</div>';
        }
        $cylinder_agent = CylinderAgent::find($id);
        return view('admin.cylinder_agent.modal',compact('cylinder_agent'));
    }

     public function change_status($id){
        if (!\App\Helpers\PermissionHelper::check('cylinder_agent', 'edit')) {
            return ['result' => -1, 'message' => 'Access Denied! You do not have permission to change status.'];
        }
        $cylinder_agent = CylinderAgent::find($id);
        if($cylinder_agent){
            $cylinder_agent->status = $cylinder_agent->status == 1 ? 0 : 1;
            $cylinder_agent->save();
            $data = [
                'result' => 1,
                'message' => 'Status Changed Successfully'
            ];
        } else {
            $data = ['result' => 0, 'message' => 'Agent NOT Found!'];
        }
        return $data;
    }

    public function delete($id)
    {
        if (auth()->user()->role_as != 'Admin') {
            return ['result' => -1, 'message' => 'Access Denied! Only Admin can delete records.'];
        }
        $cylinder_agent = CylinderAgent::find($id);
        if ($cylinder_agent) {
            $cylinder_agent->delete();
            $data = 
            [
                'result' => 1,
                'message' => 'Deleted Successfully',
            ];
        } else {
             $data = ['result' => 0, 'message' => 'Agent NOT Found!'];
        }
        return $data;
    }
    public function store(Request $request)
    {
        $cylinder_agent = CylinderAgent::find($request->cylinder_agent_id);
        if($cylinder_agent){
            // Update
            if (!\App\Helpers\PermissionHelper::check('cylinder_agent', 'edit')) {
                return ['result' => -1, 'message' => 'Access Denied! You do not have permission to edit records.'];
            }
            $check = CylinderAgent::where('name',$request->name)->where('id','!=',$cylinder_agent->id)->first();
            if($check){
                $data = [
                    'result' => -1,
                    'message' => 'Agent Name Already Used',
                    'from' => 'Cylinder Agent'
                ];
            }else{
                $cylinder_agent->name = $request->name;
                $cylinder_agent->save();
                $data = [
                    'result' => 1,
                    'message' => 'Cylinder Agent Updated Successfully',
                    'from' => 'Cylinder Agent'
                ];
            }
        }else{
            // Create
            if (!\App\Helpers\PermissionHelper::check('cylinder_agent', 'add')) {
                return ['result' => -1, 'message' => 'Access Denied! You do not have permission to add records.'];
            }
            $check = CylinderAgent::where('name',$request->name)->first();
            if($check){
                $data = [
                    'result' => -1,
                    'message' => 'Agent Name Already Used',
                    'from' => 'Cylinder Agent'
                ];
            }else{
                $cylinder_agent = new CylinderAgent();
                $cylinder_agent->name = $request->name;
                $cylinder_agent->user_id = auth()->user()->id;
                $cylinder_agent->status = 1;
                $cylinder_agent->save();
                $data = [
                    'result' => 1,
                    'message' => 'Cylinder Agent Added Successfully',
                    'from' => 'Cylinder Agent'
                ];
            }
        }
        return $data;
    }
}
