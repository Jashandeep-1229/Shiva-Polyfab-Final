<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index()
    {
        $this->checkAdmin();
        $users = User::with('parent')->get();
        $parents = User::where('role_as', 'Senior Sale Executive')->get();
        return view('lead.team.index', compact('users', 'parents'));
    }

    public function store(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $request->id,
            'password' => $request->id ? 'nullable' : 'required|min:6',
            'role' => 'required',
        ]);

        $data = $request->except(['password', 'password_confirmation', 'role']);
        if ($request->password) {
            $data['password'] = Hash::make($request->password);
            $data['show_password'] = $request->password;
        }
        $data['role_as'] = $request->role;

        User::updateOrCreate(['id' => $request->id], $data);
        return back()->with('success', 'Team member saved successfully');
    }

    public function delete($id)
    {
        $this->checkAdmin();
        if ($id == auth()->id()) {
            return back()->with('danger', 'You cannot delete yourself');
        }
        User::find($id)->delete();
        return back()->with('danger', 'Team member deleted');
    }

    private function checkAdmin()
    {
        $role = auth()->user()->role;
        if (!in_array($role, ['Admin', 'Senior Sale Executive'])) {
            abort(403, 'Unauthorized action.');
        }
    }
}
