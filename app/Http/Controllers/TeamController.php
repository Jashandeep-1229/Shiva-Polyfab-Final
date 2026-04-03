<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index()
    {
        if (!\App\Helpers\PermissionHelper::check('team_management')) {
            abort(403, 'Unauthorized access to Team Management.');
        }
        $managers = User::whereIn('role_as', ['Manager', 'Senior Sale Executive'])->get();
        return view('admin.team.index', compact('managers'));
    }

    public function datatable(Request $request)
    {
        $number = $request->value ?? 50;
        $query = User::query();
        
        if (auth()->user()->role_as != 'Admin' && \App\Helpers\PermissionHelper::accessMode('team_management') != 'all') {
            if (auth()->user()->role_as == 'Manager') {
                // If Manager and in Restricted/Owned mode, see team members and self
                $query->where(function($q) {
                    $q->where('created_by_id', auth()->id())
                      ->orWhereIn('id', auth()->user()->getManagedUserIds())
                      ->orWhere('id', auth()->id());
                });
            } else {
                // Others see only themselves
                $query->where('id', auth()->id());
            }
        }
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }
        // Normally you might want to exclude the current admin from the list or something, 
        // but for now I'll just list all.
        $team = $query->with('managedBy')->latest('id')->paginate($number);
        return view('admin.team.datatable', compact('team'));
    }

    public function edit_modal($id)
    {
        if (!\App\Helpers\PermissionHelper::check('team_management', 'edit')) {
            return '<div class="alert alert-danger m-3">Access Denied! You do not have permission to edit records.</div>';
        }
        $user = User::with('managedBy')->find($id);
        $managers = User::whereIn('role_as', ['Manager', 'Senior Sale Executive'])->get();
        return view('admin.team.modal', compact('user', 'managers'));
    }

    public function delete($id)
    {
        if (!\App\Helpers\PermissionHelper::check('team_management', 'delete')) {
            return ['result' => -1, 'message' => 'Access Denied! You do not have permission to delete records.'];
        }
        if ($id == Auth::id()) {
            return ['result' => -1, 'message' => 'You cannot delete yourself!'];
        }
        $user = User::find($id);
        $user->delete();
        return ['result' => 1, 'message' => 'Team member deleted successfully'];
    }

    public function store(Request $request)
    {
        $user = User::find($request->user_id);
        if ($user) {
            // Update
            if (!\App\Helpers\PermissionHelper::check('team_management', 'edit')) {
                return ['result' => -1, 'message' => 'Access Denied! You do not have permission to edit records.'];
            }
            $check = User::where('email', $request->email)->where('id', '!=', $user->id)->first();
            if ($check) {
                return ['result' => -1, 'message' => 'Email already exists'];
            }
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->role_as = $request->role_as;
            if ($request->password) {
                $user->password = Hash::make($request->password);
                $user->show_password = $request->password;
            }
            $user->save();
            
            $user->managedBy()->sync($request->manager_ids);
            
            return ['result' => 1, 'message' => 'Team member updated successfully'];
        } else {
            // Create
            if (!\App\Helpers\PermissionHelper::check('team_management', 'add')) {
                return ['result' => -1, 'message' => 'Access Denied! You do not have permission to add new records.'];
            }
            $check = User::where('email', $request->email)->first();
            if ($check) {
                return ['result' => -1, 'message' => 'Email already exists'];
            }
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password = Hash::make($request->password);
            $user->show_password = $request->password;
            $user->role_as = $request->role_as;
            $user->created_by_id = Auth::id();
            $user->save();
            
            $user->managedBy()->sync($request->manager_ids);
            
            return ['result' => 1, 'message' => 'Team member added successfully'];
        }
    }

    public function member_list(Request $request)
    {
        $role = $request->role;
        $query = User::query();
        if (is_array($role)) {
            $query->whereIn('role_as', $role);
        } else {
            $query->where('role_as', $role);
        }
        $users = $query->where('status', 1)->get();
        $html = '';
        foreach ($users as $user) {
            $html .= '<option value="' . $user->id . '">' . $user->name . '</option>';
        }
        return $html;
    }
}
