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
            <a href="{{ route('scraper.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-brand bg-brand/10 dark:bg-brand/20 transition">
                <i class="bi bi-globe2"></i>
                Web Scraper (HTML)
            </a>
            <a href="{{ route('scraper.pdf') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-brand hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                <i class="bi bi-file-earmark-pdf-fill"></i>
                Static PDF Scraper (Directory Harvester)
            </a>
        </div>

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2.5">
                    <span class="inline-flex p-2 rounded-xl bg-brand/10 text-brand dark:bg-brand/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                            <line x1="2" y1="12" x2="22" y2="12" stroke-width="2"/>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" stroke-width="2"/>
                        </svg>
                    </span>
                    Web Scraper & Lead Harvester
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Extract business names, contact persons, phone numbers, categories, and emails using our standalone Guzzle scraper.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    100% Free Guzzle Engine Active
                </span>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="p-3 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400">
                    <i class="bi bi-database-fill-check fs-4"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Total Scraped</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100" id="statTotal">{{ $stats['total'] }}</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="p-3 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">New Leads</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100" id="statNew">{{ $stats['new'] }}</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="p-3 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <i class="bi bi-person-check-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Converted to CRM</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100" id="statConverted">{{ $stats['converted'] }}</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="p-3 rounded-lg bg-purple-500/10 text-purple-600 dark:text-purple-400">
                    <i class="bi bi-globe2 fs-4"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Unique Sources</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100" id="statSources">{{ $stats['sources'] }}</div>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- SCRAPER CONTROLLER BOX                                    -->
        <!-- ========================================================= -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 shadow-md p-5 sm:p-6">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-brand/10 text-brand flex items-center justify-center">
                        <i class="bi bi-cloud-arrow-down-fill fs-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">Scrape a Webpage</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Paste any URL or choose a preset like SCCI Importers to extract directory cards.</p>
                    </div>
                </div>
            </div>

            <form id="scraperForm" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Preset Selection -->
                    <div class="md:col-span-4">
                        <label for="scraperPreset" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Directory Presets
                        </label>
                        <select id="scraperPreset" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-brand focus:border-brand shadow-sm">
                            <option value="https://www.scci.org.pk/importers" data-name="SCCI Importers" selected>
                                🏢 SCCI Importers (Sargodha Chamber)
                            </option>
                            <option value="https://www.scci.org.pk/exporters" data-name="SCCI Exporters">
                                🚢 SCCI Exporters (Sargodha Chamber)
                            </option>
                            <option value="{{ route('scraper.pdf') }}" data-name="SCCI Citrus PDF" data-type="pdf">
                                📄 SCCI Citrus Exporters (PDF Directory) ➔
                            </option>
                            <option value="custom" data-name="">
                                🌐 Custom URL / Other Directory
                            </option>
                        </select>
                    </div>

                    <!-- Target URL -->
                    <div class="md:col-span-5">
                        <label for="targetUrl" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Target Website URL <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="bi bi-link-45deg fs-5"></i>
                            </span>
                            <input type="url" id="targetUrl" name="url" required
                                value="https://www.scci.org.pk/importers"
                                placeholder="https://www.example.com/directory"
                                class="w-full pl-9 text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-brand focus:border-brand shadow-sm font-mono" />
                        </div>
                        <span id="err_url" class="text-xs text-red-500 mt-1 block font-medium"></span>
                    </div>

                    <!-- Source Label -->
                    <div class="md:col-span-3">
                        <label for="sourceName" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Source Name
                        </label>
                        <input type="text" id="sourceName" name="source_name"
                            value="SCCI Importers"
                            placeholder="e.g. SCCI Importers"
                            class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-brand focus:border-brand shadow-sm" />
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        <i class="bi bi-info-circle me-1 text-brand"></i>
                        Scraping extracts Company Name, Contact Person, Phone, Email, and Category into your CRM.
                    </div>

                    <button type="submit" id="startScrapeBtn"
                        class="inline-flex items-center justify-center gap-2 bg-brand hover:bg-brand/90 text-white font-semibold text-sm px-6 py-2.5 rounded-lg shadow-sm transition disabled:opacity-50">
                        <i class="bi bi-play-circle-fill"></i>
                        <span id="startScrapeBtnText">Scrape Website Now</span>
                    </button>
                </div>
            </form>

            <!-- Dynamic Result Alert Box -->
            <div id="scrapeResultAlert" class="hidden mt-4 p-4 rounded-xl text-sm transition"></div>
        </div>

        <!-- ========================================================= -->
        <!-- FILTER BAR & BULK ACTIONS                                 -->
        <!-- ========================================================= -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Status Filter -->
                <div class="flex items-center gap-2">
                    <label for="filterStatus" class="text-xs font-semibold text-gray-500 dark:text-gray-400">Status:</label>
                    <select id="filterStatus" class="text-xs rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 py-1.5">
                        <option value="">All Statuses</option>
                        @foreach ($statuses as $val => $lbl)
                            <option value="{{ $val }}">{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Source Filter -->
                <div class="flex items-center gap-2">
                    <label for="filterSource" class="text-xs font-semibold text-gray-500 dark:text-gray-400">Source:</label>
                    <select id="filterSource" class="text-xs rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 py-1.5">
                        <option value="">All Sources</option>
                        @foreach ($sources as $src)
                            <option value="{{ $src }}">{{ $src }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="button" id="resetFiltersBtn" class="text-xs text-brand hover:underline">
                    Reset Filters
                </button>
            </div>

            <!-- Bulk Delete Action -->
            <div class="flex items-center gap-2">
                <button type="button" id="bulkDeleteBtn" disabled
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/40 border border-red-200 dark:border-red-800 px-3 py-1.5 rounded-lg transition disabled:opacity-40 disabled:cursor-not-allowed">
                    <i class="bi bi-trash3-fill"></i>
                    <span>Delete Selected</span>
                    <span id="selectedCountBadge" class="hidden px-1.5 py-0.2 bg-red-600 text-white rounded-full text-[10px]">0</span>
                </button>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- SCRAPED RECORDS DATATABLE                                 -->
        <!-- ========================================================= -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 overflow-x-auto">
                <table id="scraperTable" class="w-full text-sm text-left text-gray-700 dark:text-gray-200 min-w-[900px]">
                    <colgroup>
                        <col style="width: 3%;">
                        <col style="width: 4%;">
                        <col style="width: 24%;">
                        <col style="width: 16%;">
                        <col style="width: 17%;">
                        <col style="width: 14%;">
                        <col style="width: 10%;">
                        <col style="width: 12%;">
                    </colgroup>
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-3 text-center">
                                <input type="checkbox" id="selectAllCheckbox" class="rounded border-gray-300 text-brand focus:ring-brand">
                            </th>
                            <th class="px-3 py-3">#</th>
                            <th class="px-3 py-3">Business & Category</th>
                            <th class="px-3 py-3">Contact Person</th>
                            <th class="px-3 py-3">Channels (Phone / Email)</th>
                            <th class="px-3 py-3">Source & Link</th>
                            <th class="px-3 py-3">Status</th>
                            <th class="px-3 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <!-- Populated by Yajra DataTables AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- VIEW DETAILS MODAL                                        -->
    <!-- ========================================================= -->
    <div class="modal fade" id="viewScrapedModal" tabindex="-1" aria-labelledby="viewScrapedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow-2xl border-0 rounded-4 overflow-hidden">
                <div class="modal-header px-4 py-3 bg-light-subtle border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-3 d-flex align-items-center justify-content-center text-white"
                            style="width: 40px; height: 40px; background: linear-gradient(135deg, #5b3fd4, #7952eb);">
                            <i class="bi bi-file-earmark-text fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-dark" id="viewScrapedModalLabel">Scraped Record Details</h5>
                            <small class="text-muted">Extracted metadata and CRM conversion controls.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4" id="viewScrapedContent">
                    <!-- Populated via AJAX -->
                </div>

                <div class="modal-footer px-4 py-3 bg-light-subtle border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3" data-bs-dismiss="modal">
                        Close
                    </button>
                    <div class="d-flex align-items-center gap-2" id="viewModalConversionActions">
                        <!-- Action buttons injected dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- EDIT RECORD MODAL                                         -->
    <!-- ========================================================= -->
    <div class="modal fade" id="editScrapedModal" tabindex="-1" aria-labelledby="editScrapedModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <form id="editScrapedForm" class="modal-content shadow-2xl border-0 rounded-4 overflow-hidden mb-0">
                @csrf
                <input type="hidden" id="edit_record_id" name="id">

                <div class="modal-header px-4 py-3 bg-light-subtle border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-3 d-flex align-items-center justify-content-center text-white"
                            style="width: 40px; height: 40px; background: linear-gradient(135deg, #5b3fd4, #7952eb);">
                            <i class="bi bi-pencil-square fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-dark" id="editScrapedModalLabel">Edit Scraped Record</h5>
                            <small class="text-muted">Update details and qualification.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Business / Company Name</label>
                            <input type="text" id="edit_business_name" name="business_name" class="form-control rounded-3" placeholder="e.g. Bhali Tyres">
                            <span id="err_edit_business_name" class="text-danger small mt-1 d-block"></span>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Contact Person</label>
                            <input type="text" id="edit_contact_person" name="contact_person" class="form-control rounded-3" placeholder="e.g. Muhammad Imran Bhali">
                            <span id="err_edit_contact_person" class="text-danger small mt-1 d-block"></span>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Primary Phone</label>
                            <input type="text" id="edit_phone" name="phone" class="form-control rounded-3 font-monospace" placeholder="e.g. 0300-8703600">
                            <span id="err_edit_phone" class="text-danger small mt-1 d-block"></span>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Secondary Phone</label>
                            <input type="text" id="edit_phone_2" name="phone_2" class="form-control rounded-3 font-monospace" placeholder="e.g. 0321-2221627">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Email Address</label>
                            <input type="email" id="edit_email" name="email" class="form-control rounded-3" placeholder="e.g. info@example.com">
                            <span id="err_edit_email" class="text-danger small mt-1 d-block"></span>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Category / Importer Of</label>
                            <input type="text" id="edit_category" name="category" class="form-control rounded-3" placeholder="e.g. Tyres & Tubes">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Website / Domain</label>
                            <input type="text" id="edit_website_domain" name="website_domain" class="form-control rounded-3" placeholder="e.g. company.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Status <span class="text-danger">*</span></label>
                            <select id="edit_status" name="status" class="form-select rounded-3">
                                @foreach ($statuses as $val => $lbl)
                                    <option value="{{ $val }}">{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Physical Address</label>
                            <textarea id="edit_address" name="address" rows="2" class="form-control rounded-3" placeholder="Headquarters or branch address..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Internal Notes</label>
                            <textarea id="edit_notes" name="notes" rows="2" class="form-control rounded-3" placeholder="Follow-up notes, verification comments..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer px-4 py-3 bg-light-subtle border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="saveEditBtn" class="btn btn-sm btn-primary rounded-3 px-4 d-inline-flex align-items-center gap-1.5"
                        style="background-color: #5b3fd4; border-color: #5b3fd4;">
                        <i class="bi bi-check2-circle"></i>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- JAVASCRIPT DEPENDENCIES (Direct in section for 100% execution) -->
    <!-- ========================================================= -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            // Setup CSRF header
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            // Modals
            var viewModal = new bootstrap.Modal(document.getElementById('viewScrapedModal'));
            var editModal = new bootstrap.Modal(document.getElementById('editScrapedModal'));

            // Preset handler
            $('#scraperPreset').on('change', function() {
                var selected = $(this).find(':selected');
                var val = $(this).val();
                var name = selected.data('name');
                var type = selected.data('type');

                if (type === 'pdf') {
                    window.location.href = val;
                    return;
                }

                if (val !== 'custom') {
                    $('#targetUrl').val(val);
                    $('#sourceName').val(name);
                } else {
                    $('#targetUrl').val('').focus();
                    $('#sourceName').val('');
                }
            });

            // DataTables
            var table = $('#scraperTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("scraper.data") }}',
                    data: function(d) {
                        d.status_filter = $('#filterStatus').val();
                        d.source_filter = $('#filterSource').val();
                    }
                },
                columns: [
                    { data: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-muted text-xs' },
                    { data: 'business_info', name: 'business_name' },
                    { data: 'contact_person_display', name: 'contact_person' },
                    { data: 'contact_channels', name: 'phone' },
                    { data: 'source_badge', name: 'source_name' },
                    { data: 'status_badge', name: 'status' },
                    { data: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[1, 'desc']],
                pageLength: 15,
                lengthMenu: [10, 15, 25, 50, 100],
                language: {
                    emptyTable: '<div class="py-8 text-center text-gray-400"><i class="bi bi-inbox fs-2 d-block mb-2"></i>No scraped records found. Launch the scraper above to extract records!</div>',
                    search: "_INPUT_",
                    searchPlaceholder: "Search records...",
                    processing: '<div class="flex items-center justify-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
                },
                drawCallback: function() {
                    $('#selectAllCheckbox').prop('checked', false);
                    updateBulkDeleteButton();
                }
            });

            // Filters
            $('#filterStatus, #filterSource').on('change', function() {
                table.ajax.reload();
            });

            $('#resetFiltersBtn').on('click', function() {
                $('#filterStatus').val('');
                $('#filterSource').val('');
                table.ajax.reload();
            });

            // Scraper Trigger
            $('#scraperForm').on('submit', function(e) {
                e.preventDefault();
                $('#err_url').text('');
                $('#scrapeResultAlert').addClass('hidden');

                var $btn = $('#startScrapeBtn');
                var $btnText = $('#startScrapeBtnText');
                var originalText = $btnText.text();

                $btn.prop('disabled', true);
                $btnText.html('<span class="spinner-border spinner-border-sm me-1.5" role="status" aria-hidden="true"></span> Scraping with Guzzle...');

                $.ajax({
                    url: '{{ route("scraper.run") }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.status) {
                            var alertClass = 'bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800';
                            $('#scrapeResultAlert')
                                .removeClass('hidden')
                                .attr('class', alertClass + ' p-4 rounded-xl flex items-center justify-between shadow-sm')
                                .html(`
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-check-circle-fill fs-5 text-emerald-600"></i>
                                        <div>
                                            <div class="font-bold">${res.message}</div>
                                            <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">Found: ${res.records_found} records | Saved: ${res.records_imported}</div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close small" onclick="$('#scrapeResultAlert').addClass('hidden')"></button>
                                `);

                            table.ajax.reload();
                            refreshStats();
                        } else {
                            showScrapeError(res.message || 'Scraping failed.');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON?.errors?.url) {
                            $('#err_url').text(xhr.responseJSON.errors.url[0]);
                        } else {
                            showScrapeError(xhr.responseJSON?.message || 'Error occurred while executing scraper.');
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        $btnText.text(originalText);
                    }
                });
            });

            function showScrapeError(msg) {
                var alertClass = 'bg-red-50 text-red-800 border border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800';
                $('#scrapeResultAlert')
                    .removeClass('hidden')
                    .attr('class', alertClass + ' p-4 rounded-xl flex items-center justify-between shadow-sm')
                    .html(`
                        <div class="flex items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill fs-5 text-red-600"></i>
                            <div><strong>Scraper Error:</strong> ${msg}</div>
                        </div>
                        <button type="button" class="btn-close small" onclick="$('#scrapeResultAlert').addClass('hidden')"></button>
                    `);
            }

            function refreshStats() {
                $.get('{{ route("scraper.index") }}', function(html) {
                    var $parsed = $('<div>').html(html);
                    $('#statTotal').text($parsed.find('#statTotal').text());
                    $('#statNew').text($parsed.find('#statNew').text());
                    $('#statConverted').text($parsed.find('#statConverted').text());
                    $('#statSources').text($parsed.find('#statSources').text());
                });
            }

            // View Details Modal
            $(document).on('click', '.view_scraped_btn', function() {
                var id = $(this).data('id');

                $.ajax({
                    url: '/scraper/' + id + '/show',
                    type: 'GET',
                    success: function(res) {
                        if (res.status && res.record) {
                            var r = res.record;
                            var rawDataHtml = '';

                            if (r.raw_data && Object.keys(r.raw_data).length > 0) {
                                rawDataHtml = '<div class="mt-3 pt-3 border-top"><h6 class="small fw-bold text-secondary text-uppercase mb-2"><i class="bi bi-code-slash me-1"></i> Discovered Extra Attributes</h6><div class="row g-2">';
                                for (var k in r.raw_data) {
                                    var val = r.raw_data[k];
                                    if (typeof val === 'object') val = JSON.stringify(val);
                                    rawDataHtml += `
                                        <div class="col-sm-6">
                                            <div class="p-2 rounded bg-body-tertiary border text-xs">
                                                <span class="text-muted d-block text-capitalize">${k}:</span>
                                                <span class="fw-semibold text-dark text-break">${val}</span>
                                            </div>
                                        </div>
                                    `;
                                }
                                rawDataHtml += '</div></div>';
                            }

                            var html = `
                                <div class="card border-0 shadow-sm rounded-4 p-3 mb-3 bg-light-subtle">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <h4 class="fw-bold mb-0 text-dark">${r.business_name || 'Business Record'}</h4>
                                            ${r.category ? '<span class="badge bg-primary-subtle text-primary border px-2.5 py-1 rounded-pill">' + r.category + '</span>' : ''}
                                        </div>
                                        <span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1 rounded-pill">Status: ${r.status}</span>
                                    </div>
                                    <div class="text-muted small">
                                        <span>Extracted from: </span>
                                        ${r.source_url ? '<a href="' + r.source_url + '" target="_blank" class="text-decoration-none fw-medium text-primary"><i class="bi bi-box-arrow-up-right me-1"></i>' + (r.source_name || r.source_url) + '</a>' : r.source_name}
                                        <span class="mx-1">•</span>
                                        <span>Scraped on ${r.created_at}</span>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-light-subtle">
                                            <h6 class="small fw-bold text-uppercase text-secondary mb-3"><i class="bi bi-person-lines-fill me-1"></i> Identity & Contact</h6>
                                            <div class="d-flex flex-column gap-2 small">
                                                <div class="d-flex justify-content-between p-2 rounded bg-body">
                                                    <span class="text-muted">Contact Person</span>
                                                    <span class="fw-bold text-dark">${r.contact_person || '—'}</span>
                                                </div>
                                                <div class="d-flex justify-content-between p-2 rounded bg-body">
                                                    <span class="text-muted">Primary Phone</span>
                                                    <span class="fw-bold font-monospace text-dark">${r.phone || '—'}</span>
                                                </div>
                                                <div class="d-flex justify-content-between p-2 rounded bg-body">
                                                    <span class="text-muted">Secondary Phone</span>
                                                    <span class="fw-semibold font-monospace text-dark">${r.phone_2 || '—'}</span>
                                                </div>
                                                <div class="d-flex justify-content-between p-2 rounded bg-body">
                                                    <span class="text-muted">Email</span>
                                                    <span class="fw-semibold text-primary">${r.email ? '<a href="mailto:' + r.email + '">' + r.email + '</a>' : '—'}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-light-subtle">
                                            <h6 class="small fw-bold text-uppercase text-secondary mb-3"><i class="bi bi-geo-alt me-1"></i> Location & Web</h6>
                                            <div class="d-flex flex-column gap-2 small">
                                                <div class="d-flex justify-content-between p-2 rounded bg-body">
                                                    <span class="text-muted">Website</span>
                                                    <span class="fw-medium text-dark">${r.website_domain ? '<a href="' + (r.website_domain.startsWith('http') ? r.website_domain : 'https://' + r.website_domain) + '" target="_blank">' + r.website_domain + '</a>' : '—'}</span>
                                                </div>
                                                <div class="p-2 rounded bg-body">
                                                    <span class="text-muted d-block mb-1">Address</span>
                                                    <span class="fw-medium text-dark">${r.address || 'No physical address provided.'}</span>
                                                </div>
                                                ${r.notes ? '<div class="p-2 rounded bg-body"><span class="text-muted d-block mb-1">Notes:</span><span class="text-dark">' + r.notes + '</span></div>' : ''}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                ${rawDataHtml}
                            `;

                            $('#viewScrapedContent').html(html);

                            var conversionBtns = `
                                <button type="button" class="btn btn-sm btn-outline-success rounded-3 convert_to_profile_btn" data-id="${r.id}">
                                    <i class="bi bi-person-plus-fill me-1"></i> Convert to Profile
                                </button>
                                <button type="button" class="btn btn-sm btn-success rounded-3 convert_to_org_btn" data-id="${r.id}">
                                    <i class="bi bi-building-add me-1"></i> Convert to Organization
                                </button>
                            `;
                            $('#viewModalConversionActions').html(conversionBtns);

                            viewModal.show();
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Failed to load scraped record.');
                    }
                });
            });

            // Edit Record Modal
            $(document).on('click', '.edit_scraped_btn', function() {
                var id = $(this).data('id');
                $('.text-danger').text('');

                $.ajax({
                    url: '/scraper/' + id + '/edit',
                    type: 'GET',
                    success: function(res) {
                        if (res.status && res.record) {
                            var r = res.record;
                            $('#edit_record_id').val(r.id);
                            $('#edit_business_name').val(r.business_name || '');
                            $('#edit_contact_person').val(r.contact_person || '');
                            $('#edit_phone').val(r.phone || '');
                            $('#edit_phone_2').val(r.phone_2 || '');
                            $('#edit_email').val(r.email || '');
                            $('#edit_category').val(r.category || '');
                            $('#edit_website_domain').val(r.website_domain || '');
                            $('#edit_status').val(r.status || 'new');
                            $('#edit_address').val(r.address || '');
                            $('#edit_notes').val(r.notes || '');

                            editModal.show();
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Failed to load record for editing.');
                    }
                });
            });

            $('#editScrapedForm').on('submit', function(e) {
                e.preventDefault();
                $('.text-danger').text('');
                var id = $('#edit_record_id').val();
                var $btn = $('#saveEditBtn');

                $btn.prop('disabled', true);

                $.ajax({
                    url: '/scraper/' + id + '/update',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.status) {
                            editModal.hide();
                            table.ajax.reload(null, false);
                            refreshStats();
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            var errors = xhr.responseJSON.errors;
                            for (var f in errors) {
                                $('#err_edit_' + f).text(errors[f][0]);
                            }
                        } else {
                            alert(xhr.responseJSON?.message || 'Failed to update record.');
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            });

            // Delete Record
            $(document).on('click', '.delete_scraped_btn', function() {
                var id = $(this).data('id');

                if (!confirm('Are you sure you want to delete this scraped record?')) {
                    return;
                }

                $.ajax({
                    url: '/scraper/' + id,
                    type: 'DELETE',
                    success: function(res) {
                        if (res.status) {
                            table.ajax.reload(null, false);
                            refreshStats();
                        } else {
                            alert(res.message || 'Failed to delete record.');
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Delete request failed.');
                    }
                });
            });

            // Bulk Delete
            $('#selectAllCheckbox').on('change', function() {
                var checked = $(this).is(':checked');
                $('.record-checkbox').prop('checked', checked);
                updateBulkDeleteButton();
            });

            $(document).on('change', '.record-checkbox', function() {
                updateBulkDeleteButton();
            });

            function updateBulkDeleteButton() {
                var count = $('.record-checkbox:checked').length;
                if (count > 0) {
                    $('#bulkDeleteBtn').prop('disabled', false);
                    $('#selectedCountBadge').text(count).removeClass('hidden');
                } else {
                    $('#bulkDeleteBtn').prop('disabled', true);
                    $('#selectedCountBadge').text('0').addClass('hidden');
                }
            }

            $('#bulkDeleteBtn').on('click', function() {
                var ids = [];
                $('.record-checkbox:checked').each(function() {
                    ids.push($(this).val());
                });

                if (ids.length === 0) return;

                if (!confirm(`Are you sure you want to delete ${ids.length} selected records?`)) {
                    return;
                }

                $.ajax({
                    url: '{{ route("scraper.bulk-delete") }}',
                    type: 'POST',
                    data: { ids: ids },
                    success: function(res) {
                        if (res.status) {
                            table.ajax.reload(null, false);
                            refreshStats();
                        } else {
                            alert(res.message || 'Bulk delete failed.');
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Bulk delete failed.');
                    }
                });
            });

            // Conversions to Profile / Organization
            $(document).on('click', '.convert_to_profile_btn', function(e) {
                e.preventDefault();
                var id = $(this).data('id');

                if (!confirm('Convert this scraped record into a CRM Profile?')) {
                    return;
                }

                $.ajax({
                    url: '/scraper/' + id + '/convert-profile',
                    type: 'POST',
                    success: function(res) {
                        if (res.status) {
                            alert(res.message);
                            if ($('#viewScrapedModal').hasClass('show')) {
                                viewModal.hide();
                            }
                            table.ajax.reload(null, false);
                            refreshStats();
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Conversion to Profile failed.');
                    }
                });
            });

            $(document).on('click', '.convert_to_org_btn', function(e) {
                e.preventDefault();
                var id = $(this).data('id');

                if (!confirm('Convert this scraped record into a CRM Organization?')) {
                    return;
                }

                $.ajax({
                    url: '/scraper/' + id + '/convert-org',
                    type: 'POST',
                    success: function(res) {
                        if (res.status) {
                            alert(res.message);
                            if ($('#viewScrapedModal').hasClass('show')) {
                                viewModal.hide();
                            }
                            table.ajax.reload(null, false);
                            refreshStats();
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Conversion to Organization failed.');
                    }
                });
            });
        });
    </script>
@endsection
