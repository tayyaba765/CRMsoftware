<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
         Gate::authorize('viewAny', User::class);
        $roles = Role::all()->reject(function (Role $role) {
            return $role->isProtected();
        })->sortBy('name')->values();

        return view('users.index', [
            'roles' => $roles,
        ]);
    }

    /*
    public function data()
    {
        $query = User::with('roles');

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('roles', function (User $user) {
                if ($user->roles->isEmpty()) {
                    return '—';
                }
                return $user->roles->pluck('name')->implode(', ');
            })

            ->addColumn('action', function (User $user) {
                if ($user->hasProtectedRole()) {
                    return '<div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                            Protected
                        </span>
                        <button type="button" class="badge bg-info view_btn" data-id="' . $user->id . '">View</button>
                    </div>';
                }

                return 
                    '<div class="flex items-center gap-2">
                        <button type="button"
                            class="edit_btn inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-white bg-brand hover:bg-brand/90 rounded-lg shadow-sm transition focus:outline-none focus:ring-2 focus:ring-brand/50"
                            data-id="' . $user->id . '">
                            Edit
                        </button>

                        <button type="button"
                            class="delete_btn inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition focus:outline-none focus:ring-2 focus:ring-red-500/50"
                            data-id="' . $user->id . '">
                            Delete
                        </button>
                    </div>';
            })

            ->rawColumns(['action'])
            ->make(true);
    }
*/


public function data()
{
    // Get all users with their roles
    $allUsers = User::with('roles')->get();

    // Filter users using the view permission in UserPolicy
    $visibleUsers = $allUsers->filter(function (User $user) {
        return Gate::allows('view', $user);
    });

    // Query only the users allowed by the policy
    $query = User::with('roles')
        ->whereIn('id', $visibleUsers->pluck('id'));

    return DataTables::of($query)
        ->addIndexColumn()

        // Display roles
        ->addColumn('roles', function (User $user) {
            if ($user->roles->isEmpty()) {
                return '—';
            }

            return $user->roles->pluck('name')->implode(', ');
        })

        // Display authorized actions
        ->addColumn('action', function (User $user) {

            $buttons = '<div class="flex items-center gap-2">';

            // Show View only if authorized
            if (Gate::allows('view', $user)) {
                $buttons .= '<button type="button"
                    class="badge bg-info view_btn"
                    data-id="' . $user->id . '">
                    View
                </button>';
            }

            // Protected users cannot be edited or deleted
            if ($user->hasProtectedRole()) {
                return $buttons .
                    '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                        Protected
                    </span></div>';
            }

            // Show Edit only if authorized
            if (Gate::allows('update', $user)) {
                $buttons .= '<button type="button"
                    class="edit_btn inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-white bg-brand hover:bg-brand/90 rounded-lg shadow-sm transition"
                    data-id="' . $user->id . '">
                    Edit
                </button>';
            }

            // Show Delete only if authorized
            if (Gate::allows('delete', $user)) {
                $buttons .= '<button type="button"
                    class="delete_btn inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition"
                    data-id="' . $user->id . '">
                    Delete
                </button>';
            }

            return $buttons . '</div>';
        })

        ->rawColumns(['action'])
        ->make(true);
}
    /**
     * Return single user (used by Edit button).
     */
    public function edit($id)
    {
        $user = User::with('roles')->find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        Gate::authorize('update', $user);
        if ($user->hasProtectedRole()) {
            return response()->json([
                'status' => false,
                'message' => 'Super Admin and CRM Admin users cannot be edited.',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'roles' => $user->roles->pluck('id')->values(),
        ]);
    }

    public function store(Request $request)
    {
            Gate::authorize('create', User::class);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $roles = $request->input('roles', []);

        if ($this->hasProtectedRoleIds($roles)) {
            return response()->json([
                'status' => false,
                'message' => 'Super Admin or CRM Admin roles cannot be assigned.',
            ], 403);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->roles()->sync($roles);

            return response()->json([
                'status' => true,
                'message' => 'User saved successfully',
                'data' => $user,
            ]);

        } catch (\Exception $e) {
            Log::error('USER STORE ERROR:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::with('roles')->find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }
Gate::authorize('update', $user);
        if ($user->hasProtectedRole()) {
            return response()->json([
                'status' => false,
                'message' => 'Super Admin and CRM Admin users cannot be edited.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $roles = $request->input('roles', []);

        if ($this->hasProtectedRoleIds($roles)) {
            return response()->json([
                'status' => false,
                'message' => 'Super Admin or CRM Admin roles cannot be assigned.',
            ], 403);
        }

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        $user->roles()->sync($roles);

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'data' => $user,
        ]);
    }

    /**
     * View single user (Super Admin & CRM Admin safe).
     */
    public function view($id)
    {
        $user = User::with('roles')->find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }
   try {
        Gate::authorize('view', $user);
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {

        // User is not allowed to view this user
        return response()->json([
            'status'  => false,
            'message' => 'Not authorized to view this user.',
        ], 403);
    }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->values(),
            ],
        ]);
    }

    public function destroy($id)
    {
        $user = User::with('roles')->find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }
Gate::authorize('delete', $user);
        if ($user->hasProtectedRole()) {
            return response()->json([
                'status' => false,
                'message' => 'Super Admin and CRM Admin users cannot be deleted.',
            ], 403);
        }

        $user->roles()->detach();
        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    private function hasProtectedRoleIds(array $roleIds): bool
    {
        return Role::whereIn('id', $roleIds)
            ->get()
            ->contains(function (Role $role) {
                return $role->isProtected();
            });
    }
}