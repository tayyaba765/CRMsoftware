@extends('backend.layouts.master')

@section('content')
    <!-- Bootstrap 5.3 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />

    <!-- Leaflet Map CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- ========================================================= -->
    <!-- OUTER CONTAINER (Tailwind CSS - Preserved Exactly)        -->
    <!-- ========================================================= -->
    <div id="slot-content" class="max-w-7xl mx-auto p-6">

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Organizations Management</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Manage corporate accounts, organizational tiers, status, assigned staff, sources, and focal persons.
                </p>
            </div>
            <button type="button" id="createOrgBtn"
                class="inline-flex items-center gap-2 bg-brand hover:bg-brand/90 text-white font-medium px-4 py-2.5 rounded-lg shadow-sm transition">
                <span class="text-lg leading-none">+</span> Add Organization
            </button>
        </div>

        <!-- DataTable Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
            <div class="p-4 overflow-x-auto">
                <table id="orgsTable" class="w-full text-sm text-left text-gray-700 dark:text-gray-200 min-w-[850px]">
                    <colgroup>
                        <col style="width: 4%;">
                        <col style="width: 22%;">
                        <col style="width: 12%;">
                        <col style="width: 14%;">
                        <col style="width: 11%;">
                        <col style="width: 14%;">
                        <col style="width: 11%;">
                        <col style="width: 12%;">
                    </colgroup>
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-3">#</th>
                            <th class="px-3 py-3">Organization</th>
                            <th class="px-3 py-3">Status</th>
                            <th class="px-3 py-3">Assigned To</th>
                            <th class="px-3 py-3">Source</th>
                            <th class="px-3 py-3">Ownership & Size</th>
                            <th class="px-3 py-3">Tier & Type</th>
                            <th class="px-3 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <!-- Populated via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- CREATE / EDIT ORGANIZATION MODAL (Bootstrap 5 Clean Form) -->
    <!-- ========================================================= -->
    <div class="modal fade" id="orgModal" tabindex="-1" aria-labelledby="orgModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form id="orgForm" enctype="multipart/form-data" class="modal-content shadow-2xl border-0 rounded-4 overflow-hidden mb-0">
                @csrf
                <input type="hidden" id="org_id" name="org_id">

                <!-- Modal Header -->
                <div class="modal-header px-4 py-3 bg-light-subtle border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0"
                            style="width: 42px; height: 42px; background: linear-gradient(135deg, #5b3fd4, #7952eb);">
                            <i class="bi bi-buildings fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-dark" id="orgModalLabel">Add Organization</h5>
                            <small class="text-muted">Configure corporate profile, pipeline status, assigned staff, map location, and contacts.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close" id="closeOrgModalBtn"></button>
                </div>

                <!-- Modal Body (Simplified Clean Layout) -->
                <div class="modal-body p-4">
                    <div class="row g-4">

                        <!-- LEFT COLUMN: Logo, Status, Assignment & Classification -->
                        <div class="col-lg-4">
                            <!-- Logo Upload Box -->
                            <div class="bg-light-subtle rounded-4 p-3 text-center border mb-3">
                                <div class="position-relative d-inline-block mx-auto mb-2">
                                    <img id="logoPreview"
                                        src="https://ui-avatars.com/api/?name=Org&background=5b3fd4&color=fff&rounded=true"
                                        alt="Logo preview"
                                        class="rounded-3 object-fit-cover shadow-sm border border-2 border-white bg-white"
                                        style="width: 100px; height: 100px;" />
                                    <label for="logo"
                                        class="position-absolute bottom-0 end-0 text-white rounded-circle shadow cursor-pointer d-flex align-items-center justify-content-center hover-scale"
                                        style="width: 32px; height: 32px; background-color: #5b3fd4;"
                                        title="Click to upload corporate logo">
                                        <i class="bi bi-camera-fill small"></i>
                                    </label>
                                </div>

                                <h6 class="fw-bold mb-1 text-dark text-truncate" id="orgNamePreview">Organization Logo</h6>
                                <span class="text-muted extra-small d-block mb-2">PNG, JPG, WEBP, SVG up to 3MB</span>

                                <input type="file" id="logo" name="logo" accept="image/*" class="d-none">
                                <button type="button" class="btn btn-sm btn-outline-brand w-100 rounded-pill py-1.5 fw-medium"
                                    onclick="document.getElementById('logo').click();">
                                    <i class="bi bi-upload me-1"></i> Choose Logo
                                </button>
                                <div class="text-danger small mt-1 text-start" id="err_logo"></div>
                            </div>

                            <!-- Status & Assignment Box -->
                            <div class="bg-light-subtle rounded-4 p-3 border">
                                <h6 class="fw-bold text-dark small mb-3 pb-2 border-bottom d-flex align-items-center gap-1.5">
                                    <i class="bi bi-shield-check text-brand"></i> Status & Pipeline
                                </h6>

                                <!-- Account Status -->
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary mb-1">
                                        Account Status <span class="text-danger">*</span>
                                    </label>
                                    <select id="status" name="status" class="form-select rounded-3 shadow-none" required>
                                        @foreach ($statuses as $statusKey => $statusLabel)
                                            <option value="{{ $statusKey }}">{{ $statusLabel }}</option>
                                        @endforeach
                                    </select>
                                    <div class="text-danger small mt-1" id="err_status"></div>
                                </div>

                                <!-- Assigned Staff -->
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary mb-1">
                                        Assigned Staff <span class="text-muted fw-normal">(Sales/Lead)</span>
                                    </label>
                                    <select id="assigned_to" name="assigned_to" class="form-select rounded-3 shadow-none">
                                        <option value="">-- Unassigned --</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                    <div class="text-danger small mt-1" id="err_assigned_to"></div>
                                </div>

                                <!-- Acquisition Source -->
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary mb-1">
                                        Acquisition Source <span class="text-danger">*</span>
                                    </label>
                                    <select id="source" name="source" class="form-select rounded-3 shadow-none" required>
                                        @foreach ($sources as $sourceItem)
                                            <option value="{{ $sourceItem }}">{{ $sourceItem }}</option>
                                        @endforeach
                                    </select>
                                    <div class="text-danger small mt-1" id="err_source"></div>
                                </div>

                                <!-- Tier Level -->
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary mb-1">
                                        Tier Level <span class="text-danger">*</span>
                                    </label>
                                    <select id="tier" name="tier" class="form-select rounded-3 shadow-none" required>
                                        @foreach ($tiers as $tierOption)
                                            <option value="{{ $tierOption }}">{{ $tierOption }}</option>
                                        @endforeach
                                    </select>
                                    <div class="text-danger small mt-1" id="err_tier"></div>
                                </div>

                                <!-- Ownership Type -->
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary mb-1">
                                        Ownership Type <span class="text-danger">*</span>
                                    </label>
                                    <select id="ownership_type" name="ownership_type" class="form-select rounded-3 shadow-none" required>
                                        @foreach ($ownershipTypes as $ownKey => $ownLabel)
                                            <option value="{{ $ownKey }}">{{ $ownLabel }}</option>
                                        @endforeach
                                    </select>
                                    <div class="text-danger small mt-1" id="err_ownership_type"></div>
                                </div>

                                <!-- Organization Size -->
                                <div>
                                    <label class="form-label small fw-semibold text-secondary mb-1">
                                        Employee Headcount <span class="text-danger">*</span>
                                    </label>
                                    <select id="size" name="size" class="form-select rounded-3 shadow-none" required>
                                        @foreach ($sizes as $sizeItem)
                                            <option value="{{ $sizeItem }}">{{ $sizeItem }} employees</option>
                                        @endforeach
                                    </select>
                                    <div class="text-danger small mt-1" id="err_size"></div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: Corporate Info, Focal Person & Locations -->
                        <div class="col-lg-8">

                            <!-- SECTION 1: Corporate Profile & Industry -->
                            <div class="mb-4">
                                <div class="d-flex align-items-center gap-2 pb-2 mb-3 border-bottom">
                                    <i class="bi bi-buildings text-brand fs-5"></i>
                                    <h6 class="fw-bold mb-0 text-dark">1. Corporate Profile & Industry</h6>
                                </div>

                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">
                                            Organization Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="name" name="name" class="form-control rounded-3 shadow-none"
                                            placeholder="e.g. Acme Global Corporation" required>
                                        <div class="text-danger small mt-1" id="err_name"></div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">Website Domain</label>
                                        <div class="input-group shadow-none">
                                            <span class="input-group-text rounded-start-3 small text-muted">https://</span>
                                            <input type="text" id="website_domain" name="website_domain" class="form-control rounded-end-3 shadow-none"
                                                placeholder="example.com">
                                        </div>
                                        <div class="text-danger small mt-1" id="err_website_domain"></div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">Industry / Sector</label>
                                        <select id="organization_type" name="organization_type" class="form-select rounded-3 shadow-none">
                                            <option value="">-- Select Industry --</option>
                                            @foreach ($orgTypes as $orgType)
                                                <option value="{{ $orgType }}">{{ $orgType }}</option>
                                            @endforeach
                                        </select>
                                        <div class="text-danger small mt-1" id="err_organization_type"></div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">Annual Revenue (USD $)</label>
                                        <div class="input-group shadow-none">
                                            <span class="input-group-text rounded-start-3">$</span>
                                            <input type="number" step="0.01" min="0" id="revenue" name="revenue"
                                                class="form-control font-monospace rounded-end-3 shadow-none" placeholder="500000.00">
                                        </div>
                                        <div class="text-danger small mt-1" id="err_revenue"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 2: Focal Person & Communication Channels -->
                            <div class="mb-4">
                                <div class="d-flex align-items-center gap-2 pb-2 mb-3 border-bottom">
                                    <i class="bi bi-person-lines-fill text-brand fs-5"></i>
                                    <h6 class="fw-bold mb-0 text-dark">2. Key Focal Person & Contact</h6>
                                </div>

                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">Focal Person Name / Title</label>
                                        <input type="text" id="focal_person" name="focal_person" class="form-control rounded-3 shadow-none"
                                            placeholder="Alex Morgan (Director)">
                                        <div class="text-danger small mt-1" id="err_focal_person"></div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">
                                            Focal Person CNIC <span class="text-muted fw-normal">(13 digits)</span>
                                        </label>
                                        <input type="text" id="cnic" name="cnic" maxlength="13" class="form-control font-monospace rounded-3 shadow-none"
                                            placeholder="4210112345671">
                                        <div class="text-danger small mt-1" id="err_cnic"></div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">
                                            Primary Phone <span class="text-muted fw-normal">(11 digits)</span>
                                        </label>
                                        <input type="text" id="phone" name="phone" maxlength="11" class="form-control font-monospace rounded-3 shadow-none"
                                            placeholder="03001234567">
                                        <div class="text-danger small mt-1" id="err_phone"></div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">
                                            Secondary Phone <span class="text-muted fw-normal">(11 digits)</span>
                                        </label>
                                        <input type="text" id="phone_2" name="phone_2" maxlength="11" class="form-control font-monospace rounded-3 shadow-none"
                                            placeholder="03001234568">
                                        <div class="text-danger small mt-1" id="err_phone_2"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 3: Office Locations & Interactive Map -->
                            <div>
                                <div class="d-flex align-items-center gap-2 pb-2 mb-3 border-bottom">
                                    <i class="bi bi-geo-alt-fill text-brand fs-5"></i>
                                    <h6 class="fw-bold mb-0 text-dark">3. Registered Office Locations & Map</h6>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label small fw-semibold text-secondary mb-0">
                                            Headquarters / Main Office Address
                                        </label>
                                        <button type="button" id="locateOrgAddressBtn" class="btn btn-sm btn-link p-0 text-decoration-none text-brand fw-semibold">
                                            <i class="bi bi-search me-1"></i> Locate on Map
                                        </button>
                                    </div>
                                    <input type="text" id="address" name="address" class="form-control rounded-3 shadow-none"
                                        placeholder="100 Enterprise Way, Suite 400, City, State">
                                    <div class="text-danger small mt-1" id="err_address"></div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label small fw-semibold text-secondary mb-0">
                                            Branch / Secondary Facility Address
                                        </label>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input cursor-pointer shadow-none" type="checkbox" role="switch" id="org_same_as_above">
                                            <label class="form-check-label small fw-semibold text-brand cursor-pointer" for="org_same_as_above">
                                                Same as Headquarters
                                            </label>
                                        </div>
                                    </div>
                                    <input type="text" id="address_2" name="address_2" class="form-control rounded-3 shadow-none"
                                        placeholder="Secondary facility, annex, or warehouse">
                                    <div class="text-danger small mt-1" id="err_address_2"></div>
                                </div>

                                <!-- Map Viewbox Header & GPS Controls -->
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="small fw-semibold text-secondary d-flex align-items-center gap-1">
                                            <i class="bi bi-map text-brand"></i> Pin Location on Map
                                            <span class="text-muted fw-normal extra-small">(Click or drag marker)</span>
                                        </span>
                                        <button type="button" id="getOrgCurrentLocationBtn" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 shadow-none extra-small">
                                            <i class="bi bi-crosshair me-1"></i> Use My GPS
                                        </button>
                                    </div>

                                    <!-- Hidden Coordinates -->
                                    <input type="hidden" id="latitude" name="latitude">
                                    <input type="hidden" id="longitude" name="longitude">

                                    <!-- Leaflet Map Container -->
                                    <div id="orgMapViewBox" class="rounded-3 border overflow-hidden shadow-inner" style="height: 220px; width: 100%;"></div>

                                    <div class="d-flex align-items-center justify-content-between mt-2 pt-1">
                                        <div class="badge bg-white text-dark border shadow-xs px-3 py-1.5 font-monospace rounded-pill d-flex align-items-center gap-1.5" id="orgCoordsBadge">
                                            <i class="bi bi-geo-alt-fill text-danger"></i>
                                            <span id="orgCoordsText">No location selected (Click map to pin)</span>
                                        </div>
                                        <button type="button" id="clearOrgCoordsBtn" class="btn btn-sm btn-link text-danger text-decoration-none p-0 extra-small">
                                            <i class="bi bi-trash me-1"></i> Clear Pin
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer px-4 py-3 bg-light-subtle border-top d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light px-4 rounded-3 fw-medium" data-bs-dismiss="modal" id="cancelOrgBtn">
                        Cancel
                    </button>
                    <button type="submit" id="saveOrgBtn" class="btn btn-brand px-4 rounded-3 shadow-sm d-inline-flex align-items-center gap-2 fw-medium">
                        <i class="bi bi-check2-circle fs-6"></i>
                        <span>Save Organization</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- VIEW ORGANIZATION DETAILS MODAL (Bootstrap 5 Clean Dossier)-->
    <!-- ========================================================= -->
    <div class="modal fade" id="viewOrgModal" tabindex="-1" aria-labelledby="viewOrgModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow-2xl border-0 rounded-4 overflow-hidden">
                <div class="modal-header px-4 py-3 bg-light-subtle border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0"
                            style="width: 40px; height: 40px; background: linear-gradient(135deg, #5b3fd4, #7952eb);">
                            <i class="bi bi-building-check fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-dark" id="viewOrgModalLabel">Organization Details</h5>
                            <small class="text-muted">Corporate overview, pipeline status, assigned staff, coordinates, and contact details</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close" id="closeViewOrgModalBtn"></button>
                </div>

                <div class="modal-body p-4" id="viewOrgModalContent">
                    <!-- Populated dynamically via JS -->
                </div>

                <div class="modal-footer px-4 py-3 bg-light-subtle border-top d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary px-4 rounded-3 fw-medium" data-bs-dismiss="modal" id="closeViewOrgModalBtn2">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- JAVASCRIPT DEPENDENCIES                                   -->
    <!-- ========================================================= -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- ========================================================= -->
    <!-- APPLICATION LOGIC & EVENT HANDLERS                        -->
    <!-- ========================================================= -->
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            // ---------------------------------------------------------
            // Modal Instances
            // ---------------------------------------------------------
            var orgModalEl = document.getElementById('orgModal');
            var orgModal = new bootstrap.Modal(orgModalEl, {
                backdrop: 'static',
                keyboard: true
            });

            var viewOrgModalEl = document.getElementById('viewOrgModal');
            var viewOrgModal = new bootstrap.Modal(viewOrgModalEl, {
                keyboard: true
            });

            // ---------------------------------------------------------
            // 1. Initialize DataTable
            // ---------------------------------------------------------
            var table = $('#orgsTable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: '{{ route('organizations.data') }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false },
                    { data: 'org_info', name: 'name', orderable: true },
                    { data: 'status_badge', name: 'status', orderable: true },
                    { data: 'assigned_user_badge', name: 'assigned_to', orderable: true },
                    { data: 'source_badge', name: 'source', orderable: true },
                    { data: 'size_and_ownership', name: 'size', orderable: true },
                    { data: 'tier_badge', name: 'tier', orderable: true },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[1, 'asc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search organizations...",
                    lengthMenu: "Show _MENU_ entries"
                }
            });

            // ---------------------------------------------------------
            // 2. Leaflet Map Initialization for Form Viewbox
            // ---------------------------------------------------------
            // Ensure Leaflet default marker icons resolve reliably
            if (typeof L !== 'undefined' && L.Icon && L.Icon.Default) {
                delete L.Icon.Default.prototype._getIconUrl;
                L.Icon.Default.mergeOptions({
                    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                });
            }

            var orgFormMap = null;
            var orgFormMarker = null;
            var defaultLat = 31.5204;
            var defaultLng = 74.3587;

            function initOrUpdateOrgFormMap(lat, lng) {
                var targetLat = (lat !== null && lat !== undefined && lat !== '') ? parseFloat(lat) : defaultLat;
                var targetLng = (lng !== null && lng !== undefined && lng !== '') ? parseFloat(lng) : defaultLng;
                var hasCoords = (lat !== null && lat !== undefined && lat !== '' && lng !== null && lng !== undefined && lng !== '');

                if (!orgFormMap) {
                    orgFormMap = L.map('orgMapViewBox').setView([targetLat, targetLng], hasCoords ? 13 : 6);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(orgFormMap);

                    orgFormMap.on('click', function(e) {
                        setOrgFormMarker(e.latlng.lat, e.latlng.lng);
                    });
                } else {
                    orgFormMap.setView([targetLat, targetLng], hasCoords ? 13 : 6);
                }

                if (hasCoords) {
                    setOrgFormMarker(targetLat, targetLng);
                } else {
                    removeOrgFormMarker();
                }

                setTimeout(function() {
                    if (orgFormMap) orgFormMap.invalidateSize();
                }, 300);
            }

            function setOrgFormMarker(lat, lng) {
                var fixedLat = parseFloat(lat).toFixed(6);
                var fixedLng = parseFloat(lng).toFixed(6);

                $('#latitude').val(fixedLat);
                $('#longitude').val(fixedLng);
                $('#orgCoordsText').text(fixedLat + ', ' + fixedLng);

                if (orgFormMarker) {
                    orgFormMarker.setLatLng([lat, lng]);
                } else {
                    orgFormMarker = L.marker([lat, lng], { draggable: true }).addTo(orgFormMap);

                    orgFormMarker.on('dragend', function(e) {
                        var pos = e.target.getLatLng();
                        setOrgFormMarker(pos.lat, pos.lng);
                    });
                }
            }

            function removeOrgFormMarker() {
                if (orgFormMarker && orgFormMap) {
                    orgFormMap.removeLayer(orgFormMarker);
                    orgFormMarker = null;
                }
                $('#latitude').val('');
                $('#longitude').val('');
                $('#orgCoordsText').text('No location selected (Click map to pin)');
            }

            $('#clearOrgCoordsBtn').on('click', function() {
                removeOrgFormMarker();
            });

            // Geolocation button: Use My GPS
            $('#getOrgCurrentLocationBtn').on('click', function() {
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by your browser.');
                    return;
                }
                var btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Locating...');

                navigator.geolocation.getCurrentPosition(
                    function(pos) {
                        btn.prop('disabled', false).html('<i class="bi bi-crosshair me-1"></i> Use My GPS');
                        var userLat = pos.coords.latitude;
                        var userLng = pos.coords.longitude;
                        orgFormMap.setView([userLat, userLng], 14);
                        setOrgFormMarker(userLat, userLng);
                    },
                    function(err) {
                        btn.prop('disabled', false).html('<i class="bi bi-crosshair me-1"></i> Use My GPS');
                        alert('Could not retrieve your location: ' + err.message);
                    },
                    { timeout: 10000 }
                );
            });

            // Locate address using OpenStreetMap Nominatim
            $('#locateOrgAddressBtn').on('click', function() {
                var query = $('#address').val().trim();
                if (!query) {
                    alert('Please enter an address in "Headquarters Address" first to locate on map.');
                    $('#address').focus();
                    return;
                }

                var btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Searching...');

                $.getJSON('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query))
                    .done(function(data) {
                        btn.prop('disabled', false).html('<i class="bi bi-search me-1"></i> Locate on Map');
                        if (data && data.length > 0) {
                            var firstResult = data[0];
                            var lat = parseFloat(firstResult.lat);
                            var lon = parseFloat(firstResult.lon);
                            orgFormMap.setView([lat, lon], 14);
                            setOrgFormMarker(lat, lon);
                        } else {
                            alert('Location not found for "' + query + '". You can click directly on the map to pin.');
                        }
                    })
                    .fail(function() {
                        btn.prop('disabled', false).html('<i class="bi bi-search me-1"></i> Locate on Map');
                        alert('Could not query geocoding service. Please click directly on the map.');
                    });
            });

            $('#orgModal').on('shown.bs.modal', function() {
                if (orgFormMap) {
                    orgFormMap.invalidateSize();
                }
                setTimeout(function() {
                    if (orgFormMap) {
                        orgFormMap.invalidateSize();
                    }
                }, 150);
            });

            // ---------------------------------------------------------
            // 3. Digit-only enforcement for CNIC (13) and Phone (11)
            // ---------------------------------------------------------
            $('#cnic').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13);
            });

            $('#phone, #phone_2').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
            });

            // ---------------------------------------------------------
            // 4. Address "Same as above" logic
            // ---------------------------------------------------------
            $('#org_same_as_above').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#address_2').val($('#address').val()).prop('readonly', true).addClass('bg-body-secondary');
                } else {
                    $('#address_2').prop('readonly', false).removeClass('bg-body-secondary');
                }
            });

            $('#address').on('input', function() {
                if ($('#org_same_as_above').is(':checked')) {
                    $('#address_2').val($(this).val());
                }
            });

            // ---------------------------------------------------------
            // 5. Logo preview & Live Name Sync
            // ---------------------------------------------------------
            $('#logo').on('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(evt) {
                        $('#logoPreview').attr('src', evt.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });

            $('#name').on('input', function() {
                var val = $(this).val().trim();
                $('#orgNamePreview').text(val || 'Organization Logo');
            });

            // ---------------------------------------------------------
            // 6. Modal Helpers & Form Reset
            // ---------------------------------------------------------
            function clearOrgErrors() {
                $('[id^="err_"]').text('');
            }

            function resetOrgForm() {
                $('#orgForm')[0].reset();
                $('#org_id').val('');
                $('#logoPreview').attr('src', 'https://ui-avatars.com/api/?name=Org&background=5b3fd4&color=fff&rounded=true');
                $('#orgNamePreview').text('Organization Logo');
                $('#status').val('prospective');
                $('#assigned_to').val('');
                $('#source').val('WhatsApp');
                $('#tier').val('Tier 1');
                $('#ownership_type').val('private');
                $('#size').val('2-15');
                $('#organization_type').val('');
                $('#revenue').val('');
                $('#focal_person').val('');
                $('#cnic').val('');
                $('#phone').val('');
                $('#phone_2').val('');
                $('#address').val('');
                $('#address_2').val('');
                $('#org_same_as_above').prop('checked', false);
                $('#address_2').prop('readonly', false).removeClass('bg-body-secondary');
                clearOrgErrors();
                removeOrgFormMarker();
            }

            function openOrgModal(title) {
                $('#orgModalLabel').text(title);
                clearOrgErrors();
                CorkTheme.sync(orgModalEl);
                orgModal.show();
            }

            function closeOrgModal() {
                orgModal.hide();
                resetOrgForm();
            }

            // ---------------------------------------------------------
            // 7. Create Button Click
            // ---------------------------------------------------------
            $('#createOrgBtn').on('click', function() {
                resetOrgForm();
                openOrgModal('Add New Organization');
                initOrUpdateOrgFormMap(null, null);
            });

            // ---------------------------------------------------------
            // 8. Edit Button Click
            // ---------------------------------------------------------
            $(document).on('click', '.edit_org_btn', function() {
                var id = $(this).data('id');
                resetOrgForm();

                $.ajax({
                    url: '/organizations/' + id + '/edit',
                    type: 'GET',
                    success: function(res) {
                        if (res.status && res.organization) {
                            var o = res.organization;
                            $('#org_id').val(o.id);
                            $('#name').val(o.name);
                            $('#website_domain').val(o.website_domain || '');
                            $('#status').val(o.status || 'prospective');
                            $('#assigned_to').val(o.assigned_to || '');
                            $('#source').val(o.source || 'WhatsApp');
                            $('#size').val(o.size);
                            $('#ownership_type').val(o.ownership_type);
                            $('#organization_type').val(o.organization_type || '');
                            $('#revenue').val(o.revenue || '');
                            $('#tier').val(o.tier);
                            $('#focal_person').val(o.focal_person || '');
                            $('#cnic').val(o.cnic || '');
                            $('#phone').val(o.phone || '');
                            $('#phone_2').val(o.phone_2 || '');
                            $('#address').val(o.address || '');
                            $('#address_2').val(o.address_2 || '');

                            $('#orgNamePreview').text(o.name || 'Organization Logo');

                            if (o.address && o.address === o.address_2) {
                                $('#org_same_as_above').prop('checked', true);
                                $('#address_2').prop('readonly', true).addClass('bg-body-secondary');
                            }

                            if (o.logo_url) {
                                $('#logoPreview').attr('src', o.logo_url);
                            }

                            openOrgModal('Edit Organization — ' + o.name);
                            initOrUpdateOrgFormMap(o.latitude, o.longitude);
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Error loading organization details.');
                    }
                });
            });

            // ---------------------------------------------------------
            // 9. Save Form (Store / Update)
            // ---------------------------------------------------------
            $('#orgForm').on('submit', function(e) {
                e.preventDefault();
                clearOrgErrors();

                var orgId = $('#org_id').val();
                var isEdit = Boolean(orgId);
                var url = isEdit ? '/organizations/' + orgId + '/update' : '{{ route('organizations.store') }}';

                var formData = new FormData(this);
                $('#saveOrgBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1.5"></span> Saving...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.status) {
                            closeOrgModal();
                            table.ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            for (var field in errors) {
                                $('#err_' + field).text(errors[field][0]);
                            }
                        } else {
                            alert(xhr.responseJSON?.message || 'Failed to save organization.');
                        }
                    },
                    complete: function() {
                        $('#saveOrgBtn').prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> <span>Save Organization</span>');
                    }
                });
            });

            // ---------------------------------------------------------
            // 10. Delete Button Click
            // ---------------------------------------------------------
            $(document).on('click', '.delete_org_btn', function() {
                var id = $(this).data('id');

                if (!confirm('Are you sure you want to delete this organization? This cannot be undone.')) {
                    return;
                }

                $.ajax({
                    url: '/organizations/' + id,
                    type: 'DELETE',
                    success: function(res) {
                        if (res.status) {
                            table.ajax.reload(null, false);
                        } else {
                            alert(res.message || 'Failed to delete organization.');
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Delete request failed.');
                    }
                });
            });

            // ---------------------------------------------------------
            // 11. View Details & Map Modal
            // ---------------------------------------------------------
            var viewOrgMap = null;
            var viewOrgMarker = null;

            $(document).on('click', '.view_org_btn', function() {
                var id = $(this).data('id');

                $.ajax({
                    url: '/organizations/' + id + '/show',
                    type: 'GET',
                    success: function(res) {
                        if (res.status && res.organization) {
                            var o = res.organization;
                            var lat = o.latitude;
                            var lng = o.longitude;
                            var hasCoords = (lat && lng);

                            var tierBadgeClass = 'bg-primary text-white';
                            if (o.tier === 'Tier 1') {
                                tierBadgeClass = 'bg-success text-white';
                            } else if (o.tier === 'Tier 2') {
                                tierBadgeClass = 'bg-info text-dark';
                            } else if (o.tier === 'Tier 3') {
                                tierBadgeClass = 'bg-warning text-dark';
                            } else if (o.tier === 'Tier 4') {
                                tierBadgeClass = 'bg-secondary text-white';
                            }

                            var statusColors = {
                                'prospective': 'bg-amber-100 text-amber-800 border-amber-300',
                                'working': 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                'postponed': 'bg-purple-100 text-purple-800 border-purple-300',
                                'closed': 'bg-gray-100 text-gray-800 border-gray-300',
                            };
                            var stLower = (o.status || '').toLowerCase();
                            var stStyle = statusColors[stLower] || 'bg-light text-dark border';

                            var html = `
                                <!-- Hero Organization Card -->
                                <div class="card border-0 shadow-sm rounded-4 p-3 mb-3 bg-light-subtle">
                                    <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                                        <img src="${o.logo_url}" class="rounded-3 object-fit-cover shadow-sm border border-2 border-white bg-white"
                                            style="width: 76px; height: 76px; min-width: 76px;">
                                        <div class="text-center text-sm-start flex-grow-1">
                                            <div class="d-flex flex-wrap align-items-center gap-2 justify-content-center justify-content-sm-start mb-1">
                                                <h4 class="fw-bold mb-0 text-dark">${o.name}</h4>
                                                <span class="badge ${tierBadgeClass} px-2.5 py-1 rounded-pill">${o.tier}</span>
                                                <span class="badge bg-brand-subtle text-brand px-2.5 py-1 rounded-pill border">${o.ownership_type}</span>
                                            </div>
                                            <div class="text-muted small">
                                                ${o.website_domain ? '<a href="' + (o.website_domain.startsWith('http') ? o.website_domain : 'https://' + o.website_domain) + '" target="_blank" class="text-brand text-decoration-none fw-medium"><i class="bi bi-link-45deg"></i> ' + o.website_domain + '</a> • ' : ''}
                                                <span>Registered on ${o.created_at}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 mt-3 pt-2 border-top">
                                        <span class="badge px-3 py-1.5 rounded-pill border d-inline-flex align-items-center gap-1.5 ${stStyle}">
                                            <i class="bi bi-flag-fill"></i> Status: ${o.status}
                                        </span>
                                        <span class="badge bg-brand-subtle text-brand px-3 py-1.5 rounded-pill border d-inline-flex align-items-center gap-1.5">
                                            <i class="bi bi-person-check-fill"></i> Assigned: ${o.assigned_user}
                                        </span>
                                        <span class="badge bg-light text-dark px-3 py-1.5 rounded-pill border d-inline-flex align-items-center gap-1.5">
                                            <i class="bi bi-broadcast"></i> Source: ${o.source}
                                        </span>
                                    </div>
                                </div>

                                <!-- Corporate & Communication 2-Card Row -->
                                <div class="row g-3 mb-3">
                                    <!-- Corporate Profile Card -->
                                    <div class="col-md-6">
                                        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-light-subtle">
                                            <h6 class="text-uppercase fw-bold text-brand small mb-3 d-flex align-items-center gap-1.5">
                                                <i class="bi bi-buildings text-brand"></i> Corporate Profile
                                            </h6>
                                            <div class="d-flex flex-column gap-2 small">
                                                <div class="d-flex justify-content-between p-2 rounded bg-body">
                                                    <span class="text-muted">Industry / Sector</span>
                                                    <span class="fw-semibold text-dark">${o.organization_type}</span>
                                                </div>
                                                <div class="d-flex justify-content-between p-2 rounded bg-body">
                                                    <span class="text-muted">Employee Headcount</span>
                                                    <span class="fw-semibold text-dark">👥 ${o.size} employees</span>
                                                </div>
                                                <div class="d-flex justify-content-between p-2 rounded bg-body">
                                                    <span class="text-muted">Annual Revenue</span>
                                                    <span class="fw-bold font-monospace text-success">${o.revenue_formatted}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Communication Channels Card -->
                                    <div class="col-md-6">
                                        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-light-subtle">
                                            <h6 class="text-uppercase fw-bold text-brand small mb-3 d-flex align-items-center gap-1.5">
                                                <i class="bi bi-telephone-inbound text-brand"></i> Key Contact & Focal Person
                                            </h6>
                                            <div class="d-flex flex-column gap-2 small">
                                                <div class="d-flex justify-content-between p-2 rounded bg-body">
                                                    <span class="text-muted">Focal Person</span>
                                                    <span class="fw-semibold text-dark">${o.focal_person}</span>
                                                </div>
                                                <div class="d-flex justify-content-between p-2 rounded bg-body">
                                                    <span class="text-muted">CNIC</span>
                                                    <span class="fw-semibold font-monospace text-dark">${o.cnic_formatted || '—'}</span>
                                                </div>
                                                <div class="d-flex justify-content-between p-2 rounded bg-body">
                                                    <span class="text-muted">Primary Phone</span>
                                                    <span class="fw-semibold font-monospace text-dark">${o.phone || '—'}</span>
                                                </div>
                                                <div class="d-flex justify-content-between p-2 rounded bg-body">
                                                    <span class="text-muted">Secondary Phone</span>
                                                    <span class="fw-semibold font-monospace text-dark">${o.phone_2 || '—'}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Registered Office Locations -->
                                <div class="card border-0 shadow-sm rounded-4 p-3 mb-3 bg-light-subtle">
                                    <h6 class="text-uppercase fw-bold text-brand small mb-2 d-flex align-items-center gap-1.5">
                                        <i class="bi bi-pin-map text-brand"></i> Registered Facilities & Offices
                                    </h6>
                                    <div class="row g-2 small">
                                        <div class="col-sm-6">
                                            <div class="p-2.5 rounded bg-body h-100">
                                                <span class="text-muted d-block small mb-1">Main Headquarters Address</span>
                                                <span class="fw-medium text-dark">${o.address || 'No headquarters address provided'}</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="p-2.5 rounded bg-body h-100">
                                                <span class="text-muted d-block small mb-1">Secondary / Branch Address</span>
                                                <span class="fw-medium text-dark">${o.address_2 || 'No secondary facility address'}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pinned GPS Location Map View -->
                                <div class="card border-0 shadow-sm rounded-4 p-3 bg-light-subtle">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="text-uppercase fw-bold text-brand small mb-0 d-flex align-items-center gap-1.5">
                                            <i class="bi bi-geo-alt-fill text-brand"></i> Pinned GPS Coordinates
                                        </h6>
                                        ${hasCoords ? `
                                            <a href="https://www.google.com/maps?q=${lat},${lng}" target="_blank"
                                                class="btn btn-sm btn-outline-brand rounded-pill px-3 py-1 text-decoration-none small">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> Open in Google Maps
                                            </a>
                                        ` : '<span class="badge bg-secondary text-white">No GPS Pinned</span>'}
                                    </div>
                                    <div id="viewOrgMapBox" class="rounded-3 border overflow-hidden shadow-inner" style="height: 220px; width: 100%;"></div>
                                </div>
                            `;

                            if (viewOrgMap) {
                                viewOrgMap.remove();
                                viewOrgMap = null;
                            }

                            $('#viewOrgModalContent').html(html);
                            CorkTheme.sync(viewOrgModalEl);
                            viewOrgModal.show();

                            setTimeout(function() {
                                var vLat = hasCoords ? parseFloat(lat) : defaultLat;
                                var vLng = hasCoords ? parseFloat(lng) : defaultLng;

                                if (viewOrgMap) {
                                    viewOrgMap.remove();
                                    viewOrgMap = null;
                                }

                                viewOrgMap = L.map('viewOrgMapBox').setView([vLat, vLng], hasCoords ? 14 : 6);
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    maxZoom: 19,
                                    attribution: '&copy; OpenStreetMap'
                                }).addTo(viewOrgMap);

                                if (hasCoords) {
                                    viewOrgMarker = L.marker([vLat, vLng]).addTo(viewOrgMap)
                                        .bindPopup('<b>' + o.name + '</b><br>' + (o.address || '')).openPopup();
                                }
                                viewOrgMap.invalidateSize();
                            }, 300);
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Error loading organization details.');
                    }
                });
            });

            $('#viewOrgModal').on('shown.bs.modal', function() {
                if (viewOrgMap) {
                    viewOrgMap.invalidateSize();
                }
                setTimeout(function() {
                    if (viewOrgMap) {
                        viewOrgMap.invalidateSize();
                    }
                }, 150);
            });

            $('#viewOrgModal').on('hidden.bs.modal', function() {
                if (viewOrgMap) {
                    viewOrgMap.remove();
                    viewOrgMap = null;
                }
            });
        });
    </script>
@endsection
