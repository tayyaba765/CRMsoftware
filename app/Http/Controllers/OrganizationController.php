<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class OrganizationController extends Controller
{
    /**
     * Display the organizations management page.
     */
    public function index()
    {
        Gate::authorize('viewAny', Organization::class);

        $users = User::orderBy('name')->get();
        $sources = ['WhatsApp', 'Phone', 'Facebook', 'Instagram', 'In-person', 'Other'];
        $statuses = [
            'prospective' => 'Prospective',
            'working'     => 'Working',
            'postponed'   => 'Postponed',
            'closed'      => 'Closed',
        ];
        $sizes = ['2-15', '15-50', '50-100', '100-500', '500+'];
        $ownershipTypes = [
            'private'    => 'Private',
            'public'     => 'Public',
            'non-profit' => 'Non Profit',
            'government' => 'Government',
        ];
        $tiers = ['Tier 1', 'Tier 2', 'Tier 3', 'Tier 4'];
        $orgTypes = [
            'Technology / IT',
            'Healthcare & Medical',
            'Finance & Banking',
            'Education & Academic',
            'Manufacturing & Industrial',
            'Real Estate & Construction',
            'Retail & E-commerce',
            'Consulting & Professional',
            'Government & Public Sector',
            'Non-Profit / NGO',
            'Other',
        ];

        return view('backend.organizations.index', compact('sizes', 'ownershipTypes', 'tiers', 'orgTypes', 'users', 'sources', 'statuses'));
    }

    /**
     * Process DataTables AJAX request.
     */
    public function data()
    {
        $query = Organization::with('assignedUser')->select('organizations.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('org_info', function (Organization $org) {
                $logoUrl = $org->logo_url;
                $name = e($org->name);
                $website = $org->website_domain ? e($org->website_domain) : '';
                $cleanWebsite = $website ? (str_starts_with($website, 'http') ? $website : 'https://' . $website) : '';

                return '
                    <div class="flex items-center gap-3">
                        <img src="' . $logoUrl . '" alt="' . $name . '" class="w-10 h-10 rounded-lg object-cover border border-gray-200 dark:border-gray-700 shadow-sm flex-shrink-0 bg-white dark:bg-gray-800">
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900 dark:text-gray-100 truncate">' . $name . '</div>
                            ' . ($website ? '<a href="' . $cleanWebsite . '" target="_blank" class="text-xs text-brand hover:underline inline-flex items-center gap-1 truncate">' . $website . ' <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg></a>' : '<span class="text-xs text-gray-400 dark:text-gray-500">No domain</span>') . '
                        </div>
                    </div>
                ';
            })
            ->orderColumn('org_info', function ($query, $order) {
                $query->orderBy('name', $order);
            })
            ->filterColumn('org_info', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('website_domain', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('source_badge', function (Organization $org) {
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
                $color = $colors[$org->source] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $color . '">' . e($org->source ?: '—') . '</span>';
            })
            ->orderColumn('source_badge', function ($query, $order) {
                $query->orderBy('source', $order);
            })
            ->filterColumn('source_badge', function ($query, $keyword) {
                $query->where('source', 'like', "%{$keyword}%");
            })
            ->addColumn('status_badge', function (Organization $org) {
                $statusColors = [
                    'prospective' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20',
                    'working'     => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20',
                    'postponed'   => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20',
                    'closed'      => 'bg-gray-500/10 text-gray-600 dark:text-gray-400 border border-gray-500/20',
                ];
                $style = $statusColors[strtolower($org->status ?: 'prospective')] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700';
                $label = ucfirst($org->status ?: 'prospective');
                return '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold ' . $style . '">' . e($label) . '</span>';
            })
            ->orderColumn('status_badge', function ($query, $order) {
                $query->orderBy('status', $order);
            })
            ->filterColumn('status_badge', function ($query, $keyword) {
                $query->where('status', 'like', "%{$keyword}%");
            })
            ->addColumn('assigned_user_badge', function (Organization $org) {
                if ($org->assignedUser) {
                    return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-brand/10 text-brand dark:bg-brand/20 dark:text-indigo-300">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        ' . e($org->assignedUser->name) . '
                    </span>';
                }
                return '<span class="text-xs text-gray-400 dark:text-gray-500 italic">Unassigned</span>';
            })
            ->orderColumn('assigned_user_badge', function ($query, $order) {
                $query->leftJoin('users as assigned_users', 'assigned_users.id', '=', 'organizations.assigned_to')
                      ->orderBy('assigned_users.name', $order);
            })
            ->filterColumn('assigned_user_badge', function ($query, $keyword) {
                $query->whereHas('assignedUser', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('size_and_ownership', function (Organization $org) {
                $ownershipColors = [
                    'private'    => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                    'public'     => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                    'non-profit' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
                    'government' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                ];
                $color = $ownershipColors[strtolower($org->ownership_type)] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';

                return '
                    <div class="space-y-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ' . $color . '">' . ucfirst(e($org->ownership_type)) . '</span>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">👥 ' . e($org->size) . ' employees</div>
                    </div>
                ';
            })
            ->orderColumn('size_and_ownership', function ($query, $order) {
                $query->orderBy('ownership_type', $order)->orderBy('size', $order);
            })
            ->filterColumn('size_and_ownership', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('ownership_type', 'like', "%{$keyword}%")
                      ->orWhere('size', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('tier_badge', function (Organization $org) {
                $tierColors = [
                    'Tier 1' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700',
                    'Tier 2' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border border-indigo-300 dark:border-indigo-700',
                    'Tier 3' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-300 dark:border-amber-700',
                    'Tier 4' => 'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-300 dark:border-gray-600',
                ];
                $style = $tierColors[$org->tier] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200';

                return '
                    <div class="space-y-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold ' . $style . '">' . e($org->tier) . '</span>
                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">' . e($org->organization_type ?: 'General') . '</div>
                    </div>
                ';
            })
            ->orderColumn('tier_badge', function ($query, $order) {
                $query->orderBy('tier', $order);
            })
            ->filterColumn('tier_badge', function ($query, $keyword) {
                $query->where('tier', 'like', "%{$keyword}%")
                      ->orWhere('organization_type', 'like', "%{$keyword}%");
            })
            ->addColumn('revenue_display', function (Organization $org) {
                if ($org->revenue !== null && $org->revenue !== '') {
                    return '<span class="font-mono text-xs font-semibold text-emerald-600 dark:text-emerald-400">$' . number_format((float) $org->revenue, 2) . '</span>';
                }
                return '<span class="text-xs text-gray-400">—</span>';
            })
            ->orderColumn('revenue_display', function ($query, $order) {
                $query->orderBy('revenue', $order);
            })
            ->filterColumn('revenue_display', function ($query, $keyword) {
                $clean = preg_replace('/[^0-9.]/', '', $keyword);
                $query->where('revenue', 'like', "%" . ($clean ?: $keyword) . "%");
            })
            ->addColumn('contact_info', function (Organization $org) {
                $html = '<div class="text-xs space-y-0.5">';
                if ($org->phone) {
                    $html .= '<div class="text-gray-800 dark:text-gray-200 font-mono">📞 ' . e($org->phone) . '</div>';
                }
                if ($org->phone_2) {
                    $html .= '<div class="text-gray-500 dark:text-gray-400 font-mono">📱 ' . e($org->phone_2) . '</div>';
                }
                if (!$org->phone && !$org->phone_2) {
                    $html .= '<span class="text-gray-400">—</span>';
                }
                $html .= '</div>';
                return $html;
            })
            ->orderColumn('contact_info', function ($query, $order) {
                $query->orderBy('phone', $order)->orderBy('phone_2', $order);
            })
            ->filterColumn('contact_info', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('phone', 'like', "%{$keyword}%")
                      ->orWhere('phone_2', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('focal_person_display', function (Organization $org) {
                if ($org->focal_person) {
                    return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-brand/10 text-brand dark:bg-brand/20 dark:text-indigo-300">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        ' . e($org->focal_person) . '
                    </span>';
                }
                return '<span class="text-xs text-gray-400 italic">Not Assigned</span>';
            })
            ->orderColumn('focal_person_display', function ($query, $order) {
                $query->orderBy('focal_person', $order);
            })
            ->filterColumn('focal_person_display', function ($query, $keyword) {
                $query->where('focal_person', 'like', "%{$keyword}%");
            })
            ->addColumn('action', function (Organization $org) {
                return '
                    <div class="flex items-center justify-center gap-1.5">
                        <button type="button"
                            class="view_org_btn inline-flex items-center justify-center p-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-brand bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition"
                            data-id="' . $org->id . '" title="View Details">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>

                        <button type="button"
                            class="edit_org_btn inline-flex items-center justify-center px-2.5 py-1.5 text-xs font-medium text-white bg-brand hover:bg-brand/90 rounded-lg shadow-sm transition"
                            data-id="' . $org->id . '">
                            Edit
                        </button>

                        <button type="button"
                            class="delete_org_btn inline-flex items-center justify-center px-2.5 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition"
                            data-id="' . $org->id . '">
                            Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['org_info', 'source_badge', 'status_badge', 'assigned_user_badge', 'size_and_ownership', 'tier_badge', 'revenue_display', 'contact_info', 'focal_person_display', 'action'])
            ->make(true);
    }

    /**
     * Store a newly created organization.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Organization::class);

        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:150',
            'size'              => 'required|string|max:50',
            'website_domain'    => 'nullable|string|max:191',
            'source'            => 'required|string|max:50',
            'logo'              => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:3072',
            'organization_type' => 'nullable|string|max:100',
            'revenue'           => 'nullable|numeric|min:0',
            'ownership_type'    => 'required|string|max:50',
            'phone'             => ['nullable', 'regex:/^[0-9]{11}$/'],
            'phone_2'           => ['nullable', 'regex:/^[0-9]{11}$/'],
            'cnic'              => ['nullable', 'regex:/^[0-9]{13}$/'],
            'address'           => 'nullable|string|max:500',
            'address_2'         => 'nullable|string|max:500',
            'latitude'          => 'nullable|numeric|between:-90,90',
            'longitude'         => 'nullable|numeric|between:-180,180',
            'tier'              => 'required|string|max:50',
            'status'            => 'required|string|max:50',
            'assigned_to'       => 'nullable|exists:users,id',
            'focal_person'      => 'nullable|string|max:150',
        ], [
            'phone.regex'       => 'The Phone number must be exactly 11 numeric digits.',
            'phone_2.regex'     => 'The Secondary Phone number must be exactly 11 numeric digits.',
            'cnic.regex'        => 'The CNIC number must be exactly 13 numeric digits.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $logoName = null;
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $logoName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destination = public_path('uploads/organizations');
                if (!File::isDirectory($destination)) {
                    File::makeDirectory($destination, 0755, true, true);
                }
                $file->move($destination, $logoName);
            }

            $org = Organization::create([
                'name'              => $request->name,
                'size'              => $request->size,
                'website_domain'    => $request->website_domain ?: null,
                'source'            => $request->source ?: 'WhatsApp',
                'logo'              => $logoName,
                'organization_type' => $request->organization_type ?: null,
                'revenue'           => $request->filled('revenue') ? $request->revenue : null,
                'ownership_type'    => $request->ownership_type,
                'phone'             => $request->phone ?: null,
                'phone_2'           => $request->phone_2 ?: null,
                'cnic'              => $request->cnic ?: null,
                'address'           => $request->address ?: null,
                'address_2'         => $request->address_2 ?: null,
                'latitude'          => $request->filled('latitude') ? $request->latitude : null,
                'longitude'         => $request->filled('longitude') ? $request->longitude : null,
                'tier'              => $request->tier ?: 'Tier 1',
                'status'            => $request->status ?: 'prospective',
                'assigned_to'       => $request->filled('assigned_to') ? $request->assigned_to : null,
                'focal_person'      => $request->focal_person ?: null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Organization created successfully.',
                'data'    => $org,
            ]);
        } catch (\Exception $e) {
            Log::error('ORGANIZATION STORE ERROR: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create organization: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show edit form data as JSON.
     */
    public function edit($id)
    {
        $org = Organization::with('assignedUser')->find($id);

        if (!$org) {
            return response()->json([
                'status'  => false,
                'message' => 'Organization not found.',
            ], 404);
        }

        Gate::authorize('update', $org);

        return response()->json([
            'status'       => true,
            'organization' => [
                'id'                => $org->id,
                'name'              => $org->name,
                'size'              => $org->size,
                'website_domain'    => $org->website_domain,
                'source'            => $org->source ?: 'WhatsApp',
                'logo_url'          => $org->logo_url,
                'organization_type' => $org->organization_type,
                'revenue'           => $org->revenue,
                'ownership_type'    => $org->ownership_type,
                'phone'             => $org->phone,
                'phone_2'           => $org->phone_2,
                'cnic'              => $org->cnic,
                'address'           => $org->address,
                'address_2'         => $org->address_2,
                'latitude'          => $org->latitude,
                'longitude'         => $org->longitude,
                'tier'              => $org->tier,
                'status'            => $org->status ?: 'prospective',
                'assigned_to'       => $org->assigned_to,
                'focal_person'      => $org->focal_person,
            ],
        ]);
    }

    /**
     * Update an organization.
     */
    public function update(Request $request, $id)
    {
        $org = Organization::find($id);

        if (!$org) {
            return response()->json([
                'status'  => false,
                'message' => 'Organization not found.',
            ], 404);
        }

        Gate::authorize('update', $org);

        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:150',
            'size'              => 'required|string|max:50',
            'website_domain'    => 'nullable|string|max:191',
            'source'            => 'required|string|max:50',
            'logo'              => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:3072',
            'organization_type' => 'nullable|string|max:100',
            'revenue'           => 'nullable|numeric|min:0',
            'ownership_type'    => 'required|string|max:50',
            'phone'             => ['nullable', 'regex:/^[0-9]{11}$/'],
            'phone_2'           => ['nullable', 'regex:/^[0-9]{11}$/'],
            'cnic'              => ['nullable', 'regex:/^[0-9]{13}$/'],
            'address'           => 'nullable|string|max:500',
            'address_2'         => 'nullable|string|max:500',
            'latitude'          => 'nullable|numeric|between:-90,90',
            'longitude'         => 'nullable|numeric|between:-180,180',
            'tier'              => 'required|string|max:50',
            'status'            => 'required|string|max:50',
            'assigned_to'       => 'nullable|exists:users,id',
            'focal_person'      => 'nullable|string|max:150',
        ], [
            'phone.regex'       => 'The Phone number must be exactly 11 numeric digits.',
            'phone_2.regex'     => 'The Secondary Phone number must be exactly 11 numeric digits.',
            'cnic.regex'        => 'The CNIC number must be exactly 13 numeric digits.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            if ($request->hasFile('logo')) {
                if ($org->logo && File::exists(public_path('uploads/organizations/' . $org->logo))) {
                    File::delete(public_path('uploads/organizations/' . $org->logo));
                }

                $file = $request->file('logo');
                $logoName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destination = public_path('uploads/organizations');
                if (!File::isDirectory($destination)) {
                    File::makeDirectory($destination, 0755, true, true);
                }
                $file->move($destination, $logoName);
                $org->logo = $logoName;
            }

            $org->name              = $request->name;
            $org->size              = $request->size;
            $org->website_domain    = $request->website_domain ?: null;
            $org->source            = $request->source ?: 'WhatsApp';
            $org->organization_type = $request->organization_type ?: null;
            $org->revenue           = $request->filled('revenue') ? $request->revenue : null;
            $org->ownership_type    = $request->ownership_type;
            $org->phone             = $request->phone ?: null;
            $org->phone_2           = $request->phone_2 ?: null;
            $org->cnic              = $request->cnic ?: null;
            $org->address           = $request->address ?: null;
            $org->address_2         = $request->address_2 ?: null;
            $org->latitude          = $request->filled('latitude') ? $request->latitude : null;
            $org->longitude         = $request->filled('longitude') ? $request->longitude : null;
            $org->tier              = $request->tier ?: 'Tier 1';
            $org->status            = $request->status ?: 'prospective';
            $org->assigned_to       = $request->filled('assigned_to') ? $request->assigned_to : null;
            $org->focal_person      = $request->focal_person ?: null;

            $org->save();

            return response()->json([
                'status'  => true,
                'message' => 'Organization updated successfully.',
                'data'    => $org,
            ]);
        } catch (\Exception $e) {
            Log::error('ORGANIZATION UPDATE ERROR: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update organization: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show single organization details.
     */
    public function show($id)
    {
        $org = Organization::with('assignedUser')->find($id);

        if (!$org) {
            return response()->json([
                'status'  => false,
                'message' => 'Organization not found.',
            ], 404);
        }

        try {
            Gate::authorize('view', $org);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Not authorized to view this organization.',
            ], 403);
        }

        $cnicFormatted = '—';
        if ($org->cnic) {
            $c = $org->cnic;
            $cnicFormatted = strlen($c) === 13 ? substr($c, 0, 5) . '-' . substr($c, 5, 7) . '-' . substr($c, 12, 1) : $c;
        }

        return response()->json([
            'status'       => true,
            'organization' => [
                'id'                => $org->id,
                'name'              => $org->name,
                'size'              => $org->size,
                'website_domain'    => $org->website_domain,
                'source'            => $org->source ?: 'WhatsApp',
                'logo_url'          => $org->logo_url,
                'organization_type' => $org->organization_type ?: 'General',
                'revenue_formatted' => $org->formatted_revenue,
                'ownership_type'    => ucfirst($org->ownership_type),
                'phone'             => $org->phone,
                'phone_2'           => $org->phone_2,
                'cnic'              => $org->cnic,
                'cnic_formatted'    => $cnicFormatted,
                'address'           => $org->address,
                'address_2'         => $org->address_2,
                'latitude'          => $org->latitude,
                'longitude'         => $org->longitude,
                'tier'              => $org->tier,
                'status'            => ucfirst($org->status ?: 'prospective'),
                'assigned_user'     => $org->assignedUser ? $org->assignedUser->name : 'Unassigned',
                'focal_person'      => $org->focal_person ?: 'Unassigned',
                'created_at'        => $org->created_at ? $org->created_at->format('M d, Y h:i A') : '—',
            ],
        ]);
    }

    /**
     * Remove an organization.
     */
    public function destroy($id)
    {
        $org = Organization::find($id);

        if (!$org) {
            return response()->json([
                'status'  => false,
                'message' => 'Organization not found.',
            ], 404);
        }

        Gate::authorize('delete', $org);

        try {
            if ($org->logo && File::exists(public_path('uploads/organizations/' . $org->logo))) {
                File::delete(public_path('uploads/organizations/' . $org->logo));
            }

            $org->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Organization deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('ORGANIZATION DESTROY ERROR: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete organization: ' . $e->getMessage(),
            ], 500);
        }
    }
}
