<?php

namespace App\Services;

use App\Models\ReqresUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReqresService
{
    protected string $baseUrl;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.reqres.base_url');
        $this->apiKey  = config('services.reqres.key');
    }

    /**
     * Fetch a paginated list of users from ReqRes API.
     */
    public function fetchUsers(int $page = 1, int $perPage = 6): array
    {
        $headers = ['Accept' => 'application/json'];

        if ($this->apiKey) {
            $headers['x-api-key'] = $this->apiKey;
        }

        $response = Http::timeout(15)
            ->withHeaders($headers)
            ->get("{$this->baseUrl}/users", [
                'page'     => $page,
                'per_page' => $perPage,
            ]);

        if (!$response->successful()) {
            Log::error('ReqRes API failed', ['status' => $response->status()]);
            return ['data' => [], 'meta' => []];
        }

        $json = $response->json();

        return [
            'data' => $json['data'] ?? [],
            'meta' => [
                'page'        => $json['page'] ?? 1,
                'per_page'    => $json['per_page'] ?? $perPage,
                'total'       => $json['total'] ?? 0,
                'total_pages' => $json['total_pages'] ?? 1,
            ],
        ];
    }

    /**
     * Save ReqRes users into the database.
     */
 
/**
 * Save or update ReqRes users in the database.
 */
public function saveUsers(array $users): int
{
    $count = 0;

    foreach ($users as $user) {

        // Get the user's full name
        $name = $user['first_name'] . ' ' . $user['last_name'];

        // Check whether email, first name, last name, and full name exist
        $existingUser = ReqresUser::where('email', $user['email'])
            ->where('first_name', $user['first_name'])
            ->where('last_name', $user['last_name'])
            ->first();

        if ($existingUser) {

            // User exists: update the existing record
            $existingUser->update([
                'reqres_id' => $user['id'],
                'email'     => $user['email'],
                'first_name'=> $user['first_name'],
                'last_name' => $user['last_name'],
                'avatar'    => $user['avatar'] ?? null,
            ]);

        } else {

            // User does not match all four fields: create a record
            ReqresUser::create([
                'reqres_id'  => $user['id'],
                'email'      => $user['email'],
                'first_name' => $user['first_name'],
                'last_name'  => $user['last_name'],
                'avatar'     => $user['avatar'] ?? null,
            ]);
        }

        $count++;
    }

    return $count;
}

}