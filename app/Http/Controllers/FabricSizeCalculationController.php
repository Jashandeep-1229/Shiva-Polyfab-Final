<?php

namespace App\Http\Controllers;

use App\Models\FabricSizeCalculation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FabricSizeCalculationController extends Controller
{
    public function index()
    {
       if (!\App\Helpers\PermissionHelper::check('manage_master')) {
           abort(403, 'Unauthorized access to Fabric Size Calculation Master.');
       }
       return view('admin.fabric_size.index');
    }

    public function datatable(Request $request)
    {
        $number = $request->value ?? 50;
        $query = FabricSizeCalculation::query();
        
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $sizes = $query->orderBy('name', 'asc')->paginate($number);
        return view('admin.fabric_size.datatable', compact('sizes'));
    }

    public function edit_modal($id)
    {
        if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
            return '<div class="alert alert-danger m-3">Access Denied!</div>';
        }
        $fabric_size = FabricSizeCalculation::find($id);
        return view('admin.fabric_size.modal', compact('fabric_size'));
    }

    public function change_status($id)
    {
        if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
            return ['result' => -1, 'message' => 'Access Denied!'];
        }
        $fabric_size = FabricSizeCalculation::find($id);
        if ($fabric_size) {
            $fabric_size->status = $fabric_size->status == 1 ? 0 : 1;
            $fabric_size->save();
            return ['result' => 1, 'message' => 'Status Changed Successfully'];
        }
        return ['result' => 0, 'message' => 'Record NOT Found!'];
    }

    public function delete($id)
    {
        if (auth()->user()->role_as != 'Admin') {
            return ['result' => -1, 'message' => 'Access Denied! Only Admin can delete records.'];
        }
        $fabric_size = FabricSizeCalculation::find($id);
        if ($fabric_size) {
            $fabric_size->delete();
            return ['result' => 1, 'message' => 'Deleted Successfully'];
        }
        return ['result' => 0, 'message' => 'Record NOT Found!'];
    }

    public function store(Request $request)
    {
        $fabric_size = FabricSizeCalculation::find($request->fabric_size_id);
        
        // Duplicate check
        $check = FabricSizeCalculation::where('name', $request->name);
        if ($fabric_size) {
            $check->where('id', '!=', $fabric_size->id);
        }
        if ($check->exists()) {
            return ['result' => -1, 'message' => 'Fabric Size ' . $request->name . ' already exists!'];
        }

        if ($fabric_size) {
            // Update
            if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
                return ['result' => -1, 'message' => 'Access Denied!'];
            }
            $fabric_size->name = $request->name;
            $fabric_size->save();
            return ['result' => 1, 'message' => 'Updated Successfully', 'from' => 'Fabric Size Calculation'];
        } else {
            // Create
            if (!\App\Helpers\PermissionHelper::check('manage_master', 'add')) {
                return ['result' => -1, 'message' => 'Access Denied!'];
            }
            $fabric_size = new FabricSizeCalculation();
            $fabric_size->name = $request->name;
            $fabric_size->status = 1;
            $fabric_size->save();
            return ['result' => 1, 'message' => 'Added Successfully', 'from' => 'Fabric Size Calculation'];
        }
    }
}
