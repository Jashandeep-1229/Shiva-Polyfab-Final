<?php

namespace App\Http\Controllers;

use App\Models\MasterVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterVendorController extends Controller
{
    public function add_vendor_modal($type, $id)
    {
        $modelClass = 'App\\Models\\' . $type;
        $master = $modelClass::find($id);
        return view('admin.master_vendor.add_modal', compact('type', 'id', 'master'));
    }

    public function store_vendor(Request $request)
    {
        $request->validate([
            'master_type' => 'required',
            'master_id' => 'required',
            'name' => 'required',
            'phone_no' => 'required',
        ]);

        try {
            $check = MasterVendor::where('master_type', $request->master_type)
                ->where('master_id', $request->master_id)
                ->where('phone_no', $request->phone_no)
                ->first();

            if ($check) {
                return ['result' => -1, 'message' => 'Vendor Phone No already added for this item'];
            }

            MasterVendor::create([
                'master_type' => $request->master_type,
                'master_id' => $request->master_id,
                'name' => $request->name,
                'phone_no' => $request->phone_no,
                'user_id' => Auth::id(),
            ]);

            return ['result' => 1, 'message' => 'Vendor added successfully'];
        } catch (\Exception $e) {
            return ['result' => 0, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function list_vendors_modal($type, $id)
    {
        $vendors = MasterVendor::where('master_type', $type)
            ->where('master_id', $id)
            ->get();
        $modelClass = 'App\\Models\\' . $type;
        $master = $modelClass::find($id);
        return view('admin.master_vendor.list_modal', compact('vendors', 'type', 'id', 'master'));
    }

    public function delete_vendor($id)
    {
        $vendor = MasterVendor::find($id);
        if ($vendor) {
            $vendor->delete();
            return ['result' => 1, 'message' => 'Vendor deleted successfully'];
        }
        return ['result' => 0, 'message' => 'Vendor not found'];
    }
}
