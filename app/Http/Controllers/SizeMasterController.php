<?php

namespace App\Http\Controllers;

use App\Models\SizeMaster;
use App\Models\Fabric;
use App\Models\Bopp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SizeMasterController extends Controller
{
    public function datatable(Request $request)
    {
        if (!\App\Helpers\PermissionHelper::check('manage_master')) {
            return '<div class="alert alert-danger m-3">Access Denied!</div>';
        }
        $number = $request->value ?? 50;
        $query = SizeMaster::with(['fabric', 'bopp']);
        $query = auth()->user()->applyDataRestriction($query, 'user_id', 'manage_master');
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('order_send_for', 'like', '%' . $request->search . '%')
                  ->orWhereHas('fabric', function($q) use ($request){
                      $q->where('name', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('bopp', function($q) use ($request){
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
        }
        $sizes = $query->latest('id')->paginate($number);
        return view('admin.size_color.size_datatable', compact('sizes'));
    }

    public function edit_modal($id)
    {
        if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
            return '<div class="alert alert-danger m-3">Access Denied!</div>';
        }
        $size = SizeMaster::find($id);
        $fabrics = Fabric::where('status', 1)->get();
        $bopps = Bopp::where('status', 1)->get();
        return view('admin.size_color.size_modal', compact('size', 'fabrics', 'bopps'));
    }

    public function change_status($id)
    {
        if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
            return ['result' => -1, 'message' => 'Access Denied!'];
        }
        $size = SizeMaster::find($id);
        if ($size) {
            $size->status = $size->status == 1 ? 0 : 1;
            $size->save();
            return ['result' => 1, 'message' => 'Status Changed Successfully'];
        }
    }

    public function delete($id)
    {
        if (auth()->user()->role_as != 'Admin') {
            return ['result' => -1, 'message' => 'Access Denied! Only Admin can delete records.'];
        }
        $size = SizeMaster::find($id);
        if ($size) {
            $size->delete();
            return ['result' => 1, 'message' => 'Deleted Successfully'];
        }
    }

    public function store(Request $request)
    {
        $size = SizeMaster::find($request->size_id);
        if ($size) {
            if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
                return ['result' => -1, 'message' => 'Access Denied!'];
            }
            $check = SizeMaster::where('name', $request->name)
                ->where('fabric_id', $request->fabric_id)
                ->where('bopp_id', $request->bopp_id)
                ->where('id', '!=', $size->id)->first();
            if ($check) {
                return ['result' => -1, 'message' => 'Size configuration already exists', 'from' => 'Size Master'];
            }
            $size->name = $request->name;
            $size->fabric_id = $request->fabric_id;
            $size->bopp_id = $request->bopp_id;
            $size->order_send_for = $request->order_send_for;
            $size->save();
            return ['result' => 1, 'message' => 'Size Master Updated Successfully', 'from' => 'Size Master'];
        } else {
            if (!\App\Helpers\PermissionHelper::check('manage_master', 'add')) {
                return ['result' => -1, 'message' => 'Access Denied!'];
            }
            $check = SizeMaster::where('name', $request->name)
                ->where('fabric_id', $request->fabric_id)
                ->where('bopp_id', $request->bopp_id)
                ->first();
            if ($check) {
                return ['result' => -1, 'message' => 'Size configuration already exists', 'from' => 'Size Master'];
            }
            $size = new SizeMaster();
            $size->name = $request->name;
            $size->fabric_id = $request->fabric_id;
            $size->bopp_id = $request->bopp_id;
            $size->order_send_for = $request->order_send_for;
            $size->user_id = auth()->id();
            $size->status = 1;
            $size->save();
            return ['result' => 1, 'message' => 'Size Master Added Successfully', 'from' => 'Size Master'];
        }
    }
}
