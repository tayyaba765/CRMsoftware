<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Gate;
class RolePermissionController extends Controller
{
    private function isProtected(Role $role): bool
    {
        return $role->isProtected();
    }

public function index()
{
    Gate::authorize('viewAny', Role::class);

    $modules = Module::orderBy('name')
        ->get(['id', 'name']);

    $roles = Role::orderBy('name')
        ->get(['id', 'name', 'parent_role_id'])
        ->filter(fn (Role $role) => Gate::allows('view', $role))
        ->values();

    return view('backend.roles.index', compact('modules', 'roles'));
}

 public function data(): JsonResponse
{
      Gate::authorize('viewAny', Role::class);
    // 1. Load all roles with their relationships
    $allRoles = Role::with([
        'module',
        'parentRole',
        'permissions'
    ])->get();

    // 2. Keep only roles the current user is allowed to view
    $visibleRoleIds = $allRoles
        ->filter(function (Role $role) {
            return Gate::allows('view', $role);
        })
        ->pluck('id');

    // 3. Query only authorized roles
    $roles = Role::with([
        'module',
        'parentRole',
        'permissions'
    ])
        ->whereIn('id', $visibleRoleIds)
        ->select('roles.*');

    // 4. Return the roles through DataTables
    return DataTables::of($roles)

        ->addIndexColumn()

        // Module column
        ->addColumn('module', function (Role $role) {
            if (!$role->module) {
                return '<span class="text-gray-400">--</span>';
            }

            $colors = [
                'school'   => 'bg-green-100 text-green-700',
                'staff'    => 'bg-blue-100 text-blue-700',
                'external' => 'bg-amber-100 text-amber-700',
            ];

            $class = $colors[strtolower($role->module->name)]
                ?? 'bg-gray-100 text-gray-700';

            return '<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold '
                . $class . '">'
                . e($role->module->name)
                . '</span>';
        })

        // Parent role column
        ->addColumn('parent', function (Role $role) {
            return $role->parentRole
                ? e($role->parentRole->name)
                : '--';
        })

        // Permissions count column
        ->addColumn('permissions_count', function (Role $role) {
            if ($this->isProtected($role)) {
                return '<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">All</span>';
            }

            return '<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-indigo-100 text-indigo-700">'
                . $role->permissions->count()
                . '</span>';
        })

        // Action buttons column
        ->addColumn('action', function (Role $role) {
            // Protected roles cannot be edited or deleted here
            if ($this->isProtected($role)) {
                return '<span class="inline-flex items-center rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 text-xs font-semibold px-2.5 py-1.5">Protected Role</span>';
            }

            $id = $role->id;
            $name = e($role->name);

            $buttons = '<div class="flex items-center gap-2">';

            // Show Permissions and Edit buttons only if authorized
            if (Gate::allows('update', $role)) {
                $buttons .= '<button type="button"
                    class="manage-perm-btn"
                    data-id="' . $id . '">Permissions</button>';

                $buttons .= '<button type="button"
                    class="edit-role-btn"
                    data-id="' . $id . '"
                    data-name="' . $name . '">Edit</button>';
            }

            // Show Delete button only if authorized
            if (Gate::allows('delete', $role)) {
                $buttons .= '<button type="button"
                    class="delete-role-btn"
                    data-id="' . $id . '"
                    data-name="' . $name . '">Delete</button>';
            }

            return $buttons . '</div>';
        })

        // Allow HTML only in these columns
        ->rawColumns([
            'module',
            'permissions_count',
            'action'
        ])

        ->make(true);
}
    public function store(Request $request): JsonResponse
    {
         Gate::authorize('create', Role::class);
        $validator = Validator::make($request->all(), [
            "name"           => "required|string|max:255|unique:roles,name",
            "module_id"      => "nullable|exists:modules,id",
            "parent_role_id" => "nullable|exists:roles,id",
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => false, "errors" => $validator->errors()], 422);
        }

        $clean = str_replace([' ', '-', '_'], '', strtolower($request->name));
        if (in_array($clean, ['superadmin', 'crmadmin'], true)) {
            return response()->json(["status" => false, "message" => "The superadmin and crmadmin role names are system-reserved."], 403);
        }

        try {
            $role = Role::create([
                "name"           => $request->name,
                "guard_name"     => "web",
                "module_id"      => $request->module_id ?: null,
                "parent_role_id" => $request->parent_role_id ?: null,
            ]);

            return response()->json(["status" => true, "message" => "Role created successfully", "data" => $role]);
        } catch (\Exception $e) {
            return response()->json(["status" => false, "message" => $e->getMessage()], 500);
        }
    }

    public function edit(int $id): JsonResponse
    {
        $role = Role::with("permissions")->findOrFail($id);

        if ($this->isProtected($role)) {
            return response()->json(["message" => "System roles (Super Admin and CRM Admin) cannot be edited."], 403);
        }
        Gate::authorize('update', $role);
        $assignedIds = $role->permissions->pluck("id")->toArray();

        $grouped = Module::orderBy("name")->get()->map(function (Module $module) use ($assignedIds) {
            $permissions = Permission::where("module_id", $module->id)->orderBy("name")->get()
                ->map(fn(Permission $p) => ["id" => $p->id, "name" => $p->name, "checked" => in_array($p->id, $assignedIds)]);
            return ["id" => $module->id, "name" => $module->name, "permissions" => $permissions];
        })->filter(fn($m) => count($m["permissions"]) > 0)->values();

        $unassigned = Permission::whereNull("module_id")->orderBy("name")->get()
            ->map(fn(Permission $p) => ["id" => $p->id, "name" => $p->name, "checked" => in_array($p->id, $assignedIds)]);

        if ($unassigned->isNotEmpty()) {
            $grouped->push(["id" => 0, "name" => "General", "permissions" => $unassigned]);
        }

        return response()->json([
            "role" => [
                "id"             => $role->id,
                "name"           => $role->name,
                "module_id"      => $role->module_id,
                "parent_role_id" => $role->parent_role_id,
            ],
            "modules" => $grouped,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        if ($this->isProtected($role)) {
            return response()->json(["message" => "System roles (Super Admin and CRM Admin) cannot be edited."], 403);
        }
         Gate::authorize('update', $role);

        $validator = Validator::make($request->all(), [
            "name"           => "sometimes|string|max:255|unique:roles,name,{$id}",
            "module_id"      => "nullable|exists:modules,id",
            "parent_role_id" => "nullable|exists:roles,id",
            "permissions"    => "array",
            "permissions.*"  => "exists:permissions,id",
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => false, "errors" => $validator->errors()], 422);
        }

        if ($request->has("name")) {
            $clean = str_replace([' ', '-', '_'], '', strtolower($request->name));
            if (in_array($clean, ['superadmin', 'crmadmin'], true)) {
                return response()->json(["status" => false, "message" => "The superadmin and crmadmin role names are system-reserved."], 403);
            }
            $role->name = $request->name;
        }

        // Only update these if they were actually sent in the request.
        if ($request->has("module_id")) {
            $role->module_id = $request->module_id ?: null;
        }

        if ($request->has("parent_role_id")) {
            $role->parent_role_id = $request->parent_role_id ?: null;
        }

        $role->save();

        if ($request->has("permissions")) {
            $permissions = Permission::whereIn("id", $request->input("permissions", []))->get();
            $role->syncPermissions($permissions);
        }

        return response()->json(["status" => true, "message" => "Role updated successfully."]);
    }

    public function destroy(int $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        if ($this->isProtected($role)) {
            return response()->json(["status" => false, "message" => "System roles (Super Admin and CRM Admin) cannot be deleted."], 403);
        }
Gate::authorize('delete', $role);
        try {
            $role->syncPermissions([]);
            $role->delete();
            return response()->json(["status" => true, "message" => "Role deleted successfully."]);
        } catch (\Exception $e) {
            return response()->json(["status" => false, "message" => $e->getMessage()], 500);
        }
    }
}