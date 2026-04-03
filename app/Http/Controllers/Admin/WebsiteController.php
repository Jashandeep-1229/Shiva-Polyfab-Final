<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    public function index()
    {   
        return view('admin.settings.website-setting.index');
    }
    public function insert(request $request)
    {   
        $request->validate([
            'name'       => 'required|string|max:255',

            'logo_light' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'logo_dark'  => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'favicon'    => 'nullable|image|mimes:jpg,jpeg,png,webp,ico|max:1024',
        ]);
        $replacements[env('APP_NAME')] = $request->name;
        $replacements[env('CYLINDER_LIMIT')] = $request->cylinder_limit;
        $replacements[env('PRINTING_LIMIT')] = $request->printing_limit;
        $replacements[env('LAMINATION_LIMIT')] = $request->lamination_limit;
        $replacements[env('CUTTING_LIMIT')] = $request->cutting_limit;
        
        if($request->hasfile('logo_light')){
            $random_file_name = rand(00000,99999);
            $file = $request->logo_light;
            $file_name = 'logo_light'.time() . $random_file_name . '.' . $file->getClientOriginalExtension();
            $file->move(public_path().'/uploads/website',$file_name);
            $replacements[env('APP_LOGO_LIGHT')]= '/uploads/website/'.$file_name;
            if(file_exists(public_path(env('APP_LOGO_LIGHT')))){
                unlink(public_path(env('APP_LOGO_LIGHT')));
            }
        }
        if($request->hasfile('logo_dark')){
            $random_file_name = rand(00000,99999);
            $file = $request->logo_dark;
            $file_name = 'logo_dark'.time() . $random_file_name . '.' . $file->getClientOriginalExtension();
            $file->move(public_path().'/uploads/website',$file_name);
            $replacements[env('APP_LOGO_DARK')]= '/uploads/website/'.$file_name;
            if(file_exists(public_path(env('APP_LOGO_DARK')))){
                unlink(public_path(env('APP_LOGO_DARK')));
            }
        }
        if($request->hasfile('favicon')){
            $random_file_name = rand(00000,99999);
            $file = $request->favicon;
            $file_name = 'favicon'.time() . $random_file_name . '.' . $file->getClientOriginalExtension();
            $file->move(public_path().'/uploads/website',$file_name);
            $replacements[env('APP_FAVICON')]= '/uploads/website/'.$file_name;
            if(file_exists(public_path(env('APP_FAVICON')))){
                unlink(public_path(env('APP_FAVICON')));
            }
        }

        // Read the contents of the .env file
        $contents = file_get_contents(base_path('.env'));

        // Replace the values of the environment variables
        foreach ($replacements as $search => $replace) {
            $contents = str_replace($search, $replace, $contents);
        }

        // Write the updated contents to the .env file
        file_put_contents(base_path('.env'), $contents);
        
        return redirect()->back()->with('success','Website Setting Update Successfully');;
    }
}
