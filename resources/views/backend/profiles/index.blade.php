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
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Profiles Management</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Manage customer and lead profiles, contact details, team assignments, and map locations.
                </p>
            </div>
            <button type="button" id="createProfileBtn"
                class="inline-flex items-center gap-2 bg-brand hover:bg-brand/90 text-white font-medium px-4 py-2.5 rounded-lg shadow-sm transition">
                <span class="text-lg leading-none">+</span> Add Profile
            </button>
        </div>

        <!-- DataTable Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
            <div class="p-4 overflow-x-auto">
                <table id="profilesTable" class="w-full text-sm text-left text-gray-700 dark:text-gray-200 min-w-[750px]">
                    <colgroup>
                        <col style="width: 5%;">
                        <col style="width: 20%;">
                        <col style="width: 18%;">
                        <col style="width: 12%;">
                        <col style="width: 12%;">
                        <col style="width: 15%;">
                        <col style="width: 18%;">
                    </colgroup>
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-3">#</th>
                            <th class="px-3 py-3">Profile</th>
                            <th class="px-3 py-3">Contact</th>
                            <th class="px-3 py-3">Source</th>
                            <th class="px-3 py-3">Status</th>
                            <th class="px-3 py-3">Assigned To</th>
                            <th class="px-3 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <!-- Populated by DataTables via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- CREATE / EDIT PROFILE MODAL (Bootstrap 5 Simplified Form) -->
    <!-- ========================================================= -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form id="profileForm" enctype="multipart/form-data" class="modal-content shadow-2xl border-0 rounded-4 overflow-hidden mb-0">
                @csrf
                <input type="hidden" id="profile_id" name="profile_id">

                <!-- Modal Header -->
                <div class="modal-header px-4 py-3 bg-light-subtle border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0"
                            style="width: 42px; height: 42px; background: linear-gradient(135deg, #5b3fd4, #7952eb);">
                            <i class="bi bi-person-badge fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-dark" id="profileModalLabel">Add Profile</h5>
                            <small class="text-muted">Configure profile identity, demographics, contact channels, and coordinates.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close" id="closeProfileModalBtn"></button>
                </div>

                <!-- Modal Body (Simplified Clean Layout) -->
                <div class="modal-body p-4">
                    <div class="row g-4">

                        <!-- LEFT COLUMN: Avatar, Status & Role Demographics -->
                        <div class="col-lg-4">
                            <!-- Avatar Upload Box -->
                            <div class="bg-light-subtle rounded-4 p-3 text-center border mb-3">
                                <div class="position-relative d-inline-block mx-auto mb-2">
                                    <img id="avatarPreview"
                                        src="https://ui-avatars.com/api/?name=User&background=5b3fd4&color=fff"
                                        alt="Avatar preview"
                                        class="rounded-circle object-fit-cover shadow-sm border border-2 border-white bg-white"
                                        style="width: 100px; height: 100px;" />
                                    <label for="avatar"
                                        class="position-absolute bottom-0 end-0 text-white rounded-circle shadow cursor-pointer d-flex align-items-center justify-content-center hover-scale"
                                        style="width: 32px; height: 32px; background-color: #5b3fd4;"
                                        title="Click to choose picture">
                                        <i class="bi bi-camera-fill small"></i>
                                    </label>
                                </div>

                                <h6 class="fw-bold mb-1 text-dark text-truncate" id="avatarNamePreview">Profile Avatar</h6>
                                <span class="text-muted extra-small d-block mb-2">PNG, JPG, WEBP up to 3MB</span>

                                <input type="file" id="avatar" name="avatar" accept="image/*" class="d-none">
                                <button type="button" class="btn btn-sm btn-outline-brand w-100 rounded-pill py-1.5 fw-medium"
                                    onclick="document.getElementById('avatar').click();">
                                    <i class="bi bi-upload me-1"></i> Upload Photo
                                </button>
                                <div class="text-danger small mt-1 text-start" id="err_avatar"></div>
                            </div>

                            <!-- Status & Assignment Box -->
                            <div class="bg-light-subtle rounded-4 p-3 border">
                                <h6 class="fw-bold text-dark small mb-3 pb-2 border-bottom d-flex align-items-center gap-1.5">
                                    <i class="bi bi-shield-check text-brand"></i> Status & Assignment
                                </h6>

                                <!-- Status -->
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

                                <!-- Job Type -->
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary mb-1">Job Type</label>
                                    <select id="job_type" name="job_type" class="form-select rounded-3 shadow-none">
                                        <option value="">-- Select --</option>
                                        @foreach ($jobTypes as $jobKey => $jobLabel)
                                            <option value="{{ $jobKey }}">{{ $jobLabel }}</option>
                                        @endforeach
                                    </select>
                                    <div class="text-danger small mt-1" id="err_job_type"></div>
                                </div>

                                <!-- Education -->
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary mb-1">Education</label>
                                    <select id="education" name="education" class="form-select rounded-3 shadow-none">
                                        <option value="">-- Select --</option>
                                        @foreach ($educations as $eduKey => $eduLabel)
                                            <option value="{{ $eduKey }}">{{ $eduLabel }}</option>
                                        @endforeach
                                    </select>
                                    <div class="text-danger small mt-1" id="err_education"></div>
                                </div>

                                <!-- Age Group -->
                                <div>
                                    <label class="form-label small fw-semibold text-secondary mb-1">Age Group</label>
                                    <select id="age_group" name="age_group" class="form-select rounded-3 shadow-none">
                                        <option value="">-- Select --</option>
                                        @foreach ($ageGroups as $ageKey => $ageLabel)
                                            <option value="{{ $ageKey }}">{{ $ageLabel }}</option>
                                        @endforeach
                                    </select>
                                    <div class="text-danger small mt-1" id="err_age_group"></div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: Personal Info, Contacts & Map -->
                        <div class="col-lg-8">

                            <!-- SECTION 1: Personal & Account Credentials -->
                            <div class="mb-4">
                                <div class="d-flex align-items-center gap-2 pb-2 mb-3 border-bottom">
                                    <i class="bi bi-person-fill text-brand fs-5"></i>
                                    <h6 class="fw-bold mb-0 text-dark">1. Personal & Account Information</h6>
                                </div>

                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">
                                            First Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="first_name" name="first_name" class="form-control rounded-3 shadow-none"
                                            placeholder="e.g. John" required>
                                        <div class="text-danger small mt-1" id="err_first_name"></div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">
                                            Last Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="last_name" name="last_name" class="form-control rounded-3 shadow-none"
                                            placeholder="e.g. Doe" required>
                                        <div class="text-danger small mt-1" id="err_last_name"></div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">Username</label>
                                        <div class="input-group shadow-none">
                                            <span class="input-group-text rounded-start-3 small text-muted">@</span>
                                            <input type="text" id="username" name="username" class="form-control rounded-end-3 shadow-none"
                                                placeholder="johndoe">
                                        </div>
                                        <div class="text-danger small mt-1" id="err_username"></div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">Email Address</label>
                                        <input type="email" id="email" name="email" class="form-control rounded-3 shadow-none"
                                            placeholder="john.doe@example.com">
                                        <div class="text-danger small mt-1" id="err_email"></div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label small fw-semibold text-secondary mb-1">
                                            Account Password <span id="passwordHint" class="text-muted fw-normal"></span>
                                        </label>
                                        <input type="password" id="password" name="password" class="form-control rounded-3 shadow-none"
                                            placeholder="••••••••">
                                        <div class="text-danger small mt-1" id="err_password"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 2: Identity & Contact Channels -->
                            <div class="mb-4">
                                <div class="d-flex align-items-center gap-2 pb-2 mb-3 border-bottom">
                                    <i class="bi bi-card-text text-brand fs-5"></i>
                                    <h6 class="fw-bold mb-0 text-dark">2. Identity & Contact Details</h6>
                                </div>

                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">
                                            CNIC Number <span class="text-muted fw-normal">(13 digits)</span>
                                        </label>
                                        <input type="text" id="cnic" name="cnic" maxlength="13" class="form-control font-monospace rounded-3 shadow-none"
                                            placeholder="4210112345671">
                                        <div class="text-danger small mt-1" id="err_cnic"></div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">
                                            Phone Number <span class="text-muted fw-normal">(11 digits)</span>
                                        </label>
                                        <input type="text" id="phone" name="phone" maxlength="11" class="form-control font-monospace rounded-3 shadow-none"
                                            placeholder="03001234567">
                                        <div class="text-danger small mt-1" id="err_phone"></div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label small fw-semibold text-secondary mb-1">Income ($ USD)</label>
                                        <div class="input-group shadow-none">
                                            <span class="input-group-text rounded-start-3">$</span>
                                            <input type="number" step="0.01" min="0" id="income" name="income"
                                                class="form-control font-monospace rounded-end-3 shadow-none" placeholder="50000.00">
                                        </div>
                                        <div class="text-danger small mt-1" id="err_income"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 3: Social & Professional Links (Optional) -->
                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-share text-brand fs-5"></i>
                                        <h6 class="fw-bold mb-0 text-dark">3. Social & Professional Profiles</h6>
                                    </div>
                                    <span class="badge bg-light text-muted border extra-small">Optional</span>
                                </div>

                                <div class="row g-3">
                                    <!-- Facebook Link -->
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">
                                            Facebook Profile
                                        </label>
                                        <div class="input-group shadow-none">
                                            <span class="input-group-text rounded-start-3 text-primary"><i class="bi bi-facebook"></i></span>
                                            <input type="text" id="facebook_link" name="facebook_link" class="form-control rounded-end-3 shadow-none"
                                                placeholder="https://facebook.com/username">
                                        </div>
                                        <div class="text-danger small mt-1" id="err_facebook_link"></div>
                                    </div>

                                    <!-- Instagram Link -->
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">
                                            Instagram Profile
                                        </label>
                                        <div class="input-group shadow-none">
                                            <span class="input-group-text rounded-start-3 text-danger"><i class="bi bi-instagram"></i></span>
                                            <input type="text" id="instagram_link" name="instagram_link" class="form-control rounded-end-3 shadow-none"
                                                placeholder="https://instagram.com/username">
                                        </div>
                                        <div class="text-danger small mt-1" id="err_instagram_link"></div>
                                    </div>

                                    <!-- LinkedIn Link -->
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">
                                            LinkedIn Profile
                                        </label>
                                        <div class="input-group shadow-none">
                                            <span class="input-group-text rounded-start-3 text-info"><i class="bi bi-linkedin"></i></span>
                                            <input type="text" id="linkedin_link" name="linkedin_link" class="form-control rounded-end-3 shadow-none"
                                                placeholder="https://linkedin.com/in/username">
                                        </div>
                                        <div class="text-danger small mt-1" id="err_linkedin_link"></div>
                                    </div>

                                    <!-- Threads Link -->
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">
                                            Threads Profile
                                        </label>
                                        <div class="input-group shadow-none">
                                            <span class="input-group-text rounded-start-3 text-dark"><i class="bi bi-threads"></i></span>
                                            <input type="text" id="threads_link" name="threads_link" class="form-control rounded-end-3 shadow-none"
                                                placeholder="https://threads.net/@username">
                                        </div>
                                        <div class="text-danger small mt-1" id="err_threads_link"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 4: Address & Interactive Map -->
                            <div>
                                <div class="d-flex align-items-center gap-2 pb-2 mb-3 border-bottom">
                                    <i class="bi bi-geo-alt-fill text-brand fs-5"></i>
                                    <h6 class="fw-bold mb-0 text-dark">4. Address & Geographical Map Location</h6>
                                </div>

                                <!-- Address 1 -->
                                <div class="mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label small fw-semibold text-secondary mb-0">
                                            Address 1 (Primary Location)
                                        </label>
                                        <button type="button" id="locateAddressBtn" class="btn btn-sm btn-link p-0 text-decoration-none text-brand fw-semibold">
                                            <i class="bi bi-search me-1"></i> Locate on Map
                                        </button>
                                    </div>
                                    <input type="text" id="address_1" name="address_1" class="form-control rounded-3 shadow-none"
                                        placeholder="Street address, building, city">
                                    <div class="text-danger small mt-1" id="err_address_1"></div>
                                </div>

                                <!-- Address 2 with "Same as above" -->
                                <div class="mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label small fw-semibold text-secondary mb-0">
                                            Address 2 (Billing / Secondary)
                                        </label>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input cursor-pointer shadow-none" type="checkbox" role="switch" id="same_as_above">
                                            <label class="form-check-label small fw-semibold text-brand cursor-pointer" for="same_as_above">
                                                Same as above
                                            </label>
                                        </div>
                                    </div>
                                    <input type="text" id="address_2" name="address_2" class="form-control rounded-3 shadow-none"
                                        placeholder="Suite, unit, alternate delivery point">
                                    <div class="text-danger small mt-1" id="err_address_2"></div>
                                </div>

                                <!-- Map Viewbox Header & GPS Controls -->
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="small fw-semibold text-secondary d-flex align-items-center gap-1">
                                            <i class="bi bi-map text-brand"></i> Pin Location on Map
                                            <span class="text-muted fw-normal extra-small">(Click or drag marker)</span>
                                        </span>
                                        <button type="button" id="getCurrentLocationBtn" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 shadow-none extra-small">
                                            <i class="bi bi-crosshair me-1"></i> Use My GPS
                                        </button>
                                    </div>

                                    <!-- Hidden Coordinates -->
                                    <input type="hidden" id="latitude" name="latitude">
                                    <input type="hidden" id="longitude" name="longitude">

                                    <!-- Leaflet Map Container -->
                                    <div id="mapViewBox" class="rounded-3 border overflow-hidden shadow-inner" style="height: 220px; width: 100%;"></div>

                                    <div class="d-flex align-items-center justify-content-between mt-2 pt-1">
                                        <div class="badge bg-white text-dark border shadow-xs px-3 py-1.5 font-monospace rounded-pill d-flex align-items-center gap-1.5" id="coordsBadge">
                                            <i class="bi bi-geo-alt-fill text-danger"></i>
                                            <span id="coordsText">No location selected (Click map to pin)</span>
                                        </div>
                                        <button type="button" id="clearCoordsBtn" class="btn btn-sm btn-link text-danger text-decoration-none p-0 extra-small">
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
                    <button type="button" class="btn btn-light px-4 rounded-3 fw-medium" data-bs-dismiss="modal" id="cancelProfileBtn">
                        Cancel
                    </button>
                    <button type="submit" id="saveProfileBtn" class="btn btn-brand px-4 rounded-3 shadow-sm d-inline-flex align-items-center gap-2 fw-medium">
                        <i class="bi bi-check2-circle fs-6"></i>
                        <span>Save Profile</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- VIEW DETAILS & LOCATION MODAL (Bootstrap 5 Clean Dossier) -->
    <!-- ========================================================= -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow-2xl border-0 rounded-4 overflow-hidden">
                <div class="modal-header px-4 py-3 bg-light-subtle border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0"
                            style="width: 40px; height: 40px; background: linear-gradient(135deg, #5b3fd4, #7952eb);">
                            <i class="bi bi-person-vcard fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-dark" id="viewModalLabel">Profile Details</h5>
                            <small class="text-muted">Comprehensive profile information, demographics, and GPS location</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close" id="closeViewModalBtn"></button>
                </div>

                <div class="modal-body p-4" id="viewModalContent">
                    <!-- Populated dynamically via JS -->
                </div>

                <div class="modal-footer px-4 py-3 bg-light-subtle border-top d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary px-4 rounded-3 fw-medium" data-bs-dismiss="modal" id="closeViewModalBtn2">
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
            // Bootstrap Modal Instances
            // ---------------------------------------------------------
            var profileModalEl = document.getElementById('profileModal');
            var profileModal = new bootstrap.Modal(profileModalEl, {
                backdrop: 'static',
                keyboard: true
            });

            var viewModalEl = document.getElementById('viewModal');
            var viewModal = new bootstrap.Modal(viewModalEl, {
                keyboard: true
            });

            // ---------------------------------------------------------
            // 1. Initialize DataTable
            // ---------------------------------------------------------
            var table = $('#profilesTable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: '{{ route('profiles.data') }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false },
                    { data: 'profile_info', name: 'first_name', orderable: true },
                    { data: 'contact_info', name: 'email', orderable: true },
                    { data: 'source_badge', name: 'source', orderable: true },
                    { data: 'status_badge', name: 'status', orderable: true },
                    { data: 'assigned_user_badge', name: 'assigned_to', orderable: true },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[1, 'asc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search profiles...",
                    lengthMenu: "Show _MENU_ entries"
                }
            });

            // ---------------------------------------------------------
            // 2. Leaflet Map Initialization for Form Viewbox
            // ---------------------------------------------------------
            if (typeof L !== 'undefined' && L.Icon && L.Icon.Default) {
                delete L.Icon.Default.prototype._getIconUrl;
                L.Icon.Default.mergeOptions({
                    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                });
            }

            var formMap = null;
            var formMarker = null;
            var defaultLat = 31.5204;
            var defaultLng = 74.3587;

            function initOrUpdateFormMap(lat, lng) {
                var targetLat = (lat !== null && lat !== undefined && lat !== '') ? parseFloat(lat) : defaultLat;
                var targetLng = (lng !== null && lng !== undefined && lng !== '') ? parseFloat(lng) : defaultLng;
                var hasCoords = (lat !== null && lat !== undefined && lat !== '' && lng !== null && lng !== undefined && lng !== '');

                if (!formMap) {
                    formMap = L.map('mapViewBox').setView([targetLat, targetLng], hasCoords ? 13 : 6);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(formMap);

                    formMap.on('click', function(e) {
                        setFormMarker(e.latlng.lat, e.latlng.lng);
                    });
                } else {
                    formMap.setView([targetLat, targetLng], hasCoords ? 13 : 6);
                }

                if (hasCoords) {
                    setFormMarker(targetLat, targetLng);
                } else {
                    removeFormMarker();
                }

                setTimeout(function() {
                    if (formMap) formMap.invalidateSize();
                }, 300);
            }

            function setFormMarker(lat, lng) {
                var fixedLat = parseFloat(lat).toFixed(6);
                var fixedLng = parseFloat(lng).toFixed(6);

                $('#latitude').val(fixedLat);
                $('#longitude').val(fixedLng);
                $('#coordsText').text(fixedLat + ', ' + fixedLng);

                if (formMarker) {
                    formMarker.setLatLng([lat, lng]);
                } else {
                    formMarker = L.marker([lat, lng], { draggable: true }).addTo(formMap);

                    formMarker.on('dragend', function(e) {
                        var pos = e.target.getLatLng();
                        setFormMarker(pos.lat, pos.lng);
                    });
                }
            }

            function removeFormMarker() {
                if (formMarker && formMap) {
                    formMap.removeLayer(formMarker);
                    formMarker = null;
                }
                $('#latitude').val('');
                $('#longitude').val('');
                $('#coordsText').text('No location selected (Click map to pin)');
            }

            $('#clearCoordsBtn').on('click', function() {
                removeFormMarker();
            });

            // Geolocation button: Use My GPS
            $('#getCurrentLocationBtn').on('click', function() {
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
                        formMap.setView([userLat, userLng], 14);
                        setFormMarker(userLat, userLng);
                    },
                    function(err) {
                        btn.prop('disabled', false).html('<i class="bi bi-crosshair me-1"></i> Use My GPS');
                        alert('Could not retrieve your location: ' + err.message);
                    },
                    { timeout: 10000 }
                );
            });

            // Locate address using Nominatim
            $('#locateAddressBtn').on('click', function() {
                var query = $('#address_1').val().trim();
                if (!query) {
                    alert('Please enter an address in "Address 1" first to locate on map.');
                    $('#address_1').focus();
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
                            formMap.setView([lat, lon], 14);
                            setFormMarker(lat, lon);
                        } else {
                            alert('Location not found for "' + query + '". You can click directly on the map to pin.');
                        }
                    })
                    .fail(function() {
                        btn.prop('disabled', false).html('<i class="bi bi-search me-1"></i> Locate on Map');
                        alert('Could not query geocoding service. Please click directly on the map.');
                    });
            });

            // ---------------------------------------------------------
            // 3. Address 2 "Same as above" logic
            // ---------------------------------------------------------
            $('#same_as_above').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#address_2').val($('#address_1').val()).prop('readonly', true).addClass('bg-body-secondary');
                } else {
                    $('#address_2').prop('readonly', false).removeClass('bg-body-secondary');
                }
            });

            $('#address_1').on('input', function() {
                if ($('#same_as_above').is(':checked')) {
                    $('#address_2').val($(this).val());
                }
            });

            // ---------------------------------------------------------
            // 4. Digit-only enforcement for CNIC (13) and Phone (11)
            // ---------------------------------------------------------
            $('#cnic').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13);
            });

            $('#phone').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
            });

            // ---------------------------------------------------------
            // 5. Avatar preview & Name Live Sync
            // ---------------------------------------------------------
            $('#avatar').on('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(evt) {
                        $('#avatarPreview').attr('src', evt.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });

            $('#first_name, #last_name').on('input', function() {
                var fn = $('#first_name').val().trim();
                var ln = $('#last_name').val().trim();
                var full = (fn + ' ' + ln).trim();
                $('#avatarNamePreview').text(full || 'Profile Avatar');
            });

            // ---------------------------------------------------------
            // 6. Modal Open / Close Helpers
            // ---------------------------------------------------------
            function clearErrors() {
                $('[id^="err_"]').text('');
            }

            function resetForm() {
                $('#profileForm')[0].reset();
                $('#profile_id').val('');
                $('#avatarPreview').attr('src', 'https://ui-avatars.com/api/?name=User&background=5b3fd4&color=fff');
                $('#avatarNamePreview').text('Profile Avatar');
                $('#status').val('prospective');
                $('#assigned_to').val('');
                $('#source').val('WhatsApp');
                $('#same_as_above').prop('checked', false);
                $('#address_2').prop('readonly', false).removeClass('bg-body-secondary');
                $('#passwordHint').text('');
                $('#job_type').val('');
                $('#income').val('');
                $('#education').val('');
                $('#age_group').val('');
                $('#facebook_link').val('');
                $('#instagram_link').val('');
                $('#linkedin_link').val('');
                $('#threads_link').val('');
                clearErrors();
                removeFormMarker();
            }

            function openProfileModal(title) {
                $('#profileModalLabel').text(title);
                clearErrors();
                CorkTheme.sync(profileModalEl);
                profileModal.show();
            }

            function closeProfileModal() {
                profileModal.hide();
                resetForm();
            }

            $('#profileModal').on('shown.bs.modal', function() {
                if (formMap) {
                    formMap.invalidateSize();
                }
                setTimeout(function() {
                    if (formMap) {
                        formMap.invalidateSize();
                    }
                }, 150);
            });

            // ---------------------------------------------------------
            // 7. ADD Profile Button Click
            // ---------------------------------------------------------
            $('#createProfileBtn').on('click', function() {
                resetForm();
                openProfileModal('Add New Profile');
                initOrUpdateFormMap(null, null);
            });

            // ---------------------------------------------------------
            // 8. EDIT Profile Button Click
            // ---------------------------------------------------------
            $(document).on('click', '.edit_btn', function() {
                var id = $(this).data('id');
                resetForm();

                $.ajax({
                    url: '/profiles/' + id + '/edit',
                    type: 'GET',
                    success: function(res) {
                        if (res.status && res.profile) {
                            var p = res.profile;
                            $('#profile_id').val(p.id);
                            $('#first_name').val(p.first_name);
                            $('#last_name').val(p.last_name);
                            $('#username').val(p.username || '');
                            $('#source').val(p.source || 'WhatsApp');
                            $('#email').val(p.email || '');
                            $('#cnic').val(p.cnic || '');
                            $('#phone').val(p.phone || '');
                            $('#status').val(p.status || 'prospective');
                            $('#assigned_to').val(p.assigned_to || '');
                            $('#job_type').val(p.job_type || '');
                            $('#income').val(p.income || '');
                            $('#education').val(p.education || '');
                            $('#age_group').val(p.age_group || '');
                            $('#facebook_link').val(p.facebook_link || '');
                            $('#instagram_link').val(p.instagram_link || '');
                            $('#linkedin_link').val(p.linkedin_link || '');
                            $('#threads_link').val(p.threads_link || '');
                            $('#address_1').val(p.address_1 || '');
                            $('#address_2').val(p.address_2 || '');

                            var full = (p.first_name + ' ' + p.last_name).trim();
                            $('#avatarNamePreview').text(full || 'Profile Avatar');

                            if (p.address_1 && p.address_1 === p.address_2) {
                                $('#same_as_above').prop('checked', true);
                                $('#address_2').prop('readonly', true).addClass('bg-body-secondary');
                            }

                            if (p.avatar_url) {
                                $('#avatarPreview').attr('src', p.avatar_url);
                            }

                            $('#passwordHint').text('(Leave blank to keep current)');

                            openProfileModal('Edit Profile — ' + p.first_name + ' ' + p.last_name);
                            initOrUpdateFormMap(p.latitude, p.longitude);
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Error loading profile data.');
                    }
                });
            });

            // ---------------------------------------------------------
            // 9. SAVE Form (Store or Update)
            // ---------------------------------------------------------
            $('#profileForm').on('submit', function(e) {
                e.preventDefault();
                clearErrors();

                var profileId = $('#profile_id').val();
                var isEdit = Boolean(profileId);
                var url = isEdit ? '/profiles/' + profileId + '/update' : '{{ route('profiles.store') }}';

                var formData = new FormData(this);

                $('#saveProfileBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1.5"></span> Saving...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.status) {
                            closeProfileModal();
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
                            alert(xhr.responseJSON?.message || 'An error occurred while saving the profile.');
                        }
                    },
                    complete: function() {
                        $('#saveProfileBtn').prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> <span>Save Profile</span>');
                    }
                });
            });

            // ---------------------------------------------------------
            // 10. DELETE Profile
            // ---------------------------------------------------------
            $(document).on('click', '.delete_btn', function() {
                var id = $(this).data('id');

                if (!confirm('Are you sure you want to delete this profile? This action cannot be undone.')) {
                    return;
                }

                $.ajax({
                    url: '/profiles/' + id,
                    type: 'DELETE',
                    success: function(res) {
                        if (res.status) {
                            table.ajax.reload(null, false);
                        } else {
                            alert(res.message || 'Failed to delete profile.');
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Delete request failed.');
                    }
                });
            });

            // ---------------------------------------------------------
            // 11. VIEW Details & Map Modal
            // ---------------------------------------------------------
            var viewMap = null;
            var viewMarker = null;

            $(document).on('click', '.view_btn', function() {
                var id = $(this).data('id');

                $.ajax({
                    url: '/profiles/' + id + '/show',
                    type: 'GET',
                    success: function(res) {
                        if (res.status && res.profile) {
                            var p = res.profile;
                            var lat = p.latitude;
                            var lng = p.longitude;
                            var hasCoords = (lat && lng);

                            var statusBadgeClass = 'bg-secondary text-white';
                            if (p.status === 'working') {
                                statusBadgeClass = 'bg-success text-white';
                            } else if (p.status === 'prospective') {
                                statusBadgeClass = 'bg-warning text-dark';
                            } else if (p.status === 'postponed') {
                                statusBadgeClass = 'bg-info text-dark';
                            } else if (p.status === 'closed') {
                                statusBadgeClass = 'bg-secondary text-white';
                            }

                            var cleanUrl = function(u) {
                                if (!u) return '';
                                return (u.startsWith('http://') || u.startsWith('https://')) ? u : ('https://' + u);
                            };

                            var socialLinksHtml = '';
                            if (p.facebook_link) {
                                socialLinksHtml += `<a href="${cleanUrl(p.facebook_link)}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill d-inline-flex align-items-center gap-1.5 extra-small"><i class="bi bi-facebook"></i> Facebook</a> `;
                            }
                            if (p.instagram_link) {
                                socialLinksHtml += `<a href="${cleanUrl(p.instagram_link)}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill d-inline-flex align-items-center gap-1.5 extra-small"><i class="bi bi-instagram"></i> Instagram</a> `;
                            }
                            if (p.linkedin_link) {
                                socialLinksHtml += `<a href="${cleanUrl(p.linkedin_link)}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill d-inline-flex align-items-center gap-1.5 extra-small"><i class="bi bi-linkedin"></i> LinkedIn</a> `;
                            }
                            if (p.threads_link) {
                                socialLinksHtml += `<a href="${cleanUrl(p.threads_link)}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill d-inline-flex align-items-center gap-1.5 extra-small"><i class="bi bi-threads"></i> Threads</a> `;
                            }
                            if (!socialLinksHtml) {
                                socialLinksHtml = '<span class="text-muted fst-italic extra-small">No social profiles configured</span>';
                            }

                            var html = `
                                <!-- Hero Profile Header Card -->
                                <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-light-subtle">
                                    <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                                        <img src="${p.avatar_url}" class="rounded-circle object-fit-cover shadow-sm border border-2 border-white bg-white"
                                            style="width: 76px; height: 76px; min-width: 76px;">
                                        <div class="text-center text-sm-start flex-grow-1">
                                            <div class="d-flex flex-wrap align-items-center gap-2 justify-content-center justify-content-sm-start mb-1">
                                                <h4 class="fw-bold mb-0 text-dark">${p.full_name}</h4>
                                                <span class="badge ${statusBadgeClass} text-uppercase px-2.5 py-1 rounded-pill">${p.status}</span>
                                                <span class="badge bg-brand-subtle text-brand px-2.5 py-1 rounded-pill border">${p.source}</span>
                                            </div>
                                            <div class="text-muted small">
                                                ${p.username ? '<span class="font-monospace text-brand">@' + p.username + '</span> • ' : ''}
                                                <span>Registered on ${p.created_at}</span>
                                            </div>
                                        </div>
                                        <div class="text-sm-end text-center">
                                            <small class="text-muted d-block mb-1">Assigned Staff</small>
                                            <span class="badge bg-white text-dark border px-3 py-1.5 rounded-pill shadow-xs d-inline-flex align-items-center gap-1.5">
                                                <i class="bi bi-person-check-fill text-brand"></i>
                                                ${p.assigned_user}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact & Demographics 2-Card Row -->
                                <div class="row g-3 mb-3">
                                    <!-- Contact Card -->
                                    <div class="col-md-6">
                                        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-light-subtle">
                                            <h6 class="text-uppercase fw-bold text-brand small mb-3 d-flex align-items-center gap-1.5">
                                                <i class="bi bi-envelope-at text-brand"></i> Contact Channels
                                            </h6>
                                            <div class="d-flex flex-column gap-2 small">
                                                <div class="d-flex justify-content-between p-2 rounded bg-body">
                                                    <span class="text-muted">Email</span>
                                                    <span class="fw-semibold text-dark text-break">${p.email || '—'}</span>
                                                </div>
                                                <div class="d-flex justify-content-between p-2 rounded bg-body">
                                                    <span class="text-muted">Phone Number</span>
                                                    <span class="fw-semibold font-monospace text-dark">${p.phone || '—'}</span>
                                                </div>
                                                <div class="d-flex justify-content-between p-2 rounded bg-body">
                                                    <span class="text-muted">CNIC</span>
                                                    <span class="fw-semibold font-monospace text-dark">${p.cnic || '—'}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Demographics Card -->
                                    <div class="col-md-6">
                                        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-light-subtle">
                                            <h6 class="text-uppercase fw-bold text-brand small mb-3 d-flex align-items-center gap-1.5">
                                                <i class="bi bi-briefcase text-brand"></i> Career & Demographics
                                            </h6>
                                            <div class="d-flex flex-column gap-2 small">
                                                <div class="d-flex justify-content-between p-2 rounded bg-body">
                                                    <span class="text-muted">Job Classification</span>
                                                    <span class="fw-semibold text-dark">${p.job_type}</span>
                                                </div>
                                                <div class="d-flex justify-content-between p-2 rounded bg-body">
                                                    <span class="text-muted">Annual Income</span>
                                                    <span class="fw-bold font-monospace text-success">${p.income}</span>
                                                </div>
                                                <div class="d-flex justify-content-between p-2 rounded bg-body">
                                                    <span class="text-muted">Education Level</span>
                                                    <span class="fw-semibold text-dark">${p.education}</span>
                                                </div>
                                                <div class="d-flex justify-content-between p-2 rounded bg-body">
                                                    <span class="text-muted">Age Bracket</span>
                                                    <span class="fw-semibold text-dark">${p.age_group}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Social & Professional Profiles Card -->
                                <div class="card border-0 shadow-sm rounded-4 p-3 mb-3 bg-light-subtle">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="text-uppercase fw-bold text-brand small mb-0 d-flex align-items-center gap-1.5">
                                            <i class="bi bi-share text-brand"></i> Social & Professional Profiles
                                        </h6>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 pt-1">
                                        ${socialLinksHtml}
                                    </div>
                                </div>

                                <!-- Address Details -->
                                <div class="card border-0 shadow-sm rounded-4 p-3 mb-3 bg-light-subtle">
                                    <h6 class="text-uppercase fw-bold text-brand small mb-2 d-flex align-items-center gap-1.5">
                                        <i class="bi bi-pin-map text-brand"></i> Registered Addresses
                                    </h6>
                                    <div class="row g-2 small">
                                        <div class="col-sm-6">
                                            <div class="p-2.5 rounded bg-body h-100">
                                                <span class="text-muted d-block small mb-1">Primary Location (Address 1)</span>
                                                <span class="fw-medium text-dark">${p.address_1 || 'No address provided'}</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="p-2.5 rounded bg-body h-100">
                                                <span class="text-muted d-block small mb-1">Secondary / Billing (Address 2)</span>
                                                <span class="fw-medium text-dark">${p.address_2 || 'No secondary address'}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Location Map View -->
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
                                    <div id="viewMapBox" class="rounded-3 border overflow-hidden shadow-inner" style="height: 220px; width: 100%;"></div>
                                </div>
                            `;

                            if (viewMap) {
                                viewMap.remove();
                                viewMap = null;
                            }

                            $('#viewModalContent').html(html);
                            CorkTheme.sync(viewModalEl);
                            viewModal.show();

                            setTimeout(function() {
                                var vLat = hasCoords ? parseFloat(lat) : defaultLat;
                                var vLng = hasCoords ? parseFloat(lng) : defaultLng;

                                if (viewMap) {
                                    viewMap.remove();
                                    viewMap = null;
                                }

                                viewMap = L.map('viewMapBox').setView([vLat, vLng], hasCoords ? 14 : 6);
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    maxZoom: 19,
                                    attribution: '&copy; OpenStreetMap'
                                }).addTo(viewMap);

                                if (hasCoords) {
                                    viewMarker = L.marker([vLat, vLng]).addTo(viewMap)
                                        .bindPopup('<b>' + p.full_name + '</b><br>' + (p.address_1 || '')).openPopup();
                                }
                                viewMap.invalidateSize();
                            }, 300);
                        }
                    }
                });
            });

            $('#viewModal').on('shown.bs.modal', function() {
                if (viewMap) {
                    viewMap.invalidateSize();
                }
                setTimeout(function() {
                    if (viewMap) {
                        viewMap.invalidateSize();
                    }
                }, 150);
            });

            $('#viewModal').on('hidden.bs.modal', function() {
                if (viewMap) {
                    viewMap.remove();
                    viewMap = null;
                }
            });
        });
    </script>
@endsection
