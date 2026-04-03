<?php

namespace App\Http\Controllers;

use App\Models\OldData;
use App\Imports\OldDataImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\PermissionHelper;

class OldDataController extends Controller
{
    public function index()
    {
        if (!PermissionHelper::check('old_data')) {
            abort(403, 'Unauthorized access to Old Data.');
        }
        return view('admin.old_data.index');
    }

    public function datatable(Request $request)
    {
        $number = $request->value ?? 50;
        $query = OldData::with(['bopp', 'fabric', 'loop']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name_of_job', 'like', '%' . $request->search . '%')
                  ->orWhereHas('bopp', function($q2) use ($request) {
                      $q2->where('name', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('fabric', function($q2) use ($request) {
                      $q2->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $old_data = $query->latest('id')->paginate($number);
        return view('admin.old_data.datatable', compact('old_data'));
    }

    public function import(Request $request)
    {
        if (!PermissionHelper::check('old_data', 'add')) {
            return response()->json(['result' => -1, 'message' => 'Access Denied! You do not have permission to upload data.']);
        }
        @set_time_limit(0); 
        @ini_set('max_execution_time', '0'); 
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new OldDataImport, $request->file('file'));
            return [
                'result' => 1,
                'message' => 'Data Imported Successfully'
            ];
        } catch (\Exception $e) {
            return [
                'result' => -1,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    public function delete($id)
    {
        if (auth()->user()->role_as != 'Admin') {
            return response()->json(['result' => -1, 'message' => 'Access Denied! Only Admin can delete data.']);
        }
        $old_data = OldData::find($id);
        if ($old_data) {
            $old_data->delete();
            return [
                'result' => 1,
                'message' => 'Deleted Successfully'
            ];
        }
        return [
            'result' => -1,
            'message' => 'Record Not Found'
        ];
    }

    public function search(Request $request)
    {
        $search = $request->search;
        // Optimization: Use a simpler query and only fetch needed columns
        $results = OldData::where('name_of_job', 'like', $search . '%') // Start-with search is much faster than %like%
            ->selectRaw('MAX(id) as id, name_of_job')
            ->groupBy('name_of_job')
            ->limit(10)
            ->get();
        
        // If no results, try broader search
        if($results->isEmpty()) {
            $results = OldData::where('name_of_job', 'like', '%' . $search . '%')
                ->selectRaw('MAX(id) as id, name_of_job')
                ->groupBy('name_of_job')
                ->limit(10)
                ->get();
        }
        
        return response()->json($results);
    }

    public function get_details($id)
    {
        $data = OldData::with(['bopp', 'fabric', 'loop'])->find($id);
        if ($data) {
            return response()->json([
                'result' => 1,
                'data' => $data
            ]);
        }
        return response()->json(['result' => -1]);
    }
}
