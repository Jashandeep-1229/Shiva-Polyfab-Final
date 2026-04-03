<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeadState;
use App\Models\LeadCity;

class LeadLocationController extends Controller
{
    public function index()
    {
        $states = LeadState::withCount('cities')->orderBy('name')->get();
        return view('lead.locations.index', compact('states'));
    }
    public function cityIndex(Request $request)
    {
        $query = LeadCity::with('state');
        if ($request->state_id) {
            $query->where('state_id', $request->state_id);
        }
        $cities = $query->orderBy('name')->paginate(50);
        $states = LeadState::orderBy('name')->get();
        return view('lead.locations.cities', compact('cities', 'states'));
    }

    public function storeState(Request $request)
    {
        $request->validate(['name' => 'required|unique:lead_states,name']);
        LeadState::create($request->all());
        return back()->with('success', 'State added successfully');
    }

    public function updateState(Request $request, $id)
    {
        $state = LeadState::findOrFail($id);
        $request->validate(['name' => 'required|unique:lead_states,name,' . $id]);
        $state->update($request->all());
        return back()->with('success', 'State updated successfully');
    }

    public function destroyState($id)
    {
        LeadState::findOrFail($id)->delete();
        return back()->with('danger', 'State deleted successfully');
    }

    public function storeCity(Request $request)
    {
        $request->validate([
            'state_id' => 'required|exists:lead_states,id',
            'name' => 'required'
        ]);
        
        // Check uniqueness within state
        $exists = LeadCity::where('state_id', $request->state_id)
            ->where('name', strtoupper($request->name))
            ->exists();
            
        if ($exists) {
            return back()->with('error', 'City already exists in this state');
        }

        LeadCity::create([
            'state_id' => $request->state_id,
            'name'     => strtoupper($request->name),
            'status'   => 1
        ]);
        return back()->with('success', 'City added successfully');
    }

    public function quickStoreCity(Request $request)
    {
        $state_name = $request->state_name;
        $city_name  = trim($request->city_name);

        if (!$state_name || !$city_name) {
            return response()->json(['success' => false, 'message' => 'State and City name are required.']);
        }

        $state = LeadState::where('name', $state_name)->first();
        if (!$state) {
            return response()->json(['success' => false, 'message' => 'State not found.']);
        }

        $exists = LeadCity::where('state_id', $state->id)
            ->where('name', strtoupper($city_name))
            ->exists();

        if ($exists) {
            return response()->json(['success' => true, 'message' => 'City already exists.', 'city' => $city_name]);
        }

        LeadCity::create([
            'state_id' => $state->id,
            'name'     => strtoupper($city_name),
            'status'   => 1
        ]);

        return response()->json(['success' => true, 'message' => 'City added successfully.', 'city' => $city_name]);
    }

    public function updateCity(Request $request, $id)
    {
        $city = LeadCity::findOrFail($id);
        $request->validate([
            'state_id' => 'required|exists:lead_states,id',
            'name' => 'required'
        ]);
        
        $exists = LeadCity::where('state_id', $request->state_id)
            ->where('name', $request->name)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'City already exists in this state');
        }

        $city->update($request->all());
        return back()->with('success', 'City updated successfully');
    }

    public function destroyCity($id)
    {
        LeadCity::findOrFail($id)->delete();
        return back()->with('danger', 'City deleted successfully');
    }

    public function getCitiesByState($state_name)
    {
        $state = LeadState::where('name', $state_name)->first();
        if (!$state) return response()->json([]);
        
        $cities = LeadCity::where('state_id', $state->id)->where('status', 1)->orderBy('name')->get();
        return response()->json($cities);
    }
}