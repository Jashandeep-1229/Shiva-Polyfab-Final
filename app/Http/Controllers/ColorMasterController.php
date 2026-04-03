<?php

namespace App\Http\Controllers;

use App\Models\ColorMaster;
use App\Models\Fabric;
use App\Models\Bopp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ColorMasterController extends Controller
{
    public function index()
    {
        if (!\App\Helpers\PermissionHelper::check('manage_master')) {
            abort(403, 'Unauthorized access to Color Master.');
        }
        $fabrics = Fabric::where('status', 1)->get();
        $bopps = Bopp::where('status', 1)->get();
        return view('admin.size_color.index', compact('fabrics', 'bopps'));
    }

    public function datatable(Request $request)
    {
        if (!\App\Helpers\PermissionHelper::check('manage_master')) {
            return '<div class="alert alert-danger m-3">Access Denied!</div>';
        }
        $number = $request->value ?? 50;
        $query = ColorMaster::query();
        $query = auth()->user()->applyDataRestriction($query, 'user_id', 'manage_master');
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $colors = $query->latest('id')->paginate($number);
        return view('admin.size_color.color_datatable', compact('colors'));
    }

    public function edit_modal($id)
    {
        if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
            return '<div class="alert alert-danger m-3">Access Denied!</div>';
        }
        $color = ColorMaster::find($id);
        return view('admin.size_color.color_modal', compact('color'));
    }

    public function change_status($id)
    {
        if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
            return ['result' => -1, 'message' => 'Access Denied!'];
        }
        $color = ColorMaster::find($id);
        if ($color) {
            $color->status = $color->status == 1 ? 0 : 1;
            $color->save();
            return ['result' => 1, 'message' => 'Status Changed Successfully'];
        }
    }

    public function delete($id)
    {
        if (auth()->user()->role_as != 'Admin') {
            return ['result' => -1, 'message' => 'Access Denied! Only Admin can delete records.'];
        }
        $color = ColorMaster::find($id);
        if ($color) {
            $color->delete();
            return ['result' => 1, 'message' => 'Deleted Successfully'];
        }
    }

    public function store(Request $request)
    {
        $color = ColorMaster::find($request->color_id);
        if ($color) {
            if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
                return ['result' => -1, 'message' => 'Access Denied!'];
            }
            $check = ColorMaster::where('name', $request->name)->where('id', '!=', $color->id)->first();
            if ($check) {
                return ['result' => -1, 'message' => 'Color Name Already Used', 'from' => 'Color Master'];
            }
            $color->name = $request->name;
            $color->save();
            return ['result' => 1, 'message' => 'Color Master Updated Successfully', 'from' => 'Color Master'];
        } else {
            if (!\App\Helpers\PermissionHelper::check('manage_master', 'add')) {
                return ['result' => -1, 'message' => 'Access Denied!'];
            }
            $check = ColorMaster::where('name', $request->name)->first();
            if ($check) {
                return ['result' => -1, 'message' => 'Color Name Already Used', 'from' => 'Color Master'];
            }
            $color = new ColorMaster();
            $color->name = $request->name;
            $color->user_id = auth()->id();
            $color->status = 1;
            $color->save();
            return ['result' => 1, 'message' => 'Color Master Added Successfully', 'from' => 'Color Master'];
        }
    }
}
