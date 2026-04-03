<?php

namespace App\Http\Controllers;

use App\Models\SaleExecutive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaleExecutiveController extends Controller
{
    public function index()
    {
       if (!\App\Helpers\PermissionHelper::check('master_management')) {
           abort(403, 'Unauthorized access to Sale Executive Master.');
       }
       return view('admin.sale_executive.index');
    }

    public function datatable(Request $request){
        $number = $request->value ?? 50;
        $query = SaleExecutive::query();
        $query = auth()->user()->applyDataRestriction($query, 'user_id', 'master_management');
        if($request->search){
            $query->where(function($q) use ($request){
                $q->where('name','like','%'.$request->search.'%')
                ->orWhere('phone_no','like','%'.$request->search.'%');
            });
        }
        $sale_executive = $query->latest('id')->paginate($number);
        return view('admin.sale_executive.datatable',compact('sale_executive'));
    }

    public function sale_executive_list(Request $request){
        $executives = SaleExecutive::select('id', 'name','phone_no')->where('status',1)->get();
        return view('admin.sale_executive.list',compact('executives'));
    }

    public function edit_modal($id){
        $sale_executive = SaleExecutive::find($id);
        return view('admin.sale_executive.modal',compact('sale_executive'));
    }

    public function change_status($id){
        $sale_executive = SaleExecutive::find($id);
        if($sale_executive){
            $sale_executive->status = $sale_executive->status == 1 ? 0 : 1;
            $sale_executive->save();
            $data = [
                'result' => 1,
                'message' => 'Status Changed Successfully'
            ];
        } else {
            $data = ['result' => 0, 'message' => 'Not Found'];
        }
        return $data;
    }

    public function delete($id)
    {
        $sale_executive = SaleExecutive::find($id);
        if($sale_executive){
            $sale_executive->delete();
            $data = [
                'result' => 1,
                'message' => 'Deleted Successfully',
            ];
        } else {
            $data = ['result' => 0, 'message' => 'Not Found'];
        }
        return $data;
    }

    public function store(Request $request)
    {
        $sale_executive = SaleExecutive::find($request->sale_executive_id);
        
        // Validation check for phone_no unique constraint
        if($sale_executive){
            $check = SaleExecutive::where('phone_no', $request->phone_no)
                                  ->where('id', '!=', $sale_executive->id)
                                  ->first();
        } else {
             $check = SaleExecutive::where('phone_no', $request->phone_no)->first();
        }

        if($check){
            return [
                'result' => -1,
                'message' => 'Phone Number Already Used',
                'from' => 'Sale Executive Master'
            ];
        }

        if($sale_executive){
            // Update logic
            $sale_executive->name = $request->name;
            $sale_executive->phone_no = $request->phone_no;
            $sale_executive->save();
            $data = [
                'result' => 1,
                'message' => 'Updated Successfully',
                'from' => 'Sale Executive Master'
            ];
        } else {
            // Create logic
            $sale_executive = new SaleExecutive();
            $sale_executive->name = $request->name;
            $sale_executive->phone_no = $request->phone_no;
            $sale_executive->user_id = Auth::id();
            $sale_executive->status = 1;
            $sale_executive->save();

            $data = [
                'result' => 1,
                'message' => 'Added Successfully',
                'from' => 'Sale Executive Master'
            ];
        }
        return $data;
    }
}
