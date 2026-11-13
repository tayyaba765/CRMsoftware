<?php

namespace App\Http\Controllers;

use App\Models\PdfScrapedRecord;
use App\Models\ScrapedRecord;
use App\Models\ScraperLog;
use App\Services\ScraperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ScraperController extends Controller
{
    public function __construct(
        protected ScraperService $scraperService
    ) {}

    /**
     * Find record from either PdfScrapedRecord or ScrapedRecord.
     */
    protected function findAnyRecord($id)
    {
        return PdfScrapedRecord::find($id) ?: ScrapedRecord::find($id);
    }

    /**
     * Display scraper page.
     */
    public function index()
    {
        $sources = ScrapedRecord::distinct()->whereNotNull('source_name')->pluck('source_name')->values()->all();
        $statuses = [
            'new'        => 'New / Unprocessed',
            'contacted'  => 'Contacted',
            'qualified'  => 'Qualified',
            'converted'  => 'Converted to CRM',
            'archived'   => 'Archived',
        ];

        $stats = [
            'total'     => ScrapedRecord::count(),
            'new'       => ScrapedRecord::where('status', 'new')->count(),
            'converted' => ScrapedRecord::where('status', 'converted')->count(),
            'sources'   => count($sources),
        ];

        return view('backend.scraper.index', compact('sources', 'statuses', 'stats'));
    }

    /**
     * DataTables AJAX data.
     */
    public function data(Request $request)
    {
        $query = ScrapedRecord::select('scraped_records.*');

        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }

        if ($request->filled('source_filter')) {
            $query->where('source_name', $request->source_filter);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('checkbox', function (ScrapedRecord $record) {
                return '<input type="checkbox" class="record-checkbox rounded border-gray-300 text-brand focus:ring-brand" value="' . $record->id . '">';
            })
            ->addColumn('business_info', function (ScrapedRecord $record) {
                $name = e($record->business_name ?: '—');
                $category = $record->category ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">' . e($record->category) . '</span>' : '';
                $web = $record->website_domain ? '<div class="text-xs text-brand truncate"><a href="' . (str_starts_with($record->website_domain, 'http') ? e($record->website_domain) : 'https://' . e($record->website_domain)) . '" target="_blank" class="hover:underline">' . e($record->website_domain) . '</a></div>' : '';

                return '
                    <div class="space-y-1">
                        <div class="font-semibold text-gray-900 dark:text-gray-100">' . $name . '</div>
                        ' . ($category ? '<div>' . $category . '</div>' : '') . '
                        ' . $web . '
                    </div>
                ';
            })
            ->orderColumn('business_info', function ($query, $order) {
                $query->orderBy('business_name', $order);
            })
            ->filterColumn('business_info', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('business_name', 'like', "%{$keyword}%")
                      ->orWhere('category', 'like', "%{$keyword}%")
                      ->orWhere('website_domain', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('contact_person_display', function (ScrapedRecord $record) {
                if ($record->contact_person) {
                    return '<div class="font-medium text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        ' . e($record->contact_person) . '
                    </div>';
                }
                return '<span class="text-xs text-gray-400 italic">No contact person</span>';
            })
            ->orderColumn('contact_person_display', function ($query, $order) {
                $query->orderBy('contact_person', $order);
            })
            ->filterColumn('contact_person_display', function ($query, $keyword) {
                $query->where('contact_person', 'like', "%{$keyword}%");
            })
            ->addColumn('contact_channels', function (ScrapedRecord $record) {
                $html = '<div class="text-xs space-y-1 font-mono">';
                if ($record->phone) {
                    $html .= '<div class="text-gray-800 dark:text-gray-200">📞 ' . e($record->phone) . '</div>';
                }
                if ($record->phone_2) {
                    $html .= '<div class="text-gray-500 dark:text-gray-400">📱 ' . e($record->phone_2) . '</div>';
                }
                if ($record->email) {
                    $html .= '<div class="text-brand dark:text-indigo-400 truncate font-sans" title="' . e($record->email) . '">✉️ ' . e($record->email) . '</div>';
                }
                if (!$record->phone && !$record->phone_2 && !$record->email) {
                    $html .= '<span class="text-gray-400 italic font-sans">—</span>';
                }
                $html .= '</div>';
                return $html;
            })
            ->orderColumn('contact_channels', function ($query, $order) {
                $query->orderBy('phone', $order)->orderBy('email', $order);
            })
            ->filterColumn('contact_channels', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('phone', 'like', "%{$keyword}%")
                      ->orWhere('phone_2', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('source_badge', function (ScrapedRecord $record) {
                $source = e($record->source_name ?: 'Web Scraper');
                $url = $record->source_url ? e($record->source_url) : '#';

                return '
                    <div class="space-y-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                            🌐 ' . $source . '
                        </span>
                        ' . ($record->source_url ? '<div class="text-[11px] text-gray-400 truncate max-w-[150px]"><a href="' . $url . '" target="_blank" class="hover:underline hover:text-brand" title="' . $url . '">' . $url . '</a></div>' : '') . '
                    </div>
                ';
            })
            ->orderColumn('source_badge', function ($query, $order) {
                $query->orderBy('source_name', $order);
            })
            ->filterColumn('source_badge', function ($query, $keyword) {
                $query->where('source_name', 'like', "%{$keyword}%")
                      ->orWhere('source_url', 'like', "%{$keyword}%");
            })
            ->addColumn('status_badge', function (ScrapedRecord $record) {
                $statusColors = [
                    'new'        => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20',
                    'contacted'  => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20',
                    'qualified'  => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20',
                    'converted'  => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20',
                    'archived'   => 'bg-gray-500/10 text-gray-600 dark:text-gray-400 border border-gray-500/20',
                ];
                $style = $statusColors[strtolower($record->status)] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200';
                $label = ucfirst($record->status);

                return '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold ' . $style . '">' . e($label) . '</span>';
            })
            ->orderColumn('status_badge', function ($query, $order) {
                $query->orderBy('status', $order);
            })
            ->filterColumn('status_badge', function ($query, $keyword) {
                $query->where('status', 'like', "%{$keyword}%");
            })
            ->addColumn('action', function (ScrapedRecord $record) {
                return '
                    <div class="flex items-center justify-center gap-1.5">
                        <button type="button"
                            class="view_scraped_btn inline-flex items-center justify-center p-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-brand bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition"
                            data-id="' . $record->id . '" title="View Details">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>

                        <button type="button"
                            class="edit_scraped_btn inline-flex items-center justify-center px-2.5 py-1.5 text-xs font-medium text-white bg-brand hover:bg-brand/90 rounded-lg shadow-sm transition"
                            data-id="' . $record->id . '" title="Edit Record">
                            Edit
                        </button>

                        <div class="relative inline-block text-left dropdown">
                            <button type="button"
                                class="convert_dropdown_btn inline-flex items-center justify-center px-2 py-1.5 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition"
                                data-bs-toggle="dropdown" aria-expanded="false" title="Convert to CRM">
                                <span>Convert</span>
                                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-xl text-xs py-1 z-50">
                                <li>
                                    <a class="dropdown-item py-2 px-3 flex items-center gap-2 hover:bg-brand/10 hover:text-brand convert_to_profile_btn" href="javascript:void(0)" data-id="' . $record->id . '">
                                        <span>👤 Convert to CRM Profile</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 px-3 flex items-center gap-2 hover:bg-brand/10 hover:text-brand convert_to_org_btn" href="javascript:void(0)" data-id="' . $record->id . '">
                                        <span>🏢 Convert to CRM Organization</span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <button type="button"
                            class="delete_scraped_btn inline-flex items-center justify-center px-2.5 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition"
                            data-id="' . $record->id . '" title="Delete Record">
                            Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['checkbox', 'business_info', 'contact_person_display', 'contact_channels', 'source_badge', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * Trigger scraper execution with Guzzle.
     */
    public function run(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url'         => 'required|url|max:500',
            'source_name' => 'nullable|string|max:150',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $url = trim($request->url);
        $sourceName = $request->source_name ? trim($request->source_name) : null;

        $result = $this->scraperService->scrape($url, $sourceName, auth()->id());

        if (!$result['status']) {
            return response()->json([
                'status'  => false,
                'message' => $result['message'],
            ], 500);
        }

        return response()->json([
            'status'           => true,
            'message'          => $result['message'],
            'records_found'    => $result['records_found'],
            'records_imported' => $result['records_imported'],
        ]);
    }

    /**
     * Display static PDF scraper page.
     */
    public function pdfIndex()
    {
        $sources = PdfScrapedRecord::distinct()->whereNotNull('source_name')->pluck('source_name')->values()->all();
        $statuses = [
            'new'        => 'New / Unprocessed',
            'contacted'  => 'Contacted',
            'qualified'  => 'Qualified',
            'converted'  => 'Converted to CRM',
            'archived'   => 'Archived',
        ];

        $stats = [
            'total'     => PdfScrapedRecord::count(),
            'new'       => PdfScrapedRecord::where('status', 'new')->count(),
            'converted' => PdfScrapedRecord::where('status', 'converted')->count(),
            'sources'   => count($sources),
        ];

        return view('backend.scraper.pdf', compact('sources', 'statuses', 'stats'));
    }

    /**
     * DataTables AJAX data for Static PDF scraped records.
     * All fields are directly viewable with text-wrapping support.
     */
    public function pdfData(Request $request)
    {
        $query = PdfScrapedRecord::select('pdf_scraped_records.*');
        if (!$request->has('order')) {
    $query->latest('id');
}

        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }

        if ($request->filled('source_filter')) {
            $query->where('source_name', $request->source_filter);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('checkbox', function (PdfScrapedRecord $record) {
                return '<input type="checkbox" class="record-checkbox rounded border-gray-300 text-brand focus:ring-brand" value="' . $record->id . '">';
            })
            ->addColumn('business_info', function (PdfScrapedRecord $record) {
                $name = e($record->business_name ?: '—');
                return '<div class="font-bold text-gray-900 dark:text-gray-100 whitespace-normal break-words leading-snug min-w-[130px]">' . $name . '</div>';
            })
            ->orderColumn('business_info', function ($query, $order) {
                $query->orderBy('business_name', $order);
            })
            ->filterColumn('business_info', function ($query, $keyword) {
                $query->where('business_name', 'like', "%{$keyword}%");
            })
            ->addColumn('category_badge', function (PdfScrapedRecord $record) {
                if ($record->category) {
                    return '<span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-900 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-300 dark:border-amber-800 whitespace-normal break-words leading-tight">' . e($record->category) . '</span>';
                }
                return '<span class="text-xs text-gray-400 italic">—</span>';
            })
            ->orderColumn('category_badge', function ($query, $order) {
                $query->orderBy('category', $order);
            })
            ->filterColumn('category_badge', function ($query, $keyword) {
                $query->where('category', 'like', "%{$keyword}%");
            })
            ->addColumn('address_display', function (PdfScrapedRecord $record) {
                if ($record->address) {
                    return '<div class="text-xs text-gray-700 dark:text-gray-300 whitespace-normal break-words max-w-xs leading-relaxed">' . e($record->address) . '</div>';
                }
                return '<span class="text-xs text-gray-400 italic">—</span>';
            })
            ->orderColumn('address_display', function ($query, $order) {
                $query->orderBy('address', $order);
            })
            ->filterColumn('address_display', function ($query, $keyword) {
                $query->where('address', 'like', "%{$keyword}%");
            })
            ->addColumn('contact_person_display', function (PdfScrapedRecord $record) {
                if ($record->contact_person) {
                    return '<div class="font-medium text-gray-800 dark:text-gray-200 whitespace-normal break-words flex items-center gap-1.5 leading-snug">
                        <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span>' . e($record->contact_person) . '</span>
                    </div>';
                }
                return '<span class="text-xs text-gray-400 italic">—</span>';
            })
            ->orderColumn('contact_person_display', function ($query, $order) {
                $query->orderBy('contact_person', $order);
            })
            ->filterColumn('contact_person_display', function ($query, $keyword) {
                $query->where('contact_person', 'like', "%{$keyword}%");
            })
            ->addColumn('contact_channels', function (PdfScrapedRecord $record) {
                $html = '<div class="text-xs space-y-1 font-mono whitespace-normal break-words">';
                if ($record->phone) {
                    $html .= '<div class="text-gray-900 dark:text-gray-100 font-bold">📞 ' . e($record->phone) . '</div>';
                }
                if ($record->phone_2) {
                    $html .= '<div class="text-gray-600 dark:text-gray-400">📱 ' . e($record->phone_2) . '</div>';
                }
                if ($record->email) {
                    $html .= '<div class="text-brand dark:text-indigo-400 font-sans truncate" title="' . e($record->email) . '">✉️ ' . e($record->email) . '</div>';
                }
                if (!$record->phone && !$record->phone_2 && !$record->email) {
                    $html .= '<span class="text-gray-400 italic font-sans">—</span>';
                }
                $html .= '</div>';
                return $html;
            })
            ->orderColumn('contact_channels', function ($query, $order) {
                $query->orderBy('phone', $order)->orderBy('phone_2', $order);
            })
            ->filterColumn('contact_channels', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('phone', 'like', "%{$keyword}%")
                      ->orWhere('phone_2', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('source_badge', function (PdfScrapedRecord $record) {
                $source = e($record->source_name ?: 'PDF Scraper');
                $url = $record->source_url ? e($record->source_url) : '#';

                return '
                    <div class="space-y-1 whitespace-normal break-words max-w-[170px]">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                            📄 ' . $source . '
                        </span>
                        ' . ($record->source_url ? '<div class="text-[11px] text-gray-400 truncate"><a href="' . $url . '" target="_blank" class="hover:underline hover:text-brand" title="' . $url . '">' . $url . '</a></div>' : '') . '
                    </div>
                ';
            })
            ->orderColumn('source_badge', function ($query, $order) {
                $query->orderBy('source_name', $order);
            })
            ->filterColumn('source_badge', function ($query, $keyword) {
                $query->where('source_name', 'like', "%{$keyword}%")
                      ->orWhere('source_url', 'like', "%{$keyword}%");
            })
            ->addColumn('status_badge', function (PdfScrapedRecord $record) {
                $statusColors = [
                    'new'        => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20',
                    'contacted'  => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20',
                    'qualified'  => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20',
                    'converted'  => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20',
                    'archived'   => 'bg-gray-500/10 text-gray-600 dark:text-gray-400 border border-gray-500/20',
                ];
                $style = $statusColors[strtolower($record->status)] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200';
                $label = ucfirst($record->status);

                return '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold ' . $style . '">' . e($label) . '</span>';
            })
            ->orderColumn('status_badge', function ($query, $order) {
                $query->orderBy('status', $order);
            })
            ->filterColumn('status_badge', function ($query, $keyword) {
                $query->where('status', 'like', "%{$keyword}%");
            })
            ->addColumn('action', function (PdfScrapedRecord $record) {
                return '
                    <div class="flex items-center justify-center gap-1.5">
                        <button type="button"
                            class="view_scraped_btn inline-flex items-center justify-center p-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-brand bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition"
                            data-id="' . $record->id . '" title="View Details">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>

                        <button type="button"
                            class="edit_scraped_btn inline-flex items-center justify-center px-2.5 py-1.5 text-xs font-medium text-white bg-brand hover:bg-brand/90 rounded-lg shadow-sm transition"
                            data-id="' . $record->id . '" title="Edit Record">
                            Edit
                        </button>

                        <div class="relative inline-block text-left dropdown">
                            <button type="button"
                                class="convert_dropdown_btn inline-flex items-center justify-center px-2 py-1.5 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition"
                                data-bs-toggle="dropdown" aria-expanded="false" title="Convert to CRM">
                                <span>Convert</span>
                                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-xl text-xs py-1 z-50">
                                <li>
                                    <a class="dropdown-item py-2 px-3 flex items-center gap-2 hover:bg-brand/10 hover:text-brand convert_to_profile_btn" href="javascript:void(0)" data-id="' . $record->id . '">
                                        <span>👤 Convert to CRM Profile</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 px-3 flex items-center gap-2 hover:bg-brand/10 hover:text-brand convert_to_org_btn" href="javascript:void(0)" data-id="' . $record->id . '">
                                        <span>🏢 Convert to CRM Organization</span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <button type="button"
                            class="delete_scraped_btn inline-flex items-center justify-center px-2.5 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition"
                            data-id="' . $record->id . '" title="Delete Record">
                            Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['checkbox', 'business_info', 'category_badge', 'address_display', 'contact_person_display', 'contact_channels', 'source_badge', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * Trigger static PDF scraper execution.
     */
    public function runPdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pdf_url'     => 'nullable|url|max:500',
            'pdf_file'    => 'nullable|file|mimes:pdf|max:15360',
            'source_name' => 'nullable|string|max:150',
            'mode'        => 'nullable|string|in:sync,queue',
        ]);

        $validator->after(function ($v) use ($request) {
            if (!$request->filled('pdf_url') && !$request->hasFile('pdf_file')) {
                $v->errors()->add('pdf_url', 'Please provide either a PDF URL or upload a PDF file.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $sourceName = $request->source_name ? trim($request->source_name) : null;
        $mode = $request->input('mode', 'sync');

        if ($request->hasFile('pdf_file')) {
            $uploaded = $request->file('pdf_file');
            $originalName = $uploaded->getClientOriginalName();
            $path = $uploaded->storeAs('scraped_pdfs', time() . '_' . $originalName, 'local');
            $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($path);
            $sourceName = $sourceName ?: 'Uploaded PDF (' . $originalName . ')';

            $result = $this->scraperService->scrapePdf($fullPath, $sourceName, auth()->id());
        } else {
            $pdfUrl = trim($request->pdf_url);

            if ($mode === 'queue') {
                \App\Jobs\ScrapeWebsiteJob::dispatch($pdfUrl, $sourceName, auth()->id(), true);

                return response()->json([
                    'status'  => true,
                    'message' => 'PDF Scrape job dispatched to background queue! Records will appear shortly.',
                    'queued'  => true,
                ]);
            }

            $result = $this->scraperService->scrapePdf($pdfUrl, $sourceName, auth()->id());
        }

        if (!$result['status']) {
            return response()->json([
                'status'  => false,
                'message' => $result['message'],
            ], 500);
        }

        return response()->json([
            'status'           => true,
            'message'          => $result['message'],
            'records_found'    => $result['records_found'],
            'records_imported' => $result['records_imported'],
        ]);
    }

    /**
     * Show single record data as JSON.
     */
    public function show($id)
    {
        $record = ScrapedRecord::find($id);

        if (!$record) {
            return response()->json(['status' => false, 'message' => 'Record not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'record' => [
                'id'             => $record->id,
                'business_name'  => $record->business_name,
                'contact_person' => $record->contact_person,
                'phone'          => $record->phone,
                'phone_2'        => $record->phone_2,
                'email'          => $record->email,
                'category'       => $record->category,
                'address'        => $record->address,
                'website_domain' => $record->website_domain,
                'source_name'    => $record->source_name,
                'source_url'     => $record->source_url,
                'status'         => ucfirst($record->status),
                'raw_data'       => $record->raw_data,
                'notes'          => $record->notes,
                'created_at'     => $record->created_at ? $record->created_at->format('M d, Y h:i A') : '—',
            ],
        ]);
    }

    /**
     * Show edit form data as JSON.
     */
    public function edit($id)
    {
        $record = ScrapedRecord::find($id);

        if (!$record) {
            return response()->json(['status' => false, 'message' => 'Record not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'record' => [
                'id'             => $record->id,
                'business_name'  => $record->business_name,
                'contact_person' => $record->contact_person,
                'phone'          => $record->phone,
                'phone_2'        => $record->phone_2,
                'email'          => $record->email,
                'category'       => $record->category,
                'address'        => $record->address,
                'website_domain' => $record->website_domain,
                'source_name'    => $record->source_name,
                'status'         => $record->status,
                'notes'          => $record->notes,
            ],
        ]);
    }

    /**
     * Update scraped record.
     */
    public function update(Request $request, $id)
    {
        $record = ScrapedRecord::find($id);

        if (!$record) {
            return response()->json(['status' => false, 'message' => 'Record not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'business_name'  => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:100',
            'phone_2'        => 'nullable|string|max:100',
            'email'          => 'nullable|email|max:191',
            'category'       => 'nullable|string|max:255',
            'address'        => 'nullable|string|max:1000',
            'website_domain' => 'nullable|string|max:255',
            'status'         => 'required|string|in:new,contacted,qualified,converted,archived',
            'notes'          => 'nullable|string|max:2000',
        ]);

        $validator->after(function ($v) use ($request) {
            $hasAny = $request->filled('business_name')
                || $request->filled('contact_person')
                || $request->filled('phone')
                || $request->filled('email');

            if (!$hasAny) {
                $v->errors()->add('business_name', 'At least one of Business Name, Contact Person, Phone, or Email must be provided.');
            }
        });

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $record->update([
                'business_name'  => $request->business_name ?: null,
                'contact_person' => $request->contact_person ?: null,
                'phone'          => $request->phone ?: null,
                'phone_2'        => $request->phone_2 ?: null,
                'email'          => $request->email ?: null,
                'category'       => $request->category ?: null,
                'address'        => $request->address ?: null,
                'website_domain' => $request->website_domain ?: null,
                'status'         => $request->status,
                'notes'          => $request->notes ?: null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Record updated successfully.',
                'data'    => $record,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete record.
     */
    public function destroy($id)
    {
        $record = ScrapedRecord::find($id);

        if (!$record) {
            return response()->json(['status' => false, 'message' => 'Record not found.'], 404);
        }

        $record->delete();

        return response()->json(['status' => true, 'message' => 'Record deleted successfully.']);
    }

    /**
     * Bulk delete records.
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:scraped_records,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $count = ScrapedRecord::whereIn('id', $request->ids)->delete();

        return response()->json(['status' => true, 'message' => "{$count} records deleted successfully."]);
    }

    /**
     * Convert scraped record to CRM Profile.
     */
    public function convertProfile($id)
    {
        $record = ScrapedRecord::find($id);

        if (!$record) {
            return response()->json(['status' => false, 'message' => 'Record not found.'], 404);
        }

        try {
            $profile = $this->scraperService->convertToProfile($record);

            return response()->json([
                'status'     => true,
                'message'    => "Converted to CRM Profile ({$profile->full_name}) successfully!",
                'profile_id' => $profile->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Convert scraped record to CRM Organization.
     */
    public function convertOrg($id)
    {
        $record = ScrapedRecord::find($id);

        if (!$record) {
            return response()->json(['status' => false, 'message' => 'Record not found.'], 404);
        }

        try {
            $org = $this->scraperService->convertToOrganization($record);

            return response()->json([
                'status'  => true,
                'message' => "Converted to CRM Organization ({$org->name}) successfully!",
                'org_id'  => $org->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // DEDICATED STATIC PDF SCRAPER CRUD & CONVERSION ENDPOINTS
    // All methods operate strictly on PdfScrapedRecord (table: pdf_scraped_records)
    // =========================================================================

    /**
     * Show single PDF scraped record details as JSON.
     */
    public function pdfShow($id)
    {
        $record = PdfScrapedRecord::find($id);

        if (!$record) {
            return response()->json(['status' => false, 'message' => 'PDF scraped record not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'record' => [
                'id'             => $record->id,
                'business_name'  => $record->business_name,
                'contact_person' => $record->contact_person,
                'phone'          => $record->phone,
                'phone_2'        => $record->phone_2,
                'email'          => $record->email,
                'category'       => $record->category,
                'address'        => $record->address,
                'website_domain' => $record->website_domain,
                'source_name'    => $record->source_name,
                'source_url'     => $record->source_url,
                'status'         => ucfirst($record->status),
                'raw_data'       => $record->raw_data,
                'notes'          => $record->notes,
                'created_at'     => $record->created_at ? $record->created_at->format('M d, Y h:i A') : '—',
            ],
        ]);
    }

    /**
     * Show edit form data for a PDF scraped record as JSON.
     */
    public function pdfEdit($id)
    {
        $record = PdfScrapedRecord::find($id);

        if (!$record) {
            return response()->json(['status' => false, 'message' => 'PDF scraped record not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'record' => [
                'id'             => $record->id,
                'business_name'  => $record->business_name,
                'contact_person' => $record->contact_person,
                'phone'          => $record->phone,
                'phone_2'        => $record->phone_2,
                'email'          => $record->email,
                'category'       => $record->category,
                'address'        => $record->address,
                'website_domain' => $record->website_domain,
                'source_name'    => $record->source_name,
                'status'         => $record->status,
                'notes'          => $record->notes,
            ],
        ]);
    }

    /**
     * Update a PDF scraped record.
     */
    public function pdfUpdate(Request $request, $id)
    {
        $record = PdfScrapedRecord::find($id);

        if (!$record) {
            return response()->json(['status' => false, 'message' => 'PDF scraped record not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'business_name'  => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:100',
            'phone_2'        => 'nullable|string|max:100',
            'email'          => 'nullable|email|max:191',
            'category'       => 'nullable|string|max:255',
            'address'        => 'nullable|string|max:1000',
            'website_domain' => 'nullable|string|max:255',
            'status'         => 'required|string|in:new,contacted,qualified,converted,archived',
            'notes'          => 'nullable|string|max:2000',
        ]);

        $validator->after(function ($v) use ($request) {
            $hasAny = $request->filled('business_name')
                || $request->filled('contact_person')
                || $request->filled('phone')
                || $request->filled('email');

            if (!$hasAny) {
                $v->errors()->add('business_name', 'At least one of Business Name, Contact Person, Phone, or Email must be provided.');
            }
        });

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $record->update([
                'business_name'  => $request->business_name ?: null,
                'contact_person' => $request->contact_person ?: null,
                'phone'          => $request->phone ?: null,
                'phone_2'        => $request->phone_2 ?: null,
                'email'          => $request->email ?: null,
                'category'       => $request->category ?: null,
                'address'        => $request->address ?: null,
                'website_domain' => $request->website_domain ?: null,
                'status'         => $request->status,
                'notes'          => $request->notes ?: null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'PDF record updated successfully.',
                'data'    => $record,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a single PDF scraped record.
     */
    public function pdfDestroy($id)
    {
        $record = PdfScrapedRecord::find($id);

        if (!$record) {
            return response()->json(['status' => false, 'message' => 'PDF scraped record not found.'], 404);
        }

        $record->delete();

        return response()->json(['status' => true, 'message' => 'PDF record deleted successfully.']);
    }

    /**
     * Bulk delete selected PDF scraped records.
     */
    public function pdfBulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:pdf_scraped_records,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $count = PdfScrapedRecord::whereIn('id', $request->ids)->delete();

        return response()->json(['status' => true, 'message' => "{$count} PDF records deleted successfully."]);
    }

    /**
     * Convert a PDF scraped record into a CRM Profile (Lead/Contact).
     */
    public function pdfConvertProfile($id)
    {
        $record = PdfScrapedRecord::find($id);

        if (!$record) {
            return response()->json(['status' => false, 'message' => 'PDF scraped record not found.'], 404);
        }

        try {
            $profile = $this->scraperService->convertToProfile($record);

            return response()->json([
                'status'     => true,
                'message'    => "Converted to CRM Profile ({$profile->full_name}) successfully!",
                'profile_id' => $profile->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Convert a PDF scraped record into a CRM Organization (Corporate Account).
     */
    public function pdfConvertOrg($id)
    {
        $record = PdfScrapedRecord::find($id);

        if (!$record) {
            return response()->json(['status' => false, 'message' => 'PDF scraped record not found.'], 404);
        }

        try {
            $org = $this->scraperService->convertToOrganization($record);

            return response()->json([
                'status'  => true,
                'message' => "Converted to CRM Organization ({$org->name}) successfully!",
                'org_id'  => $org->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
