<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $users = User::with(['roles', 'primaryRole', 'department', 'facility'])
            ->when($request->query('q'), fn ($q, $term) => $q->where(function ($inner) use ($term) {
                $inner->where('first_name', 'like', '%'.$term.'%')
                    ->orWhere('last_name', 'like', '%'.$term.'%')
                    ->orWhere('username', 'like', '%'.$term.'%');
            }))
            ->paginate(25);

        return view('admin.users.index', ['users' => $users]);
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('admin.users.create', [
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function store(CreateUserRequest $request)
    {
        $this->authorize('create', User::class);

        $user = User::create($request->safe()->except(['roles', 'password_confirmation']));

        $user->roles()->sync($request->input('roles', []));
        $user->forceFill(['primary_role_id' => $request->input('primary_role_id') ?: $request->input('roles.0')])->save();

        $this->audit->log(AuditAction::Create, $user, description: 'User account created', newValues: [
            'username' => $user->username,
            'roles' => $request->input('roles', []),
        ], actor: $request->user());

        return redirect()->route('admin.users.index')->with('status', __('admin.user_created'));
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return view('admin.users.show', [
            'user' => $user->load('roles', 'primaryRole', 'department', 'facility', 'careAssignments.patient'),
        ]);
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $before = $user->getOriginal();

        $user->fill($request->safe()->except(['roles']));
        $user->save();

        if ($request->user()->can('assignRoles', $user) && $request->has('roles')) {
            $user->roles()->sync($request->input('roles', []));
            $user->forceFill(['primary_role_id' => $request->input('primary_role_id') ?: $request->input('roles.0')])->save();
            $user->forgetPermissionCache();
        }

        $this->audit->log(AuditAction::Update, $user, description: 'User account updated', oldValues: $before, newValues: [
            'changed' => array_keys($user->getChanges()),
        ], actor: $request->user());

        return redirect()->route('admin.users.show', $user)->with('status', __('admin.user_updated'));
    }

    public function deactivate(User $user, Request $request)
    {
        $this->authorize('deactivate', $user);

        $user->forceFill([
            'is_active' => false,
            'deactivated_at' => now(),
        ])->save();

        $this->audit->log(AuditAction::Update, $user, description: 'User deactivated', actor: $request->user());

        return redirect()->back()->with('status', __('admin.user_deactivated'));
    }

    public function reactivate(User $user, Request $request)
    {
        $this->authorize('deactivate', $user);

        $user->forceFill([
            'is_active' => true,
            'deactivated_at' => null,
        ])->save();

        $this->audit->log(AuditAction::Update, $user, description: 'User reactivated', actor: $request->user());

        return redirect()->back()->with('status', __('admin.user_reactivated'));
    }
}