@extends('backend.layouts.master')

@section('content')
    <!-- Dependencies: Bootstrap 5.3, Icons, jQuery, and DataTables -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css" rel="stylesheet" />

    <!-- ========================================================= -->
    <!-- MAIN CONTAINER                                            -->
    <!-- ========================================================= -->
    <div id="slot-content" class="max-w-7xl mx-auto p-4 sm:p-6 space-y-6">

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-700 pb-3">
            <a href="{{ route('scraper.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-brand hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                <i class="bi bi-globe2"></i>
                Web Scraper (HTML)
            </a>
            <a href="{{ route('scraper.pdf') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-brand bg-brand/10 dark:bg-brand/20 transition">
                <i class="bi bi-file-earmark-pdf-fill"></i>
                Static PDF Scraper (Directory Harvester)
            </a>
        </div>

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2.5">
                    <span
                        class="inline-flex p-2 rounded-xl bg-red-500/10 text-red-600 dark:bg-red-500/20 dark:text-red-400">
                        <i class="bi bi-file-earmark-pdf-fill fs-4"></i>
                    </span>
                    Static PDF Scraper & Directory Harvester
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Extract structured business directories, chamber records, and tabular catalogs from PDF documents and
                    static files.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Smalot & Guzzle Engine Active
                </span>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div
                class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="p-3 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400">
                    <i class="bi bi-database-fill-check fs-4"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Total Scraped</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100" id="statTotal">{{ $stats['total'] }}
                    </div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="p-3 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">New Leads</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100" id="statNew">{{ $stats['new'] }}</div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="p-3 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <i class="bi bi-person-check-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Converted to CRM</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100" id="statConverted">
                        {{ $stats['converted'] }}</div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="p-3 rounded-lg bg-purple-500/10 text-purple-600 dark:text-purple-400">
                    <i class="bi bi-folder-check fs-4"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Unique Sources</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100" id="statSources">{{ $stats['sources'] }}
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- PDF SCRAPER CONTROLLER BOX                                -->
        <!-- ========================================================= -->
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 shadow-md p-5 sm:p-6">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-8 h-8 rounded-lg bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center">
                        <i class="bi bi-file-earmark-arrow-down-fill fs-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">Scrape a PDF Document</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Provide an online PDF link (like SCCI Citrus
                            Exporters) or upload any directory PDF file.</p>
                    </div>
                </div>
            </div>

            <form id="pdfScraperForm" class="space-y-4" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Preset Selection -->
                    <div class="md:col-span-4">
                        <label for="pdfPreset" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            PDF Directory Presets
                        </label>
                        <select id="pdfPreset"
                            class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-brand focus:border-brand shadow-sm">
                            <option value="https://www.scci.org.pk/assets/img/categories/media/1715664687_CITRUS.pdf"
                                data-name="SCCI Citrus PDF Directory" selected>
                                🍊 SCCI Citrus / Kinnow Exporters Directory (PDF)
                            </option>
                            <option value="custom" data-name="Custom PDF Directory">
                                🌐 Custom PDF URL Link
                            </option>
                            <option value="upload" data-name="Uploaded PDF Document">
                                📁 Upload Local PDF File
                            </option>
                        </select>
                    </div>

                    <!-- Target PDF URL -->
                    <div class="md:col-span-5" id="urlInputContainer">
                        <label for="pdfUrl" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Target PDF URL <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="bi bi-link-45deg fs-5"></i>
                            </span>
                            <input type="url" id="pdfUrl" name="pdf_url"
                                value="https://www.scci.org.pk/assets/img/categories/media/1715664687_CITRUS.pdf"
                                placeholder="https://www.scci.org.pk/.../CITRUS.pdf"
                                class="w-full pl-9 text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-brand focus:border-brand shadow-sm font-mono" />
                        </div>
                        <span id="err_pdf_url" class="text-xs text-red-500 mt-1 block font-medium"></span>
                    </div>

                    <!-- Target File Upload (Hidden by default, shown when Upload preset chosen) -->
                    <div class="md:col-span-5 hidden" id="fileInputContainer">
                        <label for="pdfFile" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Choose Local PDF File <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="pdfFile" name="pdf_file" accept=".pdf"
                            class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-brand focus:border-brand shadow-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand/10 file:text-brand hover:file:bg-brand/20" />
                        <span id="err_pdf_file" class="text-xs text-red-500 mt-1 block font-medium"></span>
                    </div>

                    <!-- Source Label -->
                    <div class="md:col-span-3">
                        <label for="pdfSourceName"
                            class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Source Label
                        </label>
                        <input type="text" id="pdfSourceName" name="source_name" value="SCCI Citrus PDF Directory"
                            placeholder="e.g. SCCI Citrus Directory"
                            class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-brand focus:border-brand shadow-sm" />
                    </div>
                </div>

                <!-- Mode & Actions -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                    <div class="flex items-center gap-4">
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Execution Mode:</span>
                        <label
                            class="inline-flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                            <input type="radio" name="mode" value="sync" checked
                                class="text-brand focus:ring-brand">
                            <span>Instant Live Scrape</span>
                        </label>
                        <label
                            class="inline-flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                            <input type="radio" name="mode" value="queue" class="text-brand focus:ring-brand">
                            <span>Background Queue (Async)</span>
                        </label>
                    </div>

                    <button type="submit" id="startPdfScrapeBtn"
                        class="inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold text-sm px-6 py-2.5 rounded-lg shadow-sm transition disabled:opacity-50">
                        <i class="bi bi-play-circle-fill"></i>
                        <span id="startPdfScrapeBtnText">Scrape PDF Directory Now</span>
                    </button>
                </div>
            </form>

            <!-- Dynamic Result Alert Box -->
            <div id="pdfScrapeResultAlert" class="hidden mt-4 p-4 rounded-xl text-sm transition"></div>
        </div>

        <!-- ========================================================= -->
        <!-- FILTER BAR & BULK ACTIONS                                 -->
        <!-- ========================================================= -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Status Filter -->
                <div class="flex items-center gap-2">
                    <label for="filterStatus"
                        class="text-xs font-semibold text-gray-500 dark:text-gray-400">Status:</label>
                    <select id="filterStatus"
                        class="text-xs rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 py-1.5">
                        <option value="">All Statuses</option>
                        @foreach ($statuses as $val => $lbl)
                            <option value="{{ $val }}">{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Source Filter -->
                <div class="flex items-center gap-2">
                    <label for="filterSource"
                        class="text-xs font-semibold text-gray-500 dark:text-gray-400">Source:</label>
                    <select id="filterSource"
                        class="text-xs rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 py-1.5">
                        <option value="" selected>All Sources</option>
                        @foreach ($sources as $src)
                            <option value="{{ $src }}">{{ $src }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="button" id="resetFiltersBtn" class="text-xs text-gray-500 hover:text-brand underline">
                    Reset Filters
                </button>
            </div>

            <!-- Bulk Delete Action -->
            <div class="flex items-center gap-2">
                <span id="selectedCountText" class="text-xs text-gray-500 dark:text-gray-400 hidden">0 selected</span>
                <button type="button" id="bulkDeleteBtn"
                    class="hidden inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition">
                    <i class="bi bi-trash3-fill"></i>
                    <span>Delete Selected</span>
                </button>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- DATATABLE CONTAINER                                       -->
        <!-- ========================================================= -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden p-4">
            <div class="table-responsive">
                <table id="scrapedRecordsTable"
                    class="w-full text-sm text-left text-gray-600 dark:text-gray-300 display nowrap" style="width:100%">
                    <thead
                        class="text-xs uppercase bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="p-3 w-8 text-center">
                                <input type="checkbox" id="selectAllCheckbox"
                                    class="rounded border-gray-300 text-brand focus:ring-brand">
                            </th>
                            <th class="p-3">#</th>
                            <th class="p-3">Company / Firm</th>
                            <th class="p-3">Category</th>
                            <th class="p-3">Address</th>
                            <th class="p-3">Contact Person</th>
                            <th class="p-3">Phone & Email</th>
                            <th class="p-3">Source</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ========================================================= -->
    <!-- VIEW MODAL                                                -->
    <!-- ========================================================= -->
    <div class="modal fade" id="viewScrapedModal" tabindex="-1" aria-labelledby="viewScrapedModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-2xl shadow-xl dark:bg-gray-800 dark:text-gray-100">
                <div class="modal-header border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                    <h5 class="modal-title font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2"
                        id="viewScrapedModalLabel">
                        <i class="bi bi-card-heading text-brand"></i>
                        <span>Scraped Record Details</span>
                    </h5>
                    <button type="button" class="btn-close dark:filter dark:invert" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            class="bg-gray-50 dark:bg-gray-700/40 p-3.5 rounded-xl border border-gray-100 dark:border-gray-700">
                            <span class="text-xs text-gray-400 block font-medium">Business / Firm Name</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                                id="viewBusinessName">—</span>
                        </div>
                        <div
                            class="bg-gray-50 dark:bg-gray-700/40 p-3.5 rounded-xl border border-gray-100 dark:border-gray-700">
                            <span class="text-xs text-gray-400 block font-medium">Contact Person</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                                id="viewContactPerson">—</span>
                        </div>
                        <div
                            class="bg-gray-50 dark:bg-gray-700/40 p-3.5 rounded-xl border border-gray-100 dark:border-gray-700">
                            <span class="text-xs text-gray-400 block font-medium">Primary Phone / Mobile</span>
                            <span class="text-sm font-mono font-semibold text-gray-900 dark:text-gray-100"
                                id="viewPhone">—</span>
                        </div>
                        <div
                            class="bg-gray-50 dark:bg-gray-700/40 p-3.5 rounded-xl border border-gray-100 dark:border-gray-700">
                            <span class="text-xs text-gray-400 block font-medium">Secondary Phone / Mobile</span>
                            <span class="text-sm font-mono text-gray-700 dark:text-gray-300" id="viewPhone2">—</span>
                        </div>
                        <div
                            class="bg-gray-50 dark:bg-gray-700/40 p-3.5 rounded-xl border border-gray-100 dark:border-gray-700">
                            <span class="text-xs text-gray-400 block font-medium">Email Address</span>
                            <span class="text-sm text-brand dark:text-indigo-400" id="viewEmail">—</span>
                        </div>
                        <div
                            class="bg-gray-50 dark:bg-gray-700/40 p-3.5 rounded-xl border border-gray-100 dark:border-gray-700">
                            <span class="text-xs text-gray-400 block font-medium">Category / Sector</span>
                            <span class="text-sm text-gray-800 dark:text-gray-200 font-medium" id="viewCategory">—</span>
                        </div>
                    </div>

                    <div
                        class="bg-gray-50 dark:bg-gray-700/40 p-3.5 rounded-xl border border-gray-100 dark:border-gray-700">
                        <span class="text-xs text-gray-400 block font-medium">Address</span>
                        <span class="text-sm text-gray-800 dark:text-gray-200" id="viewAddress">—</span>
                    </div>

                    <!-- Raw Data JSON Explorer -->
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-2">Raw Metadata &
                            Extracted Attributes</span>
                        <pre id="viewRawJson" class="text-xs bg-gray-900 text-emerald-400 p-4 rounded-xl overflow-x-auto max-h-48 font-mono"></pre>
                    </div>
                </div>
                <div class="modal-footer border-t border-gray-100 dark:border-gray-700 px-6 py-3">
                    <button type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 rounded-lg transition"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- EDIT MODAL                                                -->
    <!-- ========================================================= -->
    <div class="modal fade" id="editScrapedModal" tabindex="-1" aria-labelledby="editScrapedModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-2xl shadow-xl dark:bg-gray-800 dark:text-gray-100">
                <form id="editScrapedForm">
                    @csrf
                    <input type="hidden" id="editRecordId" name="id" />

                    <div class="modal-header border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                        <h5 class="modal-title font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2"
                            id="editScrapedModalLabel">
                            <i class="bi bi-pencil-square text-brand"></i>
                            <span>Edit Scraped Lead</span>
                        </h5>
                        <button type="button" class="btn-close dark:filter dark:invert" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="editBusinessName"
                                    class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Company /
                                    Firm Name</label>
                                <input type="text" id="editBusinessName" name="business_name"
                                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            </div>

                            <div>
                                <label for="editContactPerson"
                                    class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Contact
                                    Person</label>
                                <input type="text" id="editContactPerson" name="contact_person"
                                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            </div>

                            <div>
                                <label for="editPhone"
                                    class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Phone /
                                    Mobile 1</label>
                                <input type="text" id="editPhone" name="phone"
                                    class="w-full text-sm font-mono rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            </div>

                            <div>
                                <label for="editPhone2"
                                    class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Phone /
                                    Mobile 2</label>
                                <input type="text" id="editPhone2" name="phone_2"
                                    class="w-full text-sm font-mono rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            </div>

                            <div>
                                <label for="editEmail"
                                    class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                <input type="email" id="editEmail" name="email"
                                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            </div>

                            <div>
                                <label for="editCategory"
                                    class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Category</label>
                                <input type="text" id="editCategory" name="category"
                                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            </div>

                            <div>
                                <label for="editStatus"
                                    class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                <select id="editStatus" name="status"
                                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="new">New</option>
                                    <option value="contacted">Contacted</option>
                                    <option value="qualified">Qualified</option>
                                    <option value="converted">Converted</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="editAddress"
                                class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Address</label>
                            <textarea id="editAddress" name="address" rows="2"
                                class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer border-t border-gray-100 dark:border-gray-700 px-6 py-3">
                        <button type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 rounded-lg transition"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="saveEditBtn"
                            class="px-4 py-2 text-sm font-medium text-white bg-brand hover:bg-brand/90 rounded-lg shadow-sm transition">Save
                            Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Direct CDN Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            // CSRF Setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
                }
            });

            // Preset Switcher
            $('#pdfPreset').on('change', function() {
                var val = $(this).val();
                var name = $(this).find(':selected').data('name');

                if (val === 'upload') {
                    $('#urlInputContainer').addClass('hidden');
                    $('#fileInputContainer').removeClass('hidden');
                    $('#pdfUrl').val('');
                    $('#pdfSourceName').val(name || 'Uploaded PDF Document');
                } else if (val === 'custom') {
                    $('#fileInputContainer').addClass('hidden');
                    $('#urlInputContainer').removeClass('hidden');
                    $('#pdfUrl').val('').focus();
                    $('#pdfSourceName').val(name || 'Custom PDF Directory');
                } else {
                    $('#fileInputContainer').addClass('hidden');
                    $('#urlInputContainer').removeClass('hidden');
                    $('#pdfUrl').val(val);
                    $('#pdfSourceName').val(name || 'SCCI Citrus PDF Directory');
                }
            });

            // DataTables Initialization
            var table = $('#scrapedRecordsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('scraper.pdf.data') }}",
                    data: function(d) {
                        d.status_filter = $('#filterStatus').val();
                        d.source_filter = $('#filterSource').val();
                    }
                },
                columns: [{
                        data: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'business_info',
                        name: 'business_info'
                    },
                    {
                        data: 'category_badge',
                        name: 'category_badge'
                    },
                    {
                        data: 'address_display',
                        name: 'address_display'
                    },
                    {
                        data: 'contact_person_display',
                        name: 'contact_person_display'
                    },
                    {
                        data: 'contact_channels',
                        name: 'contact_channels'
                    },
                    {
                        data: 'source_badge',
                        name: 'source_badge'
                    },
                    {
                        data: 'status_badge',
                        name: 'status_badge'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [],
                pageLength: 25,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search firm, contact, phone...",
                    processing: '<div class="spinner-border spinner-border-sm text-brand" role="status"></div> Loading records...'
                }
            });

            function refreshPdfStats() {
                $.get("{{ route('scraper.pdf') }}", function(html) {
                    var $parsed = $('<div>').html(html);
                    $('#statTotal').text($parsed.find('#statTotal').text());
                    $('#statNew').text($parsed.find('#statNew').text());
                    $('#statConverted').text($parsed.find('#statConverted').text());
                    $('#statSources').text($parsed.find('#statSources').text());
                });
            }

            // Filter triggers
            $('#filterStatus, #filterSource').on('change', function() {
                table.draw();
            });

            $('#resetFiltersBtn').on('click', function() {
                $('#filterStatus').val('');
                $('#filterSource').val('');
                table.draw();
            });

            // Checkbox selection
            $('#selectAllCheckbox').on('change', function() {
                var checked = $(this).is(':checked');
                $('.record-checkbox').prop('checked', checked);
                updateBulkActions();
            });

            $(document).on('change', '.record-checkbox', function() {
                updateBulkActions();
            });

            function updateBulkActions() {
                var count = $('.record-checkbox:checked').length;
                if (count > 0) {
                    $('#selectedCountText').text(count + ' selected').removeClass('hidden');
                    $('#bulkDeleteBtn').removeClass('hidden');
                } else {
                    $('#selectedCountText').addClass('hidden');
                    $('#bulkDeleteBtn').addClass('hidden');
                    $('#selectAllCheckbox').prop('checked', false);
                }
            }

            // PDF Scraper Form Submission
            $('#pdfScraperForm').on('submit', function(e) {
                e.preventDefault();

                var $btn = $('#startPdfScrapeBtn');
                var $btnText = $('#startPdfScrapeBtnText');
                var $alert = $('#pdfScrapeResultAlert');

                $btn.prop('disabled', true);
                $btnText.html(
                    '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Extracting & Parsing PDF...'
                    );
                $alert.addClass('hidden').removeClass(
                    'bg-emerald-50 text-emerald-800 bg-red-50 text-red-800 border');

                var formData = new FormData(this);

                $.ajax({
                    url: "{{ route('scraper.pdf.run') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $btn.prop('disabled', false);
                        $btnText.text('Scrape PDF Directory Now');

                        $alert.removeClass('hidden')
                            .addClass(
                                'bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
                                )
                            .html(
                                '<i class="bi bi-check-circle-fill me-1.5 text-emerald-600"></i> ' +
                                res.message);

                        // Reset filters to ensure newly scraped records are displayed immediately
                        $('#filterStatus').val('');
                        $('#filterSource').val('');
                        table.draw();
                        refreshPdfStats();
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false);
                        $btnText.text('Scrape PDF Directory Now');

                        var msg = 'An unexpected error occurred.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            var errors = Object.values(xhr.responseJSON.errors).flat();
                            msg = errors.join('<br>');
                        }

                        $alert.removeClass('hidden')
                            .addClass(
                                'bg-red-50 text-red-800 border border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800'
                                )
                            .html(
                                '<i class="bi bi-exclamation-triangle-fill me-1.5 text-red-600"></i> ' +
                                msg);
                    }
                });
            });

            // View Details Modal
            $(document).on('click', '.view_scraped_btn', function() {
                var id = $(this).data('id');
                $.get("{{ url('scraper/pdf') }}/" + id + "/show", function(res) {
                    if (res.status && res.record) {
                        var r = res.record;
                        $('#viewBusinessName').text(r.business_name || '—');
                        $('#viewContactPerson').text(r.contact_person || '—');
                        $('#viewPhone').text(r.phone || '—');
                        $('#viewPhone2').text(r.phone_2 || '—');
                        $('#viewEmail').text(r.email || '—');
                        $('#viewCategory').text(r.category || '—');
                        $('#viewAddress').text(r.address || '—');
                        $('#viewRawJson').text(JSON.stringify(r.raw_data || {}, null, 2));

                        var modal = new bootstrap.Modal(document.getElementById(
                        'viewScrapedModal'));
                        modal.show();
                    }
                }).fail(function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Failed to load PDF record.'));
                });
            });

            // Edit Modal
            $(document).on('click', '.edit_scraped_btn', function() {
                var id = $(this).data('id');
                $.get("{{ url('scraper/pdf') }}/" + id + "/edit", function(res) {
                    if (res.status && res.record) {
                        var r = res.record;
                        $('#editRecordId').val(r.id);
                        $('#editBusinessName').val(r.business_name || '');
                        $('#editContactPerson').val(r.contact_person || '');
                        $('#editPhone').val(r.phone || '');
                        $('#editPhone2').val(r.phone_2 || '');
                        $('#editEmail').val(r.email || '');
                        $('#editCategory').val(r.category || '');
                        $('#editAddress').val(r.address || '');
                        $('#editStatus').val(r.status || 'new');

                        var modal = new bootstrap.Modal(document.getElementById(
                        'editScrapedModal'));
                        modal.show();
                    }
                }).fail(function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message ||
                        'Failed to load PDF record for editing.'));
                });
            });

            // Save Edit Form
            $('#editScrapedForm').on('submit', function(e) {
                e.preventDefault();
                var id = $('#editRecordId').val();

                $.post("{{ url('scraper/pdf') }}/" + id + "/update", $(this).serialize(), function(res) {
                    if (res.status) {
                        bootstrap.Modal.getInstance(document.getElementById('editScrapedModal'))
                            .hide();
                        table.draw();
                        refreshPdfStats();
                    }
                }).fail(function(xhr) {
                    alert('Error saving record: ' + (xhr.responseJSON?.message || 'Check inputs'));
                });
            });

            // Delete Record
            $(document).on('click', '.delete_scraped_btn', function() {
                if (!confirm('Are you sure you want to delete this PDF scraped record?')) return;
                var id = $(this).data('id');

                $.ajax({
                    url: "{{ url('scraper/pdf') }}/" + id,
                    type: 'DELETE',
                    success: function() {
                        table.draw();
                        refreshPdfStats();
                    },
                    error: function(xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message ||
                            'Failed to delete record.'));
                    }
                });
            });

            // Bulk Delete
            $('#bulkDeleteBtn').on('click', function() {
                var ids = $('.record-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();
                if (ids.length === 0) return;
                if (!confirm('Are you sure you want to delete ' + ids.length + ' PDF records?')) return;

                $.post("{{ route('scraper.pdf.bulk-delete') }}", {
                    ids: ids
                }, function() {
                    $('#selectAllCheckbox').prop('checked', false);
                    updateBulkActions();
                    table.draw();
                    refreshPdfStats();
                }).fail(function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Bulk delete failed.'));
                });
            });

            // Convert to CRM Profile
            $(document).on('click', '.convert_to_profile_btn', function() {
                var id = $(this).data('id');
                $.post("{{ url('scraper/pdf') }}/" + id + "/convert-profile", function(res) {
                    alert(res.message);
                    table.draw();
                    refreshPdfStats();
                }).fail(function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message ||
                        'Failed to convert to Profile.'));
                });
            });

            // Convert to CRM Organization
            $(document).on('click', '.convert_to_org_btn', function() {
                var id = $(this).data('id');
                $.post("{{ url('scraper/pdf') }}/" + id + "/convert-org", function(res) {
                    alert(res.message);
                    table.draw();
                    refreshPdfStats();
                }).fail(function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message ||
                        'Failed to convert to Organization.'));
                });
            });
        });
    </script>
@endsection
