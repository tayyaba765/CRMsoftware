@extends('backend.layouts.master')

@section('content')

<div id="slot-content" class="max-w-6xl mx-auto p-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2.5">
                <svg class="w-7 h-7 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 0 1 2 2m4 0a6 6 0 0 1-7.743 5.743L11 17H9v2H7v2H4a1 1 0 0 1-1-1v-2.586a1 1 0 0 1 .293-.707l5.964-5.964A6 6 0 1 1 21 9z"/>
                </svg>
                Permissions Management
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Create, organize, and categorize system permissions under module names.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('assignments.index') }}"
                class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium px-4 py-2.5 rounded-lg transition shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="8.5" cy="7" r="4" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 8v6M23 11h-6" />
                </svg>
                Direct Assignments
            </a>
            <button type="button" id="addPermissionBtn"
                class="inline-flex items-center gap-2 bg-brand hover:bg-brand/90 text-white font-medium px-4 py-2.5 rounded-lg shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Add Permission
            </button>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toastNotification" class="fixed top-5 right-5 z-50 transform transition-all duration-300 translate-y-[-20px] opacity-0 pointer-events-none">
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg bg-gray-900 text-white text-sm dark:bg-gray-100 dark:text-gray-900 font-medium">
            <span id="toastIcon" class="text-green-400 dark:text-green-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </span>
            <span id="toastMessage">Action completed successfully.</span>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700/60 overflow-hidden">
        <div class="p-3 sm:p-4 overflow-x-auto">
            <table id="permissionsTable" class="w-full text-sm text-left text-gray-700 dark:text-gray-200 min-w-[700px]">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-3 py-3" style="width: 7%;">#</th>
                        <th class="px-3 py-3" style="width: 30%;">Permission Name</th>
                        <th class="px-3 py-3" style="width: 25%;">Module Category</th>
                        <th class="px-3 py-3" style="width: 12%;">Guard</th>
                        <th class="px-3 py-3" style="width: 14%;">Created At</th>
                        <th class="px-3 py-3 text-center" style="width: 12%;">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>

<!-- ========================================================= -->
<!-- ADD / EDIT PERMISSION MODAL                               -->
<!-- ========================================================= -->
<div id="permissionModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-xs transition-opacity" id="permissionModalBackdrop"></div>

    <div class="relative min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 flex flex-col overflow-hidden">

            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h5 class="text-lg font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2" id="modalTitle">
                    Add Permission
                </h5>
                <button type="button" id="modalCloseBtn"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-2xl leading-none transition">&times;</button>
            </div>

            <!-- Modal Form Body -->
            <div class="p-6 space-y-4">
                <input type="hidden" id="permId">

                <!-- Permission Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Permission Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="permName" placeholder="e.g. view-users, create-posts, edit-roles"
                        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand/50 transition">
                    <p id="permNameErr" class="text-xs text-red-500 mt-1.5 hidden"></p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Use lowercase alphanumeric and hyphens or dots (e.g. <span class="font-mono">profile.view</span>).</p>
                </div>

                <!-- Module Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Module Category <span class="text-red-500">*</span>
                    </label>
                    <select id="permModule"
                        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/50 transition">
                        <option value="">— Select a Module Category —</option>
                        @foreach ($modules as $mod)
                            <option value="{{ $mod->id }}">{{ $mod->name }}</option>
                        @endforeach
                    </select>
                    <p id="permModuleErr" class="text-xs text-red-500 mt-1.5 hidden"></p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Categorizing under a module keeps role and user assignment organized.</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                <button type="button" id="modalCancelBtn"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    Cancel
                </button>
                <button type="button" id="savePermissionBtn"
                    class="px-5 py-2 rounded-lg text-sm font-medium text-white bg-brand hover:bg-brand/90 transition shadow-sm inline-flex items-center gap-2">
                    <span id="saveBtnSpinner" class="hidden">
                        <svg class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <span id="saveBtnText">Save Permission</span>
                </button>
            </div>

        </div>
    </div>
</div>

<!-- jQuery & DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>



<script>
$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const $modal = $('#permissionModal');
    const $toast = $('#toastNotification');
    let toastTimer = null;

    function showToast(msg, isSuccess = true) {
        clearTimeout(toastTimer);
        $('#toastMessage').text(msg);
        if (isSuccess) {
            $('#toastIcon').html('<svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>');
        } else {
            $('#toastIcon').html('<svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>');
        }
        $toast.removeClass('translate-y-[-20px] opacity-0 pointer-events-none')
              .addClass('translate-y-0 opacity-100');

        toastTimer = setTimeout(function () {
            $toast.addClass('translate-y-[-20px] opacity-0 pointer-events-none')
                  .removeClass('translate-y-0 opacity-100');
        }, 3200);
    }

    function openModal() {
        $modal.removeClass('hidden');
    }

    function closeModal() {
        $modal.addClass('hidden');
        $('#permId').val('');
        $('#permName').val('');
        $('#permModule').val('');
        clearErrors();
    }

    function clearErrors() {
        $('#permNameErr').addClass('hidden').text('');
        $('#permModuleErr').addClass('hidden').text('');
        $('#permName').removeClass('border-red-500');
        $('#permModule').removeClass('border-red-500');
    }

    /* Initialize DataTable */
    const table = $('#permissionsTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: "{{ route('permissions.data') }}",
        columns: [
            { data: 'DT_RowIndex',        name: 'DT_RowIndex',        orderable: false, searchable: false },
            { data: 'permission_name',    name: 'name',               orderable: true,  searchable: true  },
            { data: 'module_name',        name: 'module_name',        orderable: true,  searchable: true  },
            { data: 'guard_name_badge',   name: 'guard_name',         orderable: false, searchable: false },
            { data: 'created_date',       name: 'created_at',         orderable: true,  searchable: false },
            { data: 'action',             name: 'action',             orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[1, 'asc']]
    });

    /* Open Add Modal */
    $('#addPermissionBtn').on('click', function () {
        closeModal();
        $('#modalTitle').html(`
            <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Permission
        `);
        $('#saveBtnText').text('Create Permission');
        openModal();
        $('#permName').trigger('focus');
    });

    /* Open Edit Modal */
    $('#permissionsTable').on('click', '.edit-perm-btn', function () {
        const id = $(this).data('id');
        clearErrors();

        $.get(`/permissions/${id}/edit`, function (res) {
            if (res.status && res.permission) {
                $('#permId').val(res.permission.id);
                $('#permName').val(res.permission.name);
                $('#permModule').val(res.permission.module_id || '');
                $('#modalTitle').html(`
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                    Edit Permission
                `);
                $('#saveBtnText').text('Update Permission');
                openModal();
                $('#permName').trigger('focus');
            }
        }).fail(function (xhr) {
            showToast(xhr.responseJSON?.message || 'Unable to load permission details.', false);
        });
    });

    /* Save (Store or Update) */
    $('#savePermissionBtn').on('click', function () {
        clearErrors();
        const id = $('#permId').val();
        const name = $.trim($('#permName').val());
        const moduleId = $('#permModule').val();

        const isEdit = !!id;
        const url = isEdit ? `/permissions/${id}` : "{{ route('permissions.store') }}";
        const method = isEdit ? 'PUT' : 'POST';

        const $btn = $('#savePermissionBtn').prop('disabled', true);
        $('#saveBtnSpinner').removeClass('hidden');

        $.ajax({
            url: url,
            type: method,
            data: {
                name: name,
                module_id: moduleId
            },
            success: function (res) {
                closeModal();
                table.ajax.reload(null, false);
                showToast(res.message || 'Permission saved successfully.');
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errs = xhr.responseJSON.errors;
                    if (errs.name) {
                        $('#permName').addClass('border-red-500');
                        $('#permNameErr').removeClass('hidden').text(errs.name[0]);
                    }
                    if (errs.module_id) {
                        $('#permModule').addClass('border-red-500');
                        $('#permModuleErr').removeClass('hidden').text(errs.module_id[0]);
                    }
                } else {
                    showToast(xhr.responseJSON?.message || 'An error occurred while saving.', false);
                }
            },
            complete: function () {
                $btn.prop('disabled', false);
                $('#saveBtnSpinner').addClass('hidden');
            }
        });
    });

    /* Delete Permission */
    $('#permissionsTable').on('click', '.delete-perm-btn', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');

        if (!confirm(`Are you sure you want to delete permission "${name}"?\nThis will remove it from all roles and direct user assignments.`)) {
            return;
        }

        $.ajax({
            url: `/permissions/${id}`,
            type: 'DELETE',
            success: function (res) {
                table.ajax.reload(null, false);
                showToast(res.message || 'Permission deleted successfully.');
            },
            error: function (xhr) {
                showToast(xhr.responseJSON?.message || 'Unable to delete permission.', false);
            }
        });
    });

    /* Modal Close Triggers */
    $('#permissionModalBackdrop, #modalCancelBtn, #modalCloseBtn').on('click', closeModal);

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && !$modal.hasClass('hidden')) {
            closeModal();
        }
    });
});
</script>

@endsection
