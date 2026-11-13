<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
            Gate::authorize('viewAny', Profile::class);
        $users = User::orderBy('name')->get();
        $sources = ['WhatsApp', 'Phone', 'Facebook', 'Instagram', 'In-person', 'Other'];
        $statuses = [
            'prospective' => 'Prospective',
            'working'     => 'Working',
            'postponed'   => 'Postponed',
            'closed'      => 'Closed',
        ];
        $jobTypes = [
            'private'     => 'Private',
            'government'  => 'Government',
            'unemployed'  => 'Unemployed',
            'public'      => 'Public',
        ];
        $educations = [
            'highschool' => 'High School',
            'bs'         => 'BS / Bachelor\'s',
            'masters'    => 'Master\'s',
            'phd'        => 'PhD / Doctorate',
            'other'      => 'Other',
        ];
        $ageGroups = [
            'teen'       => 'Teen (13-19)',
            'adult'      => 'Adult (20-39)',
            'olderadult' => 'Older Adult (40-59)',
            'senior'     => 'Senior (60+)',
        ];

        return view('backend.profiles.index', compact('users', 'sources', 'statuses', 'jobTypes', 'educations', 'ageGroups'));
    }

    /**
     * Process DataTables AJAX request.
     */
    public function data()
    {
        $query = Profile::with('assignedUser')->select('profiles.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('profile_info', function (Profile $profile) {
                $avatarUrl = $profile->avatar_url;
                $fullName = e($profile->full_name);
                $username = $profile->username ? '@' . e($profile->username) : '';

                return '
                    <div class="flex items-center gap-3">
                        <img src="' . $avatarUrl . '" alt="' . $fullName . '" class="w-10 h-10 rounded-full object-cover border border-gray-200 dark:border-gray-700 shadow-sm flex-shrink-0">
                        <div class="min-w-0">
                            <div class="font-medium text-gray-900 dark:text-gray-100 truncate">' . $fullName . '</div>
                            ' . ($username ? '<div class="text-xs text-gray-400 dark:text-gray-500 truncate">' . $username . '</div>' : '') . '
                        </div>
                    </div>
                ';
            })
            ->orderColumn('profile_info', function ($query, $order) {
                $query->orderBy('first_name', $order)->orderBy('last_name', $order);
            })
            ->filterColumn('profile_info', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('first_name', 'like', "%{$keyword}%")
                      ->orWhere('last_name', 'like', "%{$keyword}%")
                      ->orWhere('username', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('contact_info', function (Profile $profile) {
                $html = '<div class="text-xs space-y-0.5">';
                if ($profile->email) {
                    $html .= '<div class="text-gray-700 dark:text-gray-300 truncate" title="' . e($profile->email) . '">' . e($profile->email) . '</div>';
                }
                if ($profile->phone) {
                    $html .= '<div class="text-gray-500 dark:text-gray-400 font-mono">' . e($profile->phone) . '</div>';
                }
                if (!$profile->email && !$profile->phone) {
                    $html .= '<span class="text-gray-400">—</span>';
                }
                $html .= '</div>';
                return $html;
            })
            ->orderColumn('contact_info', function ($query, $order) {
                $query->orderBy('email', $order)->orderBy('phone', $order);
            })
            ->filterColumn('contact_info', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('email', 'like', "%{$keyword}%")
                      ->orWhere('phone', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('cnic_formatted', function (Profile $profile) {
                if (!$profile->cnic) {
                    return '<span class="text-gray-400 text-xs">—</span>';
                }
                $cnic = $profile->cnic;
                if (strlen($cnic) === 13) {
                    $formatted = substr($cnic, 0, 5) . '-' . substr($cnic, 5, 7) . '-' . substr($cnic, 12, 1);
                    return '<span class="text-xs font-mono font-medium text-gray-700 dark:text-gray-300">' . $formatted . '</span>';
                }
                return '<span class="text-xs font-mono text-gray-700 dark:text-gray-300">' . e($cnic) . '</span>';
            })
            ->orderColumn('cnic_formatted', function ($query, $order) {
                $query->orderBy('cnic', $order);
            })
            ->filterColumn('cnic_formatted', function ($query, $keyword) {
                $clean = preg_replace('/[^0-9]/', '', $keyword);
                $query->where('cnic', 'like', "%" . ($clean ?: $keyword) . "%");
            })
            ->addColumn('source_badge', function (Profile $profile) {
                $colors = [
                    'WhatsApp'  => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                    'Phone'     => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                    'Facebook'  => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300',
                    'Instagram' => 'bg-pink-100 text-pink-800 dark:bg-pink-900/40 dark:text-pink-300',
                    'In-person' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                    'LinkedIn'  => 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300',
                    'Website'   => 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300',
                    'Email'     => 'bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-300',
                    'Other'     => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                ];
                $color = $colors[$profile->source] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $color . '">' . e($profile->source ?: '—') . '</span>';
            })
            ->orderColumn('source_badge', function ($query, $order) {
                $query->orderBy('source', $order);
            })
            ->filterColumn('source_badge', function ($query, $keyword) {
                $query->where('source', 'like', "%{$keyword}%");
            })
            ->addColumn('status_badge', function (Profile $profile) {
                $statusColors = [
                    'prospective' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20',
                    'working'     => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20',
                    'postponed'   => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20',
                    'closed'      => 'bg-gray-500/10 text-gray-600 dark:text-gray-400 border border-gray-500/20',
                ];
                $style = $statusColors[strtolower($profile->status)] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700';
                $label = ucfirst($profile->status);
                return '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold ' . $style . '">' . e($label) . '</span>';
            })
            ->orderColumn('status_badge', function ($query, $order) {
                $query->orderBy('status', $order);
            })
            ->filterColumn('status_badge', function ($query, $keyword) {
                $query->where('status', 'like', "%{$keyword}%");
            })
            ->addColumn('assigned_user_badge', function (Profile $profile) {
                if ($profile->assignedUser) {
                    return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-brand/10 text-brand dark:bg-brand/20 dark:text-indigo-300">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        ' . e($profile->assignedUser->name) . '
                    </span>';
                }
                return '<span class="text-xs text-gray-400 dark:text-gray-500 italic">Unassigned</span>';
            })
            ->orderColumn('assigned_user_badge', function ($query, $order) {
                $query->leftJoin('users as assigned_users', 'assigned_users.id', '=', 'profiles.assigned_to')
                      ->orderBy('assigned_users.name', $order);
            })
            ->filterColumn('assigned_user_badge', function ($query, $keyword) {
                $query->whereHas('assignedUser', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('action', function (Profile $profile) {
                return '
                    <div class="flex items-center gap-1.5">
                        <button type="button"
                            class="view_btn inline-flex items-center justify-center p-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-brand bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition"
                            data-id="' . $profile->id . '" title="View Details & Map">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>

                        <button type="button"
                            class="edit_btn inline-flex items-center justify-center px-2.5 py-1.5 text-xs font-medium text-white bg-brand hover:bg-brand/90 rounded-lg shadow-sm transition"
                            data-id="' . $profile->id . '">
                            Edit
                        </button>

                        <button type="button"
                            class="delete_btn inline-flex items-center justify-center px-2.5 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition"
                            data-id="' . $profile->id . '">
                            Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['profile_info', 'contact_info', 'cnic_formatted', 'source_badge', 'status_badge', 'assigned_user_badge', 'action'])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
           Gate::authorize('create', Profile::class);
        $validator = Validator::make($request->all(), [
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'required|string|max:100',
            'username'    => 'nullable|string|max:100|unique:profiles,username',
            'source'      => 'required|string|max:50',
            'email'       => 'nullable|email|max:191|unique:profiles,email',
            'password'    => 'nullable|string|min:6',
            'avatar'      => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'cnic'        => ['nullable', 'regex:/^[0-9]{13}$/'],
            'phone'       => ['nullable', 'regex:/^[0-9]{11}$/'],
            'address_1'   => 'nullable|string|max:500',
            'address_2'   => 'nullable|string|max:500',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'status'      => 'required|string|max:50',
            'assigned_to' => 'nullable|exists:users,id',
            'job_type'    => 'nullable|string|max:50',
            'income'      => 'nullable|numeric|min:0',
            'education'   => 'nullable|string|max:50',
            'age_group'   => 'nullable|string|max:50',
            'facebook_link'  => 'nullable|string|max:255',
            'instagram_link' => 'nullable|string|max:255',
            'linkedin_link'  => 'nullable|string|max:255',
            'threads_link'   => 'nullable|string|max:255',
        ], [
            'cnic.regex'  => 'The CNIC number must be exactly 13 numeric digits.',
            'phone.regex' => 'The Phone number must be exactly 11 numeric digits.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $avatarName = null;
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $avatarName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destination = public_path('uploads/avatars');
                if (!File::isDirectory($destination)) {
                    File::makeDirectory($destination, 0755, true, true);
                }
                $file->move($destination, $avatarName);
            }

            $profile = Profile::create([
                'first_name'  => $request->first_name,
                'last_name'   => $request->last_name,
                'username'    => $request->username ?: null,
                'source'      => $request->source,
                'email'       => $request->email ?: null,
                'password'    => $request->filled('password') ? Hash::make($request->password) : null,
                'avatar'      => $avatarName,
                'cnic'        => $request->cnic ?: null,
                'phone'       => $request->phone ?: null,
                'address_1'   => $request->address_1 ?: null,
                'address_2'   => $request->address_2 ?: null,
                'latitude'    => $request->filled('latitude') ? $request->latitude : null,
                'longitude'   => $request->filled('longitude') ? $request->longitude : null,
                'status'      => $request->status ?: 'prospective',
                'assigned_to' => $request->filled('assigned_to') ? $request->assigned_to : null,
                'job_type'    => $request->job_type ?: null,
                'income'      => $request->filled('income') ? $request->income : null,
                'education'   => $request->education ?: null,
                'age_group'   => $request->age_group ?: null,
                'facebook_link'  => $request->facebook_link ?: null,
                'instagram_link' => $request->instagram_link ?: null,
                'linkedin_link'  => $request->linkedin_link ?: null,
                'threads_link'   => $request->threads_link ?: null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Profile created successfully.',
                'data'    => $profile,
            ]);
        } catch (\Exception $e) {
            Log::error('PROFILE STORE ERROR:', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to save profile: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $profile = Profile::with('assignedUser')->find($id);

        if (!$profile) {
            return response()->json([
                'status'  => false,
                'message' => 'Profile not found.',
            ], 404);
        }
Gate::authorize('update', $profile);
        return response()->json([
            'status'  => true,
            'profile' => [
                'id'          => $profile->id,
                'first_name'  => $profile->first_name,
                'last_name'   => $profile->last_name,
                'username'    => $profile->username,
                'source'      => $profile->source,
                'email'       => $profile->email,
                'avatar_url'  => $profile->avatar_url,
                'cnic'        => $profile->cnic,
                'phone'       => $profile->phone,
                'address_1'   => $profile->address_1,
                'address_2'   => $profile->address_2,
                'latitude'    => $profile->latitude,
                'longitude'   => $profile->longitude,
                'status'      => $profile->status,
                'assigned_to' => $profile->assigned_to,
                'job_type'    => $profile->job_type,
                'income'      => $profile->income,
                'education'   => $profile->education,
                'age_group'   => $profile->age_group,
                'facebook_link'  => $profile->facebook_link,
                'instagram_link' => $profile->instagram_link,
                'linkedin_link'  => $profile->linkedin_link,
                'threads_link'   => $profile->threads_link,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $profile = Profile::find($id);

        if (!$profile) {
            return response()->json([
                'status'  => false,
                'message' => 'Profile not found.',
            ], 404);
        }
Gate::authorize('update', $profile);
        $validator = Validator::make($request->all(), [
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'required|string|max:100',
            'username'    => 'nullable|string|max:100|unique:profiles,username,' . $profile->id,
            'source'      => 'required|string|max:50',
            'email'       => 'nullable|email|max:191|unique:profiles,email,' . $profile->id,
            'password'    => 'nullable|string|min:6',
            'avatar'      => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'cnic'        => ['nullable', 'regex:/^[0-9]{13}$/'],
            'phone'       => ['nullable', 'regex:/^[0-9]{11}$/'],
            'address_1'   => 'nullable|string|max:500',
            'address_2'   => 'nullable|string|max:500',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'status'      => 'required|string|max:50',
            'assigned_to' => 'nullable|exists:users,id',
            'job_type'    => 'nullable|string|max:50',
            'income'      => 'nullable|numeric|min:0',
            'education'   => 'nullable|string|max:50',
            'age_group'   => 'nullable|string|max:50',
            'facebook_link'  => 'nullable|string|max:255',
            'instagram_link' => 'nullable|string|max:255',
            'linkedin_link'  => 'nullable|string|max:255',
            'threads_link'   => 'nullable|string|max:255',
        ], [
            'cnic.regex'  => 'The CNIC number must be exactly 13 numeric digits.',
            'phone.regex' => 'The Phone number must be exactly 11 numeric digits.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            if ($request->hasFile('avatar')) {
                // Delete previous avatar file if exists
                if ($profile->avatar && File::exists(public_path('uploads/avatars/' . $profile->avatar))) {
                    File::delete(public_path('uploads/avatars/' . $profile->avatar));
                }

                $file = $request->file('avatar');
                $avatarName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destination = public_path('uploads/avatars');
                if (!File::isDirectory($destination)) {
                    File::makeDirectory($destination, 0755, true, true);
                }
                $file->move($destination, $avatarName);
                $profile->avatar = $avatarName;
            }

            $profile->first_name  = $request->first_name;
            $profile->last_name   = $request->last_name;
            $profile->username    = $request->username ?: null;
            $profile->source      = $request->source;
            $profile->email       = $request->email ?: null;
            $profile->cnic        = $request->cnic ?: null;
            $profile->phone       = $request->phone ?: null;
            $profile->address_1   = $request->address_1 ?: null;
            $profile->address_2   = $request->address_2 ?: null;
            $profile->latitude    = $request->filled('latitude') ? $request->latitude : null;
            $profile->longitude   = $request->filled('longitude') ? $request->longitude : null;
            $profile->status      = $request->status ?: 'prospective';
            $profile->assigned_to = $request->filled('assigned_to') ? $request->assigned_to : null;
            $profile->job_type    = $request->job_type ?: null;
            $profile->income      = $request->filled('income') ? $request->income : null;
            $profile->education   = $request->education ?: null;
            $profile->age_group   = $request->age_group ?: null;
            $profile->facebook_link  = $request->facebook_link ?: null;
            $profile->instagram_link = $request->instagram_link ?: null;
            $profile->linkedin_link  = $request->linkedin_link ?: null;
            $profile->threads_link   = $request->threads_link ?: null;

            if ($request->filled('password')) {
                $profile->password = Hash::make($request->password);
            }

            $profile->save();

            return response()->json([
                'status'  => true,
                'message' => 'Profile updated successfully.',
                'data'    => $profile,
            ]);
        } catch (\Exception $e) {
            Log::error('PROFILE UPDATE ERROR:', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update profile: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * View single profile details (for view modal / map inspect).
     */
    public function show($id)
    {
        $profile = Profile::with('assignedUser')->find($id);

        if (!$profile) {
            return response()->json([
                'status'  => false,
                'message' => 'Profile not found.',
            ], 404);
        }
    try {
        Gate::authorize('view', $profile);
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {

        // User is not allowed to view this profile
        return response()->json([
            'status'  => false,
            'message' => 'Not authorized to view this profile.',
        ], 403);
    }

        return response()->json([
            'status'  => true,
            'profile' => [
                'id'            => $profile->id,
                'first_name'    => $profile->first_name,
                'last_name'     => $profile->last_name,
                'full_name'     => $profile->full_name,
                'username'      => $profile->username,
                'source'        => $profile->source,
                'email'         => $profile->email,
                'avatar_url'    => $profile->avatar_url,
                'cnic'          => $profile->cnic,
                'phone'         => $profile->phone,
                'address_1'     => $profile->address_1,
                'address_2'     => $profile->address_2,
                'latitude'      => $profile->latitude,
                'longitude'     => $profile->longitude,
                'status'        => $profile->status,
                'assigned_user' => $profile->assignedUser ? $profile->assignedUser->name : 'Unassigned',
                'job_type'      => $profile->job_type ? ucfirst($profile->job_type) : '—',
                'income'        => $profile->income !== null ? '$' . number_format((float) $profile->income, 2) : '—',
                'education'     => $profile->education ? strtoupper($profile->education) : '—',
                'age_group'     => $profile->age_group ? ucfirst($profile->age_group) : '—',
                'facebook_link'  => $profile->facebook_link,
                'instagram_link' => $profile->instagram_link,
                'linkedin_link'  => $profile->linkedin_link,
                'threads_link'   => $profile->threads_link,
                'created_at'    => $profile->created_at ? $profile->created_at->format('M d, Y h:i A') : '—',
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $profile = Profile::find($id);

        if (!$profile) {
            return response()->json([
                'status'  => false,
                'message' => 'Profile not found.',
            ], 404);
        }
 Gate::authorize('delete', $profile);
        try {

        //DELETE AVATAR
            if ($profile->avatar && File::exists(public_path('uploads/avatars/' . $profile->avatar))) {
                File::delete(public_path('uploads/avatars/' . $profile->avatar));
            }
//DELETE PROFILE
            $profile->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Profile deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('PROFILE DESTROY ERROR:', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete profile: ' . $e->getMessage(),
            ], 500);
        }
    }
}
