<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private const PROTECTED_ROLE = 'superadmin';

    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleId = $request->query('role_id')) {
            $query->whereHas('roles', fn ($q) => $q->where('roles.id', $roleId));
        }
        $users = $query->orderBy('id', 'desc')->paginate(15);

        return response()->json([
            'status' => true,
     //   get() + map() = Working with a full collection of all records.
//paginate() + through() = Working with a chunked/paginated list of records.
            'data'   => $users->through(function (User $u) {
                return [
                    'id'    => $u->id,
                    'name'  => $u->name,
                    'email' => $u->email,
                    'roles' => $u->roles->pluck('name'),
                ];
            }),
        ]);
    }

    /* -----------------------------------------
     |  Show single user (view / edit combined)
     * ----------------------------------------- */
    public function show($id)
    {
        $user = User::with('roles')->find($id);
        if (! $user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found',
            ], 404);
        }
        return response()->json([
            'status' => true,
            'data'   => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'role_ids'   => $user->roles->pluck('id')->values(),
                'role_names' => $user->roles->pluck('name')->values(),
            ],
        ]);
    }

    /* -----------------------------------------
     |  Store a new user
     * ----------------------------------------- */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'roles'    => 'required|array|min:1',
            'roles.*'  => 'exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $roles = $request->input('roles', []);

        if ($this->hasSuperAdmin($roles)) {
            return response()->json([
                'status'  => false,
                'message' => 'Super Admin role cannot be assigned.',
            ], 403);
        }

        try {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $roleNames = $request->input('roles');
              $roleIds = Role::whereIn('name', $roleNames)
            ->pluck('id')
            ->toArray();

        // Assign roles using their IDs.
        $user->roles()->sync($roleIds);

            return response()->json([
                'status'  => true,
                'message' => 'User saved successfully',
                'data'    => $user->load('roles'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('USER STORE ERROR', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    /* -----------------------------------------
     |  Update a user
     * ----------------------------------------- */
public function update(Request $request, $id)
{
    // Find user with roles
    $user = User::with('roles')->find($id);

    // User not found
    if (! $user) {
        return response()->json([
            'status'  => false,
            'message' => 'User not found',
        ], 404);
    }

    // Existing Super Admin cannot be edited
    if ($this->isProtected($user->roles->pluck('name')->toArray())) {
        return response()->json([
            'status'  => false,
            'message' => 'Super Admin cannot be edited.',
        ], 403);
    }

    // Validate only fields that are sent
    $validator = Validator::make($request->all(), [
        'name'     => 'sometimes|string|min:2|max:255',
        'email'    => 'sometimes|email|max:255|unique:users,email,' . $user->id,
        'password' => 'sometimes|nullable|string|min:8',
        'roles'    => 'sometimes|array|min:1',
        'roles.*'  => 'exists:roles,name',
    ]);

    // Validation failed
    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    // Update name only if name was sent
    if ($request->has('name')) {
        $user->name = $request->name;
    }

    // Update email only if email was sent
    if ($request->has('email')) {
        $user->email = $request->email;
    }

    // Update password only if password was sent
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    // Save user changes
    $user->save();

    // Update roles only if roles were sent
    if ($request->has('roles')) {

        $roles = $request->roles;

        // Do not allow Super Admin
        if ($this->hasSuperAdmin($roles)) {
            return response()->json([
                'status'  => false,
                'message' => 'Super Admin role cannot be assigned.',
            ], 403);
        }

        // Convert role names to role IDs
        $roleIds = Role::whereIn('name', $roles)
            ->pluck('id')
            ->toArray();

        // Sync roles
        $user->roles()->sync($roleIds);
    }

    return response()->json([
        'status'  => true,
        'message' => 'User updated successfully',
        'data'    => $user->load('roles'),
    ]);
}
    /* -----------------------------------------
     |  Delete a user
     * ----------------------------------------- */
    public function destroy($id)
    {
        $user = User::with('roles')->find($id);

        if (! $user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found',
            ], 404);
        }

        if ($this->isProtected($user->roles->pluck('name')->toArray())) {
            return response()->json([
                'status'  => false,
                'message' => 'Super Admin cannot be deleted.',
            ], 403);
        }

        $user->roles()->detach();
        $user->delete();

        return response()->json([
            'status'  => true,
            'message' => 'User deleted successfully',
        ]);
    }

  private function hasSuperAdmin(array $roleNames): bool
{
    foreach ($roleNames as $roleName) {

        if (strtolower($roleName) == strtolower(self::PROTECTED_ROLE)) {
            return true;
        }
    }

    return false;
}
    private function isProtected(array $roleNames): bool
    {
         foreach ($roleNames as $roleName) {

        if (strtolower($roleName) == strtolower(self::PROTECTED_ROLE)) {
            return true;
        }
    }

    return false;
    }
}