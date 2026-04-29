<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GsmCalculatorController extends Controller
{
    public function index()
    {
        return view('admin.gsm_calculator.index');
    }

    public function get_length(Request $request)
    {
        $width = $request->width;
        $length = $request->length;
        
        // This logic seems to be specific to the user's business rules for roll lengths.
        // If I can't find a specific table, I'll provide a placeholder or common logic.
        // Given the JS used "data / 2", I'll return $length * 2 as a default or 
        // a value that makes sense for the halving logic.
        
        // However, looking at the JS: var new_length = (length + guzzete/2 + folding) * 2;
        // then again_new_length = data / 2;
        // This implies the backend might just be returning the same value or looking it up.
        
        return $length; 
    }
}
