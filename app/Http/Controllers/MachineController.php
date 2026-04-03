<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!\App\Helpers\PermissionHelper::check('master_management') && !\App\Helpers\PermissionHelper::check('machine_master')) {
            abort(403, 'Unauthorized access to Machine Master.');
        }
        $type = $request->type;
        return view('admin.machine.index', compact('type'));
    }

    public function datatable(Request $request)
    {
        $number = $request->value ?? 50;
        $query = Machine::where('type',$request->type);
        $query = auth()->user()->applyDataRestriction($query, 'user_id', 'master_management');
        if($request->search){
            $query->where(function($q) use ($request){
                $q->where('name','like','%'.$request->search.'%');
            });
        }
        $machine = $query->latest('id')->paginate($number);
        return view('admin.machine.datatable',compact('machine'));
    }

    public function edit_modal($id){
        if (!\App\Helpers\PermissionHelper::check('machine_master', 'edit') && !\App\Helpers\PermissionHelper::check('master_management', 'edit')) {
            return '<div class="alert alert-danger m-3">Access Denied!</div>';
        }
        $machine = Machine::find($id);
        return view('admin.machine.modal',compact('machine'));
    }
    public function change_status($id){
        $machine = Machine::find($id);
        if($machine){
            $machine->status = $machine->status == 1 ? 0 : 1;
            $machine->save();
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
        $machine = Machine::find($id);
        $machine->delete();
        $data = 
        [
            'result' => 1,
            'message' => 'Deleted Successfully',
        ];
        return $data;
    }
    public function store(Request $request)
    {
        $machine = Machine::find($request->machine_id);
        if($machine){
            if (!\App\Helpers\PermissionHelper::check('machine_master', 'edit') && !\App\Helpers\PermissionHelper::check('master_management', 'edit')) {
                return ['result' => -1, 'message' => 'Access Denied!'];
            }
            $check = Machine::where('name',$request->name)->where('type',$request->type)->where('id','!=',$machine->id)->first();
            if($check){
                $data = [
                    'result' => -1,
                    'message' => 'Machine Name Already Used',
                    'from' => 'Machine Master'
                ];
            }else{
                // $machine->type = $request->type;
                $machine->name = $request->name;
                $machine->avg_per_day_production = $request->avg_per_day_production ?? 0;
                $machine->save();
                $data = [
                    'result' => 1,
                    'message' => 'Machine Master Updated Successfully',
                    'from' => 'Machine Master'
                ];
            }
        }else{
            if (!\App\Helpers\PermissionHelper::check('machine_master', 'add') && !\App\Helpers\PermissionHelper::check('master_management', 'add')) {
                return ['result' => -1, 'message' => 'Access Denied!'];
            }
            $check = Machine::where('name',$request->name)->where('type',$request->type)->first();
            if($check){
                $data = [
                    'result' => -1,
                    'message' => 'Machine Name Already Used',
                    'from' => 'Machine Master'
                ];
            }else{
                $machine = new Machine();
                $machine->type = $request->type;
                $machine->name = $request->name;
                $machine->avg_per_day_production = $request->avg_per_day_production ?? 0;
                $machine->user_id = auth()->user()->id;
                $machine->status = 1;
                $machine->save();
                $data = [
                    'result' => 1,
                    'message' => 'Machine Master Added Successfully',
                    'from' => 'Machine Master'
                ];
            }
        }
        return $data;
    }
}
