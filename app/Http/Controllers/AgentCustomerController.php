<?php

namespace App\Http\Controllers;

use App\Models\AgentCustomer;
use App\Helpers\PermissionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;

class AgentCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       if (!PermissionHelper::check('agent_customer')) {
           abort(403, 'Unauthorized access to Agent / Customer Master.');
       }
       $sales_executives = \App\Models\User::whereIn('role_as', ['Sale Executive', 'Senior Sale Executive'])->where('status', 1)->get();
       return view('admin.agent_customer.index', compact('sales_executives'));
    }

    public function datatable(Request $request){
        $number = $request->value ?? 50;
        $query = AgentCustomer::with('sale_executive');
        $query = auth()->user()->applyDataRestriction($query, 'sale_executive_id', 'agent_customer');
        if($request->search){
            $query->where(function($q) use ($request){
                $q->where('name','like','%'.$request->search.'%')
                ->orWhere('phone_no','like','%'.$request->search.'%')
                ->orWhere('code','like','%'.$request->search.'%')
                ->orWhere('role','like','%'.$request->search.'%')
                ->orWhereHas('sale_executive', function($sq) use ($request) {
                    $sq->where('name', 'like', '%' . $request->search . '%');
                });
            });
        }
        if($request->sale_executive_id){
            $query->where('sale_executive_id', $request->sale_executive_id);
        }
        if($request->role_filter){
            $query->where('role', $request->role_filter);
        }
        if($request->type_filter){
            $query->where('type', $request->type_filter);
        }
        $agent_customer = $query->latest('id')->paginate($number);
        return view('admin.agent_customer.datatable',compact('agent_customer'));
    }

    public function agent_customer_list(Request $request){
        $agents = AgentCustomer::select('id', 'name','phone_no', 'sale_executive_id')->where('status',1);
        $agents = auth()->user()->applyDataRestriction($agents, 'sale_executive_id', 'agent_customer');
        if($request->role ?? 0){
            $agents->where('role',$request->role);
        }
        $agents = $agents->get();
        return view('admin.agent_customer.list',compact('agents'));
    }

    public function edit_modal($id){
        if ($id > 0) {
            if (!PermissionHelper::check('agent_customer', 'edit')) {
                return 'Access Denied! You do not have permission to edit records.';
            }
        } else {
            if (!PermissionHelper::check('agent_customer', 'add')) {
                return 'Access Denied! You do not have permission to add records.';
            }
        }
        $agent_customer = AgentCustomer::find($id);
        $sales_executives = \App\Models\User::whereIn('role_as', ['Sale Executive', 'Senior Sale Executive'])->where('status', 1)->get();
        return view('admin.agent_customer.modal',compact('agent_customer', 'sales_executives'));
    }

     public function change_status($id){
        if (!PermissionHelper::check('agent_customer', 'edit')) {
            return ['result' => -1, 'message' => 'Access Denied! You do not have permission to edit records.'];
        }
        $agent_customer = AgentCustomer::find($id);
        if($agent_customer){
            $agent_customer->status = $agent_customer->status == 1 ? 0 : 1;
            $agent_customer->save();
            $data = [
                'result' => 1,
                'message' => 'Status Changed Successfully'
            ];
        }
        return $data;
    }

    public function delete($id)
    {
        if (!PermissionHelper::check('agent_customer', 'delete')) {
            return ['result' => -1, 'message' => 'Access Denied! You do not have permission to delete records.'];
        }
        $agent_customer = AgentCustomer::find($id);
        $agent_customer->delete();
        $data = 
        [
            'result' => 1,
            'message' => 'Deleted Successfully',
        ];
        return $data;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone_no' => 'required|digits:10',
            'role' => 'required',
            'type' => 'required',
            'sale_executive_id' => 'required',
            'gst' => 'required',
        ]);

        if(strlen($request->phone_no) != 10){
            return [
                'result' => -1,
                'message' => 'Phone number must be exactly 10 digits.',
                'from' => 'Agent / Customer Master'
            ];
        }
        $agent_customer = AgentCustomer::find($request->agent_customer_id);
        
        // Validation check for phone_no unique constraint
        if($agent_customer){
            $check = AgentCustomer::where('phone_no', $request->phone_no)
                                  ->where('id', '!=', $agent_customer->id)
                                  ->first();
        } else {
             $check = AgentCustomer::where('phone_no', $request->phone_no)->first();
        }

        if($check){
            if(auth()->user()->role_as == 'Admin'){
                if(!$request->confirm_duplicate){
                    return [
                        'result' => 2,
                        'message' => 'Phone Number Already Used for "' . $check->name . '" ('. $check->code .'). Do you still want to continue with a duplicate entry?',
                        'from' => 'Agent / Customer Master'
                    ];
                }
            } else {
                return [
                    'result' => -1,
                    'message' => 'Phone Number Already Used for "' . $check->name . '" ('. $check->code .'). Only Admin can permit duplicate phone numbers.',
                    'from' => 'Agent / Customer Master'
                ];
            }
        }

        if($agent_customer){
            // Check for edit permission
            if(!PermissionHelper::check('agent_customer', 'edit')){
                return [
                    'result' => -1,
                    'message' => 'Access Denied! You do not have permission to edit existing records.',
                    'from' => 'Agent / Customer Master'
                ];
            }
            // Update logic
            $old_role = $agent_customer->role;
            $agent_customer->name = $request->name;
            $agent_customer->phone_no = $request->phone_no;
            $agent_customer->role = $request->role;
            $agent_customer->type = $request->type;
            $agent_customer->sale_executive_id = $request->sale_executive_id;
            $agent_customer->gst = $request->gst;
            $agent_customer->address = $request->address;
            $agent_customer->pincode = $request->pincode;
            $agent_customer->state = $request->state;
            $agent_customer->city = $request->city;
            $agent_customer->remarks = $request->remarks;

            // If role changed, update the code prefix (C or A) but keep the rest
            if($old_role != $request->role){
                $co = ($request->role == 'Customer') ? 'C' : 'A';
                $current_code = $agent_customer->code;
                if($current_code && strlen($current_code) > 3){
                    // Replace the 4th character (index 3) with the new prefix
                    $agent_customer->code = substr_replace($current_code, $co, 3, 1);
                }
            }
            
            $agent_customer->save();
            $data = [
                'result' => 1,
                'message' => 'Updated Successfully',
                'from' => 'Agent / Customer Master'
            ];

        }else{
            // Check for add permission
            if(!PermissionHelper::check('agent_customer', 'add')){
                return [
                    'result' => -1,
                    'message' => 'Access Denied! You do not have permission to add new records.',
                    'from' => 'Agent / Customer Master'
                ];
            }
            // Create logic
            $agent_customer = new AgentCustomer();
            $agent_customer->name = $request->name;
            $agent_customer->phone_no = $request->phone_no;
            $agent_customer->role = $request->role;
            $agent_customer->type = $request->type;
            $agent_customer->sale_executive_id = $request->sale_executive_id;
            $agent_customer->gst = $request->gst;
            $agent_customer->address = $request->address;
            $agent_customer->pincode = $request->pincode;
            $agent_customer->state = $request->state;
            $agent_customer->city = $request->city;
            $agent_customer->remarks = $request->remarks;
            $agent_customer->user_id = auth()->user()->id;
            $agent_customer->status = 1;
            $agent_customer->save(); 
            
            $co = ($request->role == 'Customer') ? 'C' : 'A';
            
            do {
                 $generated_code = 'SPF' . $co . $agent_customer->id . rand(10000, 99999);
                 $code_check = AgentCustomer::where('code', $generated_code)->exists();
            } while ($code_check);

            $agent_customer->code = $generated_code;
            $agent_customer->save();

            $data = [
                'result' => 1,
                'message' => 'Added Successfully',
                'from' => 'Agent / Customer Master'
            ];
        }
        return $data;
    }

    public function check_lead(Request $request) {
        return response()->json(['status' => 'not_found']);
    }

    public function convert($id) {
        return response()->json(['result' => -1, 'message' => 'Not Supportable here']);
    }

    public function upload(Request $request)
    {
        if (!PermissionHelper::check('agent_customer', 'add')) {
            return response()->json(['result' => -1, 'message' => 'Unauthorized action.']);
        }

        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv'
        ]);

        ini_set('max_execution_time', 600);

        try {
            $file = $request->file('excel_file');
            // Use GenericImport to handle calculated formulas in Excel
            $data = Excel::toArray(new \App\Imports\GenericImport, $file);
            
            if (empty($data) || empty($data[0])) {
                return back()->with('error', 'Excel file is empty or invalid.');
            }

            $rows = $data[0];
            \Log::info("Bulk Update Started. Rows: " . count($rows));

            // Helper to clean phone numbers from mixed Excel values
            $cleanPhoneFunc = function($val) {
                if (is_numeric($val)) {
                    $val = sprintf('%.0f', $val);
                }
                $val = strval($val);
                if (str_contains($val, '.')) {
                    $val = explode('.', $val)[0];
                }
                $val = preg_replace('/[^0-9]/', '', $val);
                return (strlen($val) >= 10) ? substr($val, -10) : null;
            };

            // Collect all unique phones for batch fetching
            $phonesInExcel = [];
            foreach ($rows as $index => $row) {
                if ($index < 1) continue; 
                $p = $cleanPhoneFunc($row[3] ?? '');
                if ($p) $phonesInExcel[] = $p;
            }
            
            $existingCustomers = AgentCustomer::whereIn('phone_no', array_unique($phonesInExcel))
                ->get()
                ->keyBy('phone_no');

            \Log::info("Bulk Update: Found " . count($phonesInExcel) . " possible phones, " . count($existingCustomers) . " matched in DB.");

            $updatedCount = 0;
            $pincodeCache = [];

            foreach ($rows as $index => $row) {
                if ($index < 1) continue; 

                $phone = $cleanPhoneFunc($row[3] ?? '');
                if (!$phone) continue;

                $customer = $existingCustomers->get($phone);
                if (!$customer) continue;

                $addr1 = trim(strval($row[1] ?? ''));
                $addr2 = trim(strval($row[2] ?? ''));
                $gst = trim(strval($row[5] ?? ''));
                $pincodeVal = $row[8] ?? '';
                if (is_numeric($pincodeVal)) $pincodeVal = sprintf('%.0f', $pincodeVal);
                $pincode = preg_replace('/[^0-9]/', '', strval($pincodeVal));

                $newAddress = $addr1 . ($addr2 ? ', ' . $addr2 : '');
                $hasChanged = false;

                if ($newAddress && $customer->address != $newAddress) {
                    $customer->address = $newAddress;
                    $hasChanged = true;
                }

                if ($gst && $customer->gst != $gst && $gst != 'NA') {
                    $customer->gst = $gst;
                    $hasChanged = true;
                }

                if ($pincode && strlen($pincode) == 6 && $customer->pincode != $pincode) {
                    $customer->pincode = $pincode;
                    $hasChanged = true;

                    if (!array_key_exists($pincode, $pincodeCache)) {
                        $pincodeCache[$pincode] = $this->fetchLocation($pincode);
                        usleep(100000); // 0.1s to avoid hitting API too hard
                    }

                    $location = $pincodeCache[$pincode];
                    if ($location) {
                        $customer->state = $location['state'];
                        $customer->city = $location['city'];
                    }
                }

                if ($hasChanged) {
                    $customer->save();
                    $updatedCount++;
                }
            }

            \Log::info("Bulk Update Finished. Total Updated: $updatedCount");
            return response()->json([
                'result' => 1,
                'message' => "Excel processed successfully. Updated $updatedCount records.",
                'from' => 'Agent / Customer Master'
            ]);

        } catch (\Exception $e) {
            \Log::error("Bulk Update Error: " . $e->getMessage());
            return response()->json([
                'result' => -1,
                'message' => 'Error processing file: ' . $e->getMessage()
            ]);
        }
    }

    private function fetchLocation($pincode)
    {
        try {
            $response = Http::timeout(5)->get("https://api.postalpincode.in/pincode/{$pincode}")->json();
            if (!empty($response) && $response[0]['Status'] === 'Success') {
                $postOffices = $response[0]['PostOffice'];
                $state = $postOffices[0]['State'];
                $city = $postOffices[0]['District'];
                
                foreach ($postOffices as $po) {
                    if ($po['Name'] === $po['Block']) {
                        $city = $po['Name'];
                        break;
                    }
                }
                return ['state' => $state, 'city' => $city];
            }
        } catch (\Exception $e) {
            \Log::error("Pincode API Error ($pincode): " . $e->getMessage());
        }
        return null;
    }
}
