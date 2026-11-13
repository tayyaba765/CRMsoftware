<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class SimplePermissionController extends Controller
{
    public function index()
    {
        return view('backend.permissions.simple-permissions');
    }

    private function isProtected(User $user): bool
    {
        return $user->hasProtectedRole();
    }

    public function data(Request $request): JsonResponse
    {
        $users = User::with('roles')
            ->withCount('permissions')   
            ->select('users.*');         

        return DataTables::of($users)

            ->addIndexColumn()
            ->orderColumn('name',  fn ($q, $o) => $q->orderBy('name', $o))
            ->orderColumn('email', fn ($q, $o) => $q->orderBy('email', $o))

            ->filterColumn('name',  fn ($q, $k) => $q->where('name', 'like', "%{$k}%"))
            ->filterColumn('email', fn ($q, $k) => $q->where('email', 'like', "%{$k}%"))

            ->addColumn('roles', function (User $user) {
                if ($user->roles->isEmpty()) {
                    return '<span class="text-gray-400">—</span>';
                }
                return $user->roles->map(fn ($role) => '
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5
                        text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                        '.e($role->name).'
                    </span>
                ')->implode(' ');
            })

            ->addColumn('permissions_count', function (User $user) {
                if ($this->isProtected($user)) {
                    return '
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5
                            text-xs font-semibold bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                            All
                        </span>
                    ';
                }

                $count = (int) $user->permissions_count;
                $color = $count > 0 
                    ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300' 
                    : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400';

                return '
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5
                        text-xs font-semibold '.$color.'">
                        '.$count.'
                    </span>
                ';
            })

            ->addColumn('action', function (User $user) {

                if ($this->isProtected($user)) {
                    return '
                        <span class="inline-flex items-center rounded-lg bg-purple-100
                            text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 text-xs font-semibold px-2.5 py-1.5">
                            Protected Admin
                        </span>
                    ';
                }

                return '
<div class="inline-flex items-center gap-1.5">

    <button type="button"
        class="manage-perm-btn inline-flex items-center justify-center
        rounded-lg bg-brand/10 hover:bg-brand/20 text-brand dark:bg-brand/20 dark:hover:bg-brand/30 dark:text-indigo-300 p-2 transition shadow-xs"
        data-id="'.$user->id.'" title="Manage Permissions">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12.75 11.25 15 15 9.75m6.75 2.25a9.75 9.75 0 1 1-19.5 0 9.75 9.75 0 0 1 19.5 0Z" />
        </svg>
    </button>

    <button type="button"
        class="edit-user-btn inline-flex items-center justify-center
        rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 dark:text-blue-400 p-2 transition shadow-xs"
        data-id="'.$user->id.'" title="Edit User">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
        </svg>
    </button>

    <button type="button"
        class="delete-user-btn inline-flex items-center justify-center
        rounded-lg bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-400 p-2 transition shadow-xs"
        data-id="'.$user->id.'" data-name="'.e($user->name).'" title="Delete User">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
        </svg>
    </button>

</div>
                ';
            })

            ->rawColumns(['roles', 'permissions_count', 'action'])
            ->make(true);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id'  => 'nullable|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->filled('role_id')) {
            $role = Role::findOrFail($request->input('role_id'));
            if ($role->isProtected()) {
                return response()->json([
                    'message' => 'Protected roles (Super Admin / CRM Admin) cannot be assigned from this screen.'
                ], 403);
            }
        }

        $user = User::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        if ($request->filled('role_id')) {
            $role = Role::findOrFail($request->input('role_id'));
            $user->syncRoles([$role]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.'
        ]);
    }

    public function edit(int $id): JsonResponse
    {
        $user = User::with('roles')->findOrFail($id);

        if ($this->isProtected($user)) {
            return response()->json([
                'message' => 'Protected Admin users (Super Admin / CRM Admin) cannot be edited from this screen.'
            ], 403);
        }

        return response()->json([
            'user' => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'role_id' => optional($user->roles->first())->id,
            ],
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($this->isProtected($user)) {
            return response()->json([
                'message' => 'Protected Admin users (Super Admin / CRM Admin) cannot be edited from this screen.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role_id'  => 'nullable|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('role_id') && $request->filled('role_id')) {
            $role = Role::findOrFail($request->input('role_id'));
            if ($role->isProtected()) {
                return response()->json([
                    'message' => 'Protected roles (Super Admin / CRM Admin) cannot be assigned from this screen.'
                ], 403);
            }
        }

        $user->name  = $request->input('name');
        $user->email = $request->input('email');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        if ($request->has('role_id')) {
            if ($request->filled('role_id')) {
                $role = Role::findOrFail($request->input('role_id'));
                $user->syncRoles([$role]);
            } else {
                $user->syncRoles([]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.'
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($this->isProtected($user)) {
            return response()->json([
                'message' => 'Protected Admin users (Super Admin / CRM Admin) cannot be deleted.'
            ], 403);
        }

        if ($user->id === Auth::id()) {
            return response()->json([
                'message' => 'You cannot delete your own account.'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.'
        ]);
    }

    public function permissions(int $id): JsonResponse
    {
        $user = User::with(['permissions', 'roles'])->findOrFail($id);

        if ($this->isProtected($user)) {
            return response()->json([
                'message' => 'Protected Admin users already have every permission and cannot be edited.'
            ], 403);
        }

        $directIds  = $user->permissions->pluck('id')->toArray();
        $viaRoleIds = $user->getPermissionsViaRoles()->pluck('id')->toArray();

        $mapPermission = function (Permission $permission) use ($directIds, $viaRoleIds) {
            return [
                'id'       => $permission->id,
                'name'     => $permission->name,
                'checked'  => in_array($permission->id, $directIds),
                'via_role' => in_array($permission->id, $viaRoleIds),
            ];
        };

        $modules = Module::orderBy('name')->get();

        $grouped = $modules->map(function (Module $module) use ($mapPermission) {
            $permissions = Permission::where('module_id', $module->id)
                ->orderBy('name')
                ->get()
                ->map($mapPermission);

            return [
                'id'          => $module->id,
                'name'        => $module->name,
                'permissions' => $permissions,
            ];
        })->filter(fn ($module) => count($module['permissions']) > 0)->values();

        $unassigned = Permission::whereNull('module_id')
            ->orderBy('name')
            ->get()
            ->map($mapPermission);

        if ($unassigned->isNotEmpty()) {
            $grouped->push([
                'id'          => 0,
                'name'        => 'General',
                'permissions' => $unassigned,
            ]);
        }

        return response()->json([
            'user'    => [
                'id'   => $user->id,
                'name' => $user->name,
            ],
            'modules' => $grouped,
        ]);
    }

    public function savePermissions(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($this->isProtected($user)) {
            return response()->json([
                'message' => 'Protected Admin users already have every permission and cannot be edited.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'permissions'   => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $permissionIds = $request->input('permissions', []);
        $permissions   = Permission::whereIn('id', $permissionIds)->get();

        $user->syncPermissions($permissions);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'message' => 'Permissions updated successfully.'
        ]);
    }
}
