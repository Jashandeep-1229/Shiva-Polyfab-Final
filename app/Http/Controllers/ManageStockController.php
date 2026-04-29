<?php

namespace App\Http\Controllers;

use App\Models\ManageStock;
use Illuminate\Http\Request;


class ManageStockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!\App\Helpers\PermissionHelper::check('stock_management')) {
            abort(403, 'Unauthorized access to Stock Management.');
        }
        $stock_name = $request->stock_name ?? 'fabric';
        $in_out = $request->in_out ?? 'in';
        $unit_name = '';
        $average = 0;

        if ($stock_name == 'fabric' || $stock_name == 'bopp' || $stock_name == 'loop') {
            $unit_name = 'Rolls';
        } else if ($stock_name == 'ink') {
            $unit_name = 'Drums';
        } else if ($stock_name == 'dana') {
            $unit_name = 'Bags';
        }

        if ($stock_name == 'fabric') {
            $average = 80;
        } else if ($stock_name == 'bopp') {
            $average = 255;
        } else if ($stock_name == 'dana') {
            $average = 25;
        } else if ($stock_name == 'loop') {
            $average = 40;
        } else if ($stock_name == 'ink') {
            $average = 20;
        }
        
        $stock_name_capital = ucfirst($stock_name);
        $modelClass = "App\\Models\\" . $stock_name_capital;
        $stock_list = $modelClass::where('status', 1)->get();

        return view('admin.manage_stock.index', compact('stock_name', 'stock_name_capital', 'in_out', 'unit_name', 'average', 'stock_list'));
    }
    public function bulk_modal(Request $request)
    {
        if (!\App\Helpers\PermissionHelper::check('stock_management', 'add')) {
            return '<div class="alert alert-danger m-3">Access Denied! You do not have permission to record new stock.</div>';
        }
        $stock_name = $request->stock_name ?? 'fabric';
        $in_out = $request->in_out ?? 'in';
        $unit_name = $request->unit_name;
        $average = $request->average;
        
        $stock_name_capital = ucfirst($stock_name);
        $modelClass = "App\\Models\\" . $stock_name_capital;
        $stock_list = $modelClass::where('status', 1)->get();
        return view('admin.manage_stock.bulk_modal', compact('stock_name','in_out','unit_name','average','stock_list','stock_name_capital'));
    }

    public function get_current_stock(Request $request, $id)
    {
        $manage_stock = ManageStock::where('stock_name', $request->stock_name)->where('stock_id', $id)->get();
        $in_stock = $manage_stock->where('in_out', 'in')->sum('quantity');
        $out_stock = $manage_stock->where('in_out', 'out')->sum('quantity');
        $current_stock = $in_stock - $out_stock;

        $in_average = $manage_stock->where('in_out', 'in')->sum('average');
        $out_average = $manage_stock->where('in_out', 'out')->sum('average');
        $current_average = $in_average - $out_average;

        return response()->json([
            'result' => 1,
            'current_stock' => $current_stock,
            'current_average' => $current_average
        ]);
    }
    public function edit_modal(Request $request, $id)
    {
        if (!\App\Helpers\PermissionHelper::check('stock_management', 'edit')) {
            return '<div class="alert alert-danger m-3">Access Denied! You do not have permission to edit stock.</div>';
        }
        $stock_name = $request->stock_name ?? 'fabric';
        $in_out = $request->in_out ?? 'in';
        $unit_name = $request->unit_name;
        $average = $request->average;

        
        $stock_name_capital = ucfirst($stock_name);
        $modelClass = "App\\Models\\" . $stock_name_capital;
        $stock_list = $modelClass::where('status', 1)->get();
        $manage_stock = ManageStock::find($id);
        return view('admin.manage_stock.modal', compact('manage_stock','stock_name','in_out','unit_name','average','stock_list','stock_name_capital'));
    }

    public function datatable(Request $request)
    {
        $number = $request->value ?? 50;
        $stock_name = $request->stock_name ?? 'fabric';
        $in_out = $request->in_out ?? 'in';

        $query = ManageStock::with('master')->where('stock_name', $stock_name)->where('in_out', $in_out);
        $query = auth()->user()->applyDataRestriction($query, 'user_id', 'stock_management');

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('remarks', 'like', '%' . $search . '%')
                  ->orWhereHas('master', function($mq) use ($search) {
                      $mq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }
        $manage_stocks = $query->latest('id')->paginate($number);
        return view('admin.manage_stock.datatable', compact('manage_stocks', 'stock_name', 'in_out'));
    }
    public function average_index(Request $request)
    {
        $stock_name = $request->stock_name ?? 'fabric';
        $stock_name_capital = ucfirst($stock_name);
        $modelClass = "App\\Models\\" . $stock_name_capital;
        $stock_data = $modelClass::where('status', 1)->get();
        return view('admin.manage_stock.average_index', compact('stock_name', 'stock_name_capital', 'stock_data'));
    }

    public function average_datatable(Request $request)
    {
        $number = $request->value ?? 50;
        $stock_name = $request->stock_name ?? 'fabric';
        $stock_name_capital = ucfirst($stock_name);
        $unit_name = '';
        
        if ($stock_name == 'fabric' || $stock_name == 'bopp' || $stock_name == 'loop') {
            $unit_name = 'Rolls';
        } else if ($stock_name == 'ink') {
            $unit_name = 'Drums';
        } else if ($stock_name == 'dana') {
            $unit_name = 'Bags';
        }

        $modelClass = "App\\Models\\" . $stock_name_capital;
        $table = (new $modelClass)->getTable();

        $query = $modelClass::where($table . '.status', 1)
            ->leftJoin('manage_stocks', function($join) use ($table, $stock_name) {
                $join->on($table . '.id', '=', 'manage_stocks.stock_id')
                     ->where('manage_stocks.stock_name', '=', $stock_name);
            })
            ->select(
                $table . '.id as stock_id',
                $table . '.name as item_name',
                $table . '.alert_min_stock',
                $table . '.alert_max_stock'
            )
            ->selectRaw("MAX(manage_stocks.unit) as unit_val")
            ->selectRaw("SUM(CASE WHEN in_out = 'in' THEN quantity ELSE 0 END) as in_quantity")
            ->selectRaw("SUM(CASE WHEN in_out = 'in' THEN average ELSE 0 END) as in_average")
            ->selectRaw("SUM(CASE WHEN in_out = 'out' THEN quantity ELSE 0 END) as out_quantity")
            ->selectRaw("SUM(CASE WHEN in_out = 'out' THEN average ELSE 0 END) as out_average")
            ->selectRaw("SUM(CASE WHEN in_out = 'in' THEN quantity ELSE -quantity END) as total_quantity")
            ->selectRaw("SUM(CASE WHEN in_out = 'in' THEN average ELSE -average END) as total_average")
            ->groupBy($table . '.id', $table . '.name', $table . '.alert_min_stock', $table . '.alert_max_stock')
            ->orderByRaw('LENGTH(' . $table . '.name) ASC')->orderBy($table . '.name', 'asc');
        
        if ($request->stock_id) {
            $query->where($table . '.id', $request->stock_id);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where($table . '.name', 'like', '%' . $search . '%');
        }

        if ($request->filter_by && $request->filter_by != 'all') {
            // Since we need to filter on aggregated results (total_average), 
            // we use having() instead of filter() on a collection for pagination support.
            if ($request->filter_by == 'zero_stock') {
                $query->having('total_average', '<=', 0);
            } elseif ($request->filter_by == 'low_stock') {
                $query->having('total_average', '>', 0)
                      ->havingRaw('total_average <= ' . $table . '.alert_min_stock');
            } elseif ($request->filter_by == 'over_stock') {
                $query->havingRaw('total_average >= ' . $table . '.alert_max_stock');
            }
        }

        $results = $query->paginate($number);
        return view('admin.manage_stock.average_datatable', compact('results', 'stock_name', 'stock_name_capital', 'unit_name'));
    }

    public function history_index(Request $request)
    {
        $stock_name = $request->stock_name;
        $stock_id = $request->stock_id;
        $stock_name_capital = ucfirst($stock_name);
        
        $modelClass = "App\\Models\\" . $stock_name_capital;
        $master = $modelClass::find($stock_id);
        $item_name = $master ? $master->name : 'N/A';

        return view('admin.manage_stock.history_index', compact('stock_name', 'stock_id', 'stock_name_capital', 'item_name'));
    }

    public function history_datatable(Request $request)
    {
        $number = $request->value ?? 50;
        $stock_name = $request->stock_name;
        $stock_id = $request->stock_id;

        $query = ManageStock::with(['master', 'user'])
            ->where('stock_name', $stock_name)
            ->where('stock_id', $stock_id);
            
        $query = auth()->user()->applyDataRestriction($query);

        if ($request->from_date) {
            $query->where('date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->where('date', '<=', $request->to_date);
        }

        $history = $query->oldest('date')->oldest('id')->paginate($number);
        return view('admin.manage_stock.history_datatable', compact('history'));
    }

    public function bulk_store(Request $request)
    {
        if (!\App\Helpers\PermissionHelper::check('stock_management', 'add')) {
            return response()->json(['result' => -1, 'message' => 'Access Denied! You do not have permission to record new stock.']);
        }

        $input = $request->all();
        $date = $input['date'] ?? date('Y-m-d');
        $stock_name = $input['stock_name'];
        $unit_name = $input['unit_name'];
        $in_out = $input['in_out'];
        $average_factor = $input['average_factor'];
        $user_id = auth()->user()->id;

        $items = $request->items ?? [];
        $count = 0;

        foreach ($items as $item) {
            if (isset($item['stock_id']) && $item['stock_id'] > 0 && isset($item['quantity']) && $item['quantity'] > 0) {
                ManageStock::create([
                    'date' => $date,
                    'stock_name' => $stock_name,
                    'unit_name' => $unit_name,
                    'in_out' => $in_out,
                    'stock_id' => $item['stock_id'],
                    'quantity' => $item['quantity'],
                    'average' => ($item['quantity'] * $average_factor),
                    'remarks' => $item['remarks'] ?? null,
                    'user_id' => $user_id,
                    'from' => 'Manually',
                    'status' => 1
                ]);
                $count++;
            }
        }

        if ($count > 0) {
            return response()->json([
                'result' => 1,
                'message' => $count . ' stock records saved successfully.'
            ]);
        }

        return response()->json(['result' => 0, 'message' => 'No valid stock entries found.']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $input['user_id'] = auth()->user()->id;
        $input['status'] = 1;

        if ($request->manage_stock_id > 0) {
            // Update
            if (!\App\Helpers\PermissionHelper::check('stock_management', 'edit')) {
                return response()->json(['result' => -1, 'message' => 'Access Denied! You do not have permission to edit stock.']);
            }
            $stock = ManageStock::find($request->manage_stock_id);
            if ($stock) {
                $stock->update($input);
                $message = ucfirst($input['stock_name']) . ' ' . $input['in_out'] . ' updated successfully';
            } else {
                return response()->json(['result' => 0, 'message' => 'Record not found.']);
            }
        } else {
            // Create
            if (!\App\Helpers\PermissionHelper::check('stock_management', 'add')) {
                return response()->json(['result' => -1, 'message' => 'Access Denied! You do not have permission to record new stock.']);
            }
            $stock = ManageStock::create($input);
            $message = ucfirst($input['stock_name']) . ' ' . $input['in_out'] . ' recorded successfully';
        }

        if ($stock) {
            return response()->json([
                'result' => 1,
                'message' => $message
            ]);
        }
        
        return response()->json(['result' => 0, 'message' => 'Failed to save stock.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        if (auth()->user()->role_as != 'Admin') {
            return response()->json(['result' => -1, 'message' => 'Access Denied! Only Admin can delete stock records.']);
        }
        $stock = ManageStock::find($id);
        if ($stock) {
            $stock->delete();
            return response()->json([
                'result' => 1,
                'message' => 'Stock record deleted successfully'
            ]);
        }
        return response()->json(['result' => 0, 'message' => 'Record not found.']);
    }
}
