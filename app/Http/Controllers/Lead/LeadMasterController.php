<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\LeadSource;
use App\Models\LeadTag;
use App\Models\LeadStatus;
use Illuminate\Http\Request;

class LeadMasterController extends Controller
{
    // Source Methods
    public function sourceIndex()
    {
        $sources = LeadSource::all();
        return view('lead.master.source.index', compact('sources'));
    }

    public function sourceStore(Request $request)
    {
        $request->validate(['name' => 'required']);
        LeadSource::updateOrCreate(['id' => $request->id], $request->all());
        return back()->with('success', 'Source saved successfully');
    }

    public function sourceDelete($id)
    {
        LeadSource::find($id)->delete();
        return back()->with('danger', 'Source deleted');
    }

    // Tag Methods
    public function tagIndex()
    {
        $tags = LeadTag::all();
        return view('lead.master.tag.index', compact('tags'));
    }

    public function tagStore(Request $request)
    {
        $request->validate(['name' => 'required']);
        LeadTag::updateOrCreate(['id' => $request->id], $request->all());
        return back()->with('success', 'Tag saved successfully');
    }

    public function tagDelete($id)
    {
        LeadTag::find($id)->delete();
        return back()->with('danger', 'Tag deleted');
    }

    // Status (Steps) Methods
    public function statusIndex()
    {
        $statuses = LeadStatus::orderBy('sort_order')->get();
        return view('lead.master.status.index', compact('statuses'));
    }

    public function statusStore(Request $request)
    {
        $request->validate(['name' => 'required']);
        $data = $request->all();
        $data['is_required'] = $request->has('is_required') ? 1 : 0;
        
        LeadStatus::updateOrCreate(['id' => $request->id], $data);
        return back()->with('success', 'Step saved successfully');
    }

    public function statusUpdateOrder(Request $request)
    {
        if ($request->has('order')) {
            foreach ($request->order as $index => $id) {
                LeadStatus::where('id', $id)->update(['sort_order' => $index + 1]);
            }
        }
        return response()->json(['status' => 'success']);
    }

    public function statusUpdateField(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:lead_statuses,id',
            'field' => 'required',
            'value' => 'required'
        ]);

        LeadStatus::where('id', $request->id)->update([$request->field => $request->value]);
        return response()->json(['status' => 'success']);
    }

    public function statusDelete($id)
    {
        LeadStatus::find($id)->delete();
        return back()->with('danger', 'Step deleted');
    }
}
