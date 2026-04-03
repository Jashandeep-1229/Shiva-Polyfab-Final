<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Auth;

class PaymentMethodController extends Controller
{
    public function index()
    {
        if (!\App\Helpers\PermissionHelper::check('manage_master')) {
            abort(403);
        }
        return view('admin.payment_method.index');
    }

    public function datatable(Request $request)
    {
        if (!\App\Helpers\PermissionHelper::check('manage_master')) {
            return '<div class="alert alert-danger m-3">Access Denied!</div>';
        }
        $number = $request->value ?? 50;
        $query = PaymentMethod::query();
        
        if($request->search){
            $query->where('name','like','%'.$request->search.'%');
        }
        
        $payment_method = $query->latest()->paginate($number);
        return view('admin.payment_method.datatable', compact('payment_method'));
    }

    public function store(Request $request)
    {
        if (!\App\Helpers\PermissionHelper::check('manage_master', 'add') && !\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
            return response()->json(['result' => -1, 'message' => 'Access Denied!']);
        }
        $request->validate([
            'name' => 'required|unique:payment_methods,name,' . ($request->id ?? 'NULL') . ',id,deleted_at,NULL',
        ]);

        PaymentMethod::updateOrCreate(
            ['id' => $request->id],
            [
                'name' => $request->name,
                'status' => $request->status ?? 1,
                'user_id' => Auth::id()
            ]
        );

        return response()->json(['result' => 1, 'message' => 'Payment Method Saved Successfully']);
    }

    public function edit_modal($id)
    {
        if (!\App\Helpers\PermissionHelper::check('manage_master', 'edit')) {
            return '<div class="alert alert-danger m-3">Access Denied!</div>';
        }
        $method = PaymentMethod::find($id);
        return view('admin.payment_method.edit_modal', compact('method'));
    }

    public function delete($id)
    {
        if (auth()->user()->role_as != 'Admin') {
            return response()->json(['result' => -1, 'message' => 'Access Denied! Only Admin can delete.']);
        }
        PaymentMethod::find($id)->delete();
        return response()->json(['result' => 1, 'message' => 'Deleted Successfully']);
    }
}
