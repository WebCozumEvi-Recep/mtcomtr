<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $role = new Role();
        return view('admin.roles.form', compact('role'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        
        $role = new Role();
        $role->name = $request->name;
        $role->permissions = $request->permissions; // Array from checkboxes
        $role->save();

        return redirect()->route('admin.roles')->with('success', 'Rol oluşturuldu.');
    }

    public function edit(Role $role)
    {
        return view('admin.roles.form', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate(['name' => 'required|string|max:255']);
        
        $role->name = $request->name;
        $role->permissions = $request->permissions;
        $role->save();

        return redirect()->route('admin.roles')->with('success', 'Rol güncellendi.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('admin.roles')->with('success', 'Rol silindi.');
    }
}
