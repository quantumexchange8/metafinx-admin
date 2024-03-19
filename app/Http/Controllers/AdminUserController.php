<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;

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
        $permissionsList = Permission::query()
            ->get()
            ->groupBy(function ($permission) {
                return ucwords(str_replace('_', ' ', $permission->type));
            });

        return Inertia::render('AdminUser/AddSubAdmin', [
            'permissionsList' => $permissionsList,
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

        // Check if the role already exists
        $existing_role = Role::where('name', $user->role)->first();

        if (!$existing_role) {
            // Role doesn't exist, create a new role
            $existing_role = Role::create(['name' => $user->role, 'guard_name' => 'web']);
        }

        $user->assignRole($existing_role);

        // Assign permissions to the user
        $user->givePermissionTo($request->permissionsList);

        // Log activity
        Activity::create([
            'log_name' => 'user',
            'description' => \Auth::user()->name . ' has added a new sub-admin: ' . $user->name,
            'subject_type' => User::class,
            'subject_id' => \Auth::id(),
            'causer_type' => get_class(\Auth::user()),
            'causer_id' => \Auth::id(),
            'event' => 'created',
        ]);

        return to_route('admin_user.admin_listing')->with('title', 'Sub-admin added!')->with('success', 'This new sub-admin has been added successfully.');
    }

    public function edit_sub_admin(Request $request, $id)
    {
        $permissionsList = Permission::query()
            ->get()
            ->groupBy(function ($permission) {
                return ucwords(str_replace('_', ' ', $permission->type));
            });

        $user = User::find($id);
        $user->role = ucwords(str_replace('_', ' ', $user->role));

        return Inertia::render('AdminUser/EditSubAdmin', [
            'permissionsList' => $permissionsList,
            'admin_permissions' => User::find($id)->getPermissionNames(),
            'admin' => $user,
        ]);
    }

    public function editSubAdmin(Request $request)
    {
        $user = User::find($request->id);

        $originalUserData = $user->getOriginal();

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'role' => \Str::slug($request->position, '_'),
        ]);
    
        // Check if the role already exists
        $existing_role = Role::where('name', $user->role)->first();
    
        if (!$existing_role) {
            // Role doesn't exist, create a new role
            $existing_role = Role::create(['name' => $user->role, 'guard_name' => 'web']);
        }
    
        // Assign the existing or newly created role to the user
        $user->roles()->detach();
        $user->assignRole($existing_role);
    
        // Assign permissions to the user
        $user->permissions()->detach();
        $user->givePermissionTo($request->permissionsList);

        // Log activity
        Activity::create([
            'log_name' => 'user',
            'description' => \Auth::user()->name . ' has updated sub-admin: ' . $user->name,
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'causer_type' => get_class(\Auth::user()),
            'causer_id' => \Auth::id(),
            'properties' => [
                'original_user_data' => $originalUserData,
                'updated_user_data' => $user->fresh()->toArray()
            ],
            'event' => 'updated',
        ]);

    
        return to_route('admin_user.admin_listing')->with('title', 'Sub-admin updated!')->with('success', 'This sub-admin has been updated successfully.');
    }
    
    public function deleteSubAdmin(Request $request)
    {
        $userId = $request->id;
        
        // Find the user
        $user = User::find($userId);
        
        // Delete the user
        if ($user) {
            // Detach all roles
            $user->roles()->detach();

            // Detach all permissions
            $user->permissions()->detach();

            // Log activity
            Activity::create([
                'log_name' => 'user',
                'description' => \Auth::user()->name . ' has deleted sub-admin: ' . $user['name'],
                'subject_type' => User::class,
                'subject_id' => $user['id'],
                'causer_type' => get_class(\Auth::user()),
                'causer_id' => \Auth::id(),
                'properties' => ['deleted_user_data' => $user],
                'event' => 'deleted',
            ]);

            // Delete the user
            $user->delete();

            return redirect()->route('admin_user.admin_listing')->with('title', 'Sub-admin deleted!')->with('success', 'The sub-admin has been deleted successfully.');
        }
    }
}
