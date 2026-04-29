<?php

namespace App\Http\Controllers;

use App\Models\ExecutiveTargetRecord;
use App\Models\User;
use Illuminate\Http\Request;

class ExecutiveTargetController extends Controller
{
    public function index(Request $request)
    {
        $executives = User::whereIn('role_as', ['Sale Executive', 'Senior Sale Executive'])->where('status', 1)->get();
        return view('admin.executive_target.index', compact('executives'));
    }

    public function datatable(Request $request)
    {
        $query = ExecutiveTargetRecord::with(['job_card', 'executive']);

        if ($request->executive_id) {
            $query->where('executive_id', $request->executive_id);
        }

        if ($request->from_date) {
            $query->whereDate('date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $records = $query->latest('date')->get();
        
        $total_weight = $records->sum('total_weight');
        $total_pcs = $records->sum('total_pcs');

        return view('admin.executive_target.datatable', compact('records', 'total_weight', 'total_pcs'));
    }
}
