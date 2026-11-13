<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReqresUser;
use App\Services\ReqresService;
use Illuminate\Http\Request;

class ReqresController extends Controller
{
    protected ReqresService $service;

    public function __construct(ReqresService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/reqres/users?page=1&per_page=6
     * Fetch users live from the ReqRes API (no DB write).
     */
    public function index(Request $request)
    {
        $page    = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 6);

        $result = $this->service->fetchUsers($page, $perPage);

        return response()->json([
            'status' => true,
            'page'   => $result['meta']['page'] ?? 1,
            'meta'   => $result['meta'],
            'data'   => collect($result['data'])->map(function ($user) {
                return [
                    'id'         => $user['id'],
                    'email'      => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name'  => $user['last_name'],
                    'name'       => "{$user['first_name']} {$user['last_name']}",
                    'avatar'     => $user['avatar'],
                ];
            })->toArray(),
        ]);
    }

    /**
     * POST /api/reqres/users/sync
     * Fetch users from API and SAVE them to the database.
     */
    public function sync(Request $request)
    {
        $page    = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 6);

        $result = $this->service->fetchUsers($page, $perPage);

        if (empty($result['data'])) {
            return response()->json([
                'status'  => false,
                'message' => 'No users fetched from ReqRes API.',
            ], 404);
        }

        $saved = $this->service->saveUsers($result['data']);

        return response()->json([
            'status'  => true,
            'message' => "{$saved} users saved to the database.",
            'saved'   => $saved,
        ]);
    }

    /**
     * GET /api/reqres/saved
     * List users that have ALREADY been saved to the database.
     */
    public function saved()
    {
        $users = ReqresUser::orderBy('reqres_id')->paginate(6);

        return response()->json([
            'status' => true,
            'total'  => $users->total(),
            'page'   => $users->currentPage(),
            'data'   => collect($users->items())->map(function ($user) {
                return [
                    'id'         => $user->reqres_id,
                    'email'      => $user->email,
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                    'name'       => $user->full_name,
                    'avatar'     => $user->avatar,
                ];
            })->toArray(),
        ]);
    }
}