<?php

namespace App\Http\Controllers;

use App\Models\CommonManageStock;
use App\Models\ColorMaster;
use App\Models\SizeMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommonManageStockController extends Controller
{
    public function index(Request $request)
    {
        $in_out = $request->in_out ?? 'In';
        $colors = ColorMaster::select('id', 'name')->where('status', 1)->orderBy('name')->get();
        $sizes = SizeMaster::select('id', 'name')->where('status', 1)->orderBy('name')->get();
        
        return view('admin.common_stock.index', compact('colors', 'sizes', 'in_out'));
    }

    public function datatable(Request $request)
    {
        $number = $request->value ?? 50;
        $in_out = $request->in_out ?? 'In';
        
        $query = CommonManageStock::with(['color', 'size', 'user'])
            ->where('in_out', $in_out);
            
        $query = auth()->user()->applyDataRestriction($query, 'user_id', 'common_product_stock');

        if ($request->color_id) {
            $query->where('color_id', $request->color_id);
        }
        if ($request->size_id) {
            $query->where('size_id', $request->size_id);
        }
        if ($request->from_date) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('color', function($cq) use ($search) {
                    $cq->where('name', 'like', '%' . $search . '%');
                })->orWhereHas('size', function($sq) use ($search) {
                    $sq->where('name', 'like', '%' . $search . '%');
                })->orWhere('remarks', 'like', '%' . $search . '%');
            });
        }

        $manage_stocks = $query->latest('date')->latest('id')->paginate($number);
        return view('admin.common_stock.datatable', compact('manage_stocks', 'in_out'));
    }

    public function remaining(Request $request)
    {
        $colors = ColorMaster::select('id', 'name')->where('status', 1)->orderBy('name')->get();
        $sizes = SizeMaster::select('id', 'name')->where('status', 1)->orderBy('name')->get();

        if ($request->ajax()) {
            $color_search = $request->color_search;
            $size_search = $request->size_search;

            if ($color_search) {
                $colors = ColorMaster::where('status', 1)
                    ->where('name', 'like', '%' . $color_search . '%')
                    ->orderBy('name')->get();
            }
            if ($size_search) {
                $sizes = SizeMaster::where('status', 1)
                    ->where('name', 'like', '%' . $size_search . '%')
                    ->orderBy('name')->get();
            }

            return view('admin.common_stock.remaining_matrix_partial', compact('colors', 'sizes'));
        }

        return view('admin.common_stock.remaining', compact('colors', 'sizes'));
    }

    public function remaining_list(Request $request)
    {
        $colors = ColorMaster::select('id', 'name')->where('status', 1)->orderBy('name')->get();
        $sizes = SizeMaster::select('id', 'name')->where('status', 1)->orderBy('name')->get();
        return view('admin.common_stock.remaining_list', compact('colors', 'sizes'));
    }

    public function remaining_list_datatable(Request $request)
    {
        // Fetch data grouped by size and color
        $query = CommonManageStock::with(['color', 'size'])
            ->select('color_id', 'size_id', DB::raw("SUM(CASE WHEN in_out = 'In' THEN quantity ELSE -quantity END) as balance"))
            ->groupBy('color_id', 'size_id');

        if ($request->color_id) {
            $query->where('color_id', $request->color_id);
        }
        if ($request->size_id) {
            $query->where('size_id', $request->size_id);
        }
        
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('color', function($cq) use ($search) {
                    $cq->where('name', 'like', '%' . $search . '%');
                })->orWhereHas('size', function($sq) use ($search) {
                    $sq->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        if ($request->balance_type == 'active') {
            $query->havingRaw("SUM(CASE WHEN in_out = 'In' THEN quantity ELSE -quantity END) != 0");
        }

        // To support the new merging design, we fetch all filtered records and group them by size
        $all_stocks = $query->get();
        
        // Group by size name (or size_id)
        $grouped_stocks = $all_stocks->groupBy(function($item) {
            return $item->size->name ?? 'N/A';
        });

        // We'll pass the grouped collection instead of a paginator for this specific design
        return view('admin.common_stock.remaining_list_datatable', compact('grouped_stocks'));
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $input['user_id'] = auth()->id();
        $input['from'] = $request->from ?? 'Manually';
        $input['from_id'] = $request->from_id ?? 0;

        // Prevent negative stock for 'Out' transactions
        if ($request->in_out == 'Out') {
            $current_stock = CommonManageStock::where('color_id', $request->color_id)
                ->where('size_id', $request->size_id)
                ->selectRaw("SUM(CASE WHEN in_out = 'In' THEN quantity ELSE -quantity END) as total")
                ->first()->total ?? 0;

            if ($request->quantity > $current_stock) {
                return response()->json([
                    'result' => 0, 
                    'message' => 'Insufficient stock. Current available: ' . number_format($current_stock, 3)
                ]);
            }
        }

        $stock = CommonManageStock::create($input);

        if ($stock) {
            return response()->json([
                'result' => 1,
                'message' => 'Stock ' . $request->in_out . ' recorded successfully'
            ]);
        }

        return response()->json(['result' => 0, 'message' => 'Failed to save stock.']);
    }

    public function edit_modal($id)
    {
        $manage_stock = CommonManageStock::select('id', 'date', 'in_out', 'color_id', 'size_id', 'quantity', 'remarks')->find($id);
        $colors = ColorMaster::select('id', 'name')->where('status', 1)->orderBy('name')->get();
        $sizes = SizeMaster::select('id', 'name')->where('status', 1)->orderBy('name')->get();
        
        return view('admin.common_stock.edit_modal', compact('manage_stock', 'colors', 'sizes'));
    }

    public function update(Request $request, $id)
    {
        $stock = CommonManageStock::find($id);
        if ($stock) {
            // Check if changing quantity results in negative stock
            $in_out = $request->in_out ?? $stock->in_out;
            $qty = $request->quantity ?? $stock->quantity;
            $color_id = $request->color_id ?? $stock->color_id;
            $size_id = $request->size_id ?? $stock->size_id;

            // Calculate "Others" stock (excluding current record)
            $others_stock = CommonManageStock::where('id', '!=', $id)
                ->where('color_id', $color_id)
                ->where('size_id', $size_id)
                ->selectRaw("SUM(CASE WHEN in_out = 'In' THEN quantity ELSE -quantity END) as total")
                ->first()->total ?? 0;

            $new_balance = ($in_out == 'In') ? ($others_stock + $qty) : ($others_stock - $qty);

            if ($new_balance < 0) {
                return response()->json([
                    'result' => 0, 
                    'message' => 'Insufficient stock for this update. Resulting balance would be negative (' . number_format($new_balance, 3) . ')'
                ]);
            }

            $stock->update($request->all());
            return response()->json([
                'result' => 1,
                'message' => 'Stock record updated successfully'
            ]);
        }
        return response()->json(['result' => 0, 'message' => 'Record not found.']);
    }

    public function delete($id)
    {
        if (auth()->user()->role_as != 'Admin') {
            return response()->json(['result' => -1, 'message' => 'Access Denied! Only Admin can delete stock records.']);
        }
        $stock = CommonManageStock::find($id);
        if ($stock) {
            // If deleting an 'In' entry, check if balance becomes negative
            if ($stock->in_out == 'In') {
                $current_stock = CommonManageStock::where('color_id', $stock->color_id)
                    ->where('size_id', $stock->size_id)
                    ->selectRaw("SUM(CASE WHEN in_out = 'In' THEN quantity ELSE -quantity END) as total")
                    ->first()->total ?? 0;

                if ($stock->quantity > $current_stock) {
                    return response()->json([
                        'result' => 0, 
                        'message' => 'Cannot delete this Stock IN. It would result in negative inventory (' . number_format($current_stock - $stock->quantity, 3) . ')'
                    ]);
                }
            }

            $stock->delete();
            return response()->json([
                'result' => 1,
                'message' => 'Stock record deleted successfully'
            ]);
        }
        return response()->json(['result' => 0, 'message' => 'Record not found.']);
    }

    public function history(Request $request)
    {
        $color_id = $request->color_id;
        $size_id = $request->size_id;
        
        $color = ColorMaster::find($color_id);
        $size = SizeMaster::find($size_id);
        
        return view('admin.common_stock.history_modal', compact('color', 'size', 'color_id', 'size_id'));
    }

    public function history_datatable(Request $request)
    {
        $query = CommonManageStock::with('user')
            ->where('color_id', $request->color_id)
            ->where('size_id', $request->size_id);
            
        $query = auth()->user()->applyDataRestriction($query);

        if ($request->from_date) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $history = $query->latest('date')->latest('id')->get();
        return view('admin.common_stock.history_datatable', compact('history'));
    }

    public function get_current_stock(Request $request)
    {
        $color_id = $request->color_id;
        $size_id = $request->size_id;
        $exclude_slip_id = $request->exclude_slip_id;

        $query = CommonManageStock::where('color_id', $color_id)
            ->where('size_id', $size_id);
            
        if ($exclude_slip_id) {
            $query->where(function($q) use ($exclude_slip_id) {
                $q->where('from', '!=', 'Packing Slip')
                  ->orWhere('from_id', '!=', $exclude_slip_id);
            });
        }

        $stock = $query->selectRaw("SUM(CASE WHEN in_out = 'In' THEN quantity ELSE -quantity END) as total")
            ->first();

        return response()->json([
            'result' => 1,
            'current_stock' => $stock ? $stock->total : 0
        ]);
    }

    public function report(Request $request)
    {
        if (auth()->user()->role_as != 'Admin') {
            abort(403, 'Unauthorized. Only Admin can generate reports.');
        }
        $type = $request->type; // 'In', 'Out', or 'Remaining'
        
        if ($type == 'Remaining') {
            $colors = ColorMaster::select('id', 'name')->where('status', 1)->orderBy('name')->get();
            $sizes = SizeMaster::select('id', 'name')->where('status', 1)->orderBy('name')->get();
            
            // Fetch all stock records for calculation in view
            $stock_data = CommonManageStock::select('color_id', 'size_id')
                ->selectRaw("SUM(CASE WHEN in_out = 'In' THEN quantity ELSE -quantity END) as total")
                ->groupBy('color_id', 'size_id')
                ->get()
                ->keyBy(function($item) {
                    return $item->color_id . '-' . $item->size_id;
                });

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.common_stock.pdf_remaining', compact('colors', 'sizes', 'stock_data'))
                ->setPaper('a4', 'landscape');
            return $pdf->stream('Common_Remaining_Stock.pdf');
        } elseif ($type == 'RemainingList') {
            // Group everything by color and size to get remaining balance
            $query = CommonManageStock::with(['color', 'size'])
                ->select('color_id', 'size_id', DB::raw("SUM(CASE WHEN in_out = 'In' THEN quantity ELSE -quantity END) as balance"))
                ->groupBy('color_id', 'size_id');

            if ($request->color_id) {
                $query->where('color_id', $request->color_id);
            }
            if ($request->size_id) {
                $query->where('size_id', $request->size_id);
            }
            if ($request->balance_type == 'active') {
                $query->havingRaw("SUM(CASE WHEN in_out = 'In' THEN quantity ELSE -quantity END) != 0");
            }
            if ($request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->whereHas('color', function($cq) use ($search) {
                        $cq->where('name', 'like', '%' . $search . '%');
                    })->orWhereHas('size', function($sq) use ($search) {
                        $sq->where('name', 'like', '%' . $search . '%');
                    });
                });
            }

            $records = $query->get();
            $grouped_stocks = $records->groupBy(function($item) {
                return $item->size->name ?? 'N/A';
            });

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.common_stock.pdf_remaining_list', compact('grouped_stocks'))
                ->setPaper('a4', 'portrait');
            return $pdf->stream('Common_Remaining_Stock_List.pdf');
        } else {
            // 'In' or 'Out'
            $query = CommonManageStock::with(['color', 'size', 'user'])
                ->where('in_out', $type);

            if ($request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->whereHas('color', function($cq) use ($search) {
                        $cq->where('name', 'like', '%' . $search . '%');
                    })->orWhereHas('size', function($sq) use ($search) {
                        $sq->where('name', 'like', '%' . $search . '%');
                    })->orWhere('remarks', 'like', '%' . $search . '%');
                });
            }

            $records = $query->latest('date')->latest('id')->get();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.common_stock.pdf_list', compact('records', 'type'))
                ->setPaper('a4', 'portrait');
            return $pdf->stream('Common_Stock_' . $type . '.pdf');
        }
    }
}
