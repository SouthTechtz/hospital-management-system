<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRolePermissionsRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function index()
    {
        $this->authorize('viewAny', Role::class);

        return view('admin.roles.index', [
            'roles' => Role::withCount('users')->orderBy('name')->paginate(25),
        ]);
    }

    public function show(Role $role)
    {
        $this->authorize('viewAny', Role::class);

        $permissions = Permission::orderBy('module')->orderBy('name')->get()
            ->groupBy('module');

        return view('admin.roles.show', [
            'role' => $role->load('permissions', 'users'),
            'permissionGroups' => $permissions,
        ]);
    }

    public function updatePermissions(Role $role, UpdateRolePermissionsRequest $request)
    {
        $this->authorize('update', $role);

        $before = $role->permissions()->pluck('permissions.id')->sort()->values()->all();
        $after = collect($request->input('permissions', []))->map(fn ($id) => (int) $id)->sort()->values()->all();

        $role->syncPermissionsSafely($after);

        $this->audit->log(AuditAction::PermissionChange, $role, description: 'Role permission matrix updated', oldValues: [
            'permission_ids' => $before,
        ], newValues: [
            'permission_ids' => $after,
        ], actor: $request->user());

        return redirect()->route('admin.roles.show', $role)->with('status', __('admin.permissions_updated'));
    }
}