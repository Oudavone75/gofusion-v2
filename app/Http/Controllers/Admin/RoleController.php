<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AppCommonFunction;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    use AppCommonFunction;

    public function index()
    {
        $query = Role::with('permissions')->where('guard_name', 'admin')->where('name', '!=', 'Admin');
        $roles = $this->getPaginatedData($query);

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::where('guard_name', 'admin')->where('company_id', null)->select('name', 'id')->get();
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'admin',
        ]);

        $permissions = Permission::whereIn('id', $request->permissions)
            ->pluck('name')
            ->toArray();

        $role->syncPermissions($permissions);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::where('guard_name', 'admin')->select('name', 'id')->get();

        return view('admin.roles.edit', compact('role', 'permissions'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
            'permissions' => 'required|array',
        ]);

        $role = Role::findOrFail($id);
        $role->update([
            'name' => $request->name,
        ]);

        $permissions = Permission::whereIn('id', $request->permissions)
            ->pluck('name')
            ->toArray();

        $role->syncPermissions($permissions);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }


    public function destroy($id)
    {
        Role::findOrFail($id)->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }

    public function show($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::where('guard_name', 'admin')->pluck('name', 'id');

        return view('admin.roles.view', compact('role', 'permissions'));
    }
}
