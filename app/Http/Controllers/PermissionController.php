<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\PermissionRegistrar;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index()
    {
        $modules = Module::orderBy('name')->get();
        return view('backend.permissions.index', compact('modules'));
    }

    /**
     * Process DataTables AJAX request for permissions.
     */
    public function data(Request $request): JsonResponse
    {
        $query = Permission::with('module')->select('permissions.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->orderColumn('name', fn ($q, $o) => $q->orderBy('name', $o))
            ->filterColumn('name', fn ($q, $k) => $q->where('name', 'like', "%{$k}%"))
            ->addColumn('permission_name', function (Permission $permission) {
                return '
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 font-mono text-xs font-semibold px-2.5 py-1 rounded-md bg-gray-100 dark:bg-gray-700/80 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-600">
                            <svg class="w-3.5 h-3.5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            '.e($permission->name).'
                        </span>
                    </div>
                ';
            })
            ->addColumn('module_name', function (Permission $permission) {
                if ($permission->module) {
                    return '
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand/10 text-brand dark:bg-brand/20 dark:text-indigo-300 border border-brand/20">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            '.e($permission->module->name).'
                        </span>
                    ';
                }
                return '<span class="text-xs text-gray-400 italic">— General / Unassigned —</span>';
            })
            ->orderColumn('module_name', function ($q, $o) {
                $q->leftJoin('modules', 'modules.id', '=', 'permissions.module_id')
                  ->orderBy('modules.name', $o);
            })
            ->filterColumn('module_name', function ($q, $k) {
                $q->whereHas('module', function ($m) use ($k) {
                    $m->where('name', 'like', "%{$k}%");
                });
            })
            ->addColumn('guard_name_badge', function (Permission $permission) {
                return '
                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-mono">
                        '.e($permission->guard_name ?: 'web').'
                    </span>
                ';
            })
            ->addColumn('created_date', function (Permission $permission) {
                return '<span class="text-xs text-gray-500 dark:text-gray-400">'.$permission->created_at?->format('M d, Y').'</span>';
            })
            ->addColumn('action', function (Permission $permission) {
                return '
                    <div class="inline-flex items-center justify-center gap-1.5">
                        <button type="button"
                            class="edit-perm-btn inline-flex items-center justify-center rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 dark:text-blue-400 p-2 transition shadow-xs"
                            data-id="'.$permission->id.'" title="Edit Permission">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                            </svg>
                        </button>
                        <button type="button"
                            class="delete-perm-btn inline-flex items-center justify-center rounded-lg bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-400 p-2 transition shadow-xs"
                            data-id="'.$permission->id.'" data-name="'.e($permission->name).'" title="Delete Permission">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['permission_name', 'module_name', 'guard_name_badge', 'created_date', 'action'])
            ->make(true);
    }

    /**
     * Store a newly created permission under a module.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:191|unique:permissions,name',
            'module_id' => 'required|exists:modules,id',
        ], [
            'name.required'      => 'The permission name is required.',
            'name.unique'        => 'This permission name already exists.',
            'module_id.required' => 'Please select a module category for this permission.',
            'module_id.exists'   => 'The selected module is invalid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $permission = Permission::create([
            'name'       => trim($request->input('name')),
            'module_id'  => $request->input('module_id'),
            'guard_name' => 'web',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json([
            'status'     => true,
            'message'    => "Permission '{$permission->name}' created successfully.",
            'permission' => $permission,
        ]);
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(int $id): JsonResponse
    {
        $permission = Permission::with('module')->findOrFail($id);

        return response()->json([
            'status'     => true,
            'permission' => $permission,
        ]);
    }

    /**
     * Update the specified permission in storage (edit name and change module).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'      => ['required', 'string', 'max:191', Rule::unique('permissions', 'name')->ignore($permission->id)],
            'module_id' => 'required|exists:modules,id',
        ], [
            'name.required'      => 'The permission name is required.',
            'name.unique'        => 'This permission name already exists.',
            'module_id.required' => 'Please select a module category for this permission.',
            'module_id.exists'   => 'The selected module is invalid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $permission->update([
            'name'      => trim($request->input('name')),
            'module_id' => $request->input('module_id'),
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json([
            'status'     => true,
            'message'    => "Permission '{$permission->name}' updated successfully.",
            'permission' => $permission,
        ]);
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);
        $name = $permission->name;

        $permission->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json([
            'status'  => true,
            'message' => "Permission '{$name}' deleted successfully.",
        ]);
    }
}
