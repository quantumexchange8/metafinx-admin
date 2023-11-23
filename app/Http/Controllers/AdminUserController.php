<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function admin_listing()
    {
        $admins = User::query()
            ->whereNot('role', 'user')
            ->latest()
            ->select('id', 'name', 'email', 'role', 'password')
            ->get()
            ->map(function ($user) {
                $user->role = ucwords(str_replace('_', ' ', $user->role));
                $user->profile_photo_url = $user->getFirstMediaUrl('profile_photo');
                return $user;
            });

        return Inertia::render('AdminUser/AdminListing', [
            'admins' => $admins
        ]);
    }

    public function add_sub_admin()
    {
        $permissions = Permission::query()
            ->get()
            ->groupBy(function ($permission) {
                return ucwords(str_replace('_', ' ', $permission->type));
            });

        return Inertia::render('AdminUser/AddSubAdmin', [
            'permissions' => $permissions,
        ]);
    }

    public function addSubAdmin(Request $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'country' => 'Administration',
            'phone' => 'Administration - ' . rand(1000, 9999),
            'password' => Hash::make($request->password),
            'role' => \Str::slug($request->position, '_'),
        ]);

        $existing_roles = Role::all();

        foreach ($existing_roles as $existing_role) {
            if ($existing_role->name == $user->role) {
                // Role already exists, assign it to the user
                $role = $existing_role;
            } else {
                // Role doesn't exist, create a new role and assign it to the user
                $role = Role::firstOrCreate(
                    ['name' => $user->role],
                    ['guard_name' => 'web']
                );
            }
            $user->assignRole($role);
        }

        $user->givePermissionTo($request->permissions);

        return to_route('admin_user.admin_listing')->with('title', 'Sub-admin added!')->with('success', 'This new sub-admin has been added successfully.');
    }

    public function edit_sub_admin(Request $request, $id)
    {
        $permissions = Permission::query()
            ->get()
            ->groupBy(function ($permission) {
                return ucwords(str_replace('_', ' ', $permission->type));
            });

        $user = User::find($id);
        $user->role = ucwords(str_replace('_', ' ', $user->role));

        return Inertia::render('AdminUser/EditSubAdmin', [
            'permissions' => $permissions,
            'admin_permissions' => User::find($id)->getPermissionNames(),
            'admin' => $user,
        ]);
    }

    public function editSubAdmin(Request $request)
    {
        $user = User::find($request->id);

        $user->update([
            'name' => $request->name,
            'role' => \Str::slug($request->position, '_'),
        ]);

        if ($request->password) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        $existing_roles = Role::all();

        foreach ($existing_roles as $existing_role) {
            if ($existing_role->name == $user->role) {
                // Role already exists, assign it to the user
                $role = $existing_role;
            } else {
                // Role doesn't exist, create a new role and assign it to the user
                $role = Role::firstOrCreate(
                    ['name' => $user->role],
                    ['guard_name' => 'web']
                );
            }
            $user->assignRole($role);
        }

        $user->syncPermissions($request->permissions);

        return to_route('admin_user.admin_listing')->with('title', 'Sub-admin updated!')->with('success', 'This new sub-admin has been added successfully.');
    }

    public function deleteSubAdmin(Request $request)
    {
        dd($request->all());
    }
}
