@extends('backend.layouts.master')

@section('content')

<div id="slot-content" class="max-w-5xl mx-auto p-6">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Direct Permission Assignments</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Assign or revoke direct permissions for users in addition to their role permissions.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
        <div class="p-3 sm:p-4 overflow-x-auto">
            <table id="usersTable" class="w-full text-sm text-left text-gray-700 dark:text-gray-200 min-w-[700px]">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-2 py-2" style="width: 6%;">Id</th>
                        <th class="px-2 py-2" style="width: 22%;">Name</th>
                        <th class="px-2 py-2" style="width: 26%;">Email</th>
                        <th class="px-2 py-2" style="width: 18%;">Role</th>
                        <th class="px-2 py-2" style="width: 14%;">Direct Permissions</th>
                        <th class="px-2 py-2" style="width: 14%;">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- MANAGE USER PERMISSIONS MODAL                              -->
<!-- ========================================================= -->
<div id="permModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" id="permModalBackdrop"></div>

    <div class="relative min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-2xl bg-white dark:bg-gray-800 rounded-xl shadow-xl max-h-[85vh] flex flex-col">

            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                <h5 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                    Manage Permissions — <span id="permModalUserName" class="text-brand"></span>
                </h5>
                <button type="button" id="permCloseBtn"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none">&times;</button>
            </div>

            <div class="px-5 pt-3 text-xs text-gray-500 dark:text-gray-400">
                Greyed-out permissions come from the user's role(s) and can only be changed on the Roles page.
            </div>

            <div class="px-5 py-4 overflow-y-auto space-y-5" id="permModalBody">
                <!-- module blocks injected here -->
            </div>

            <div class="flex items-center justify-between px-5 py-4 border-t border-gray-200 dark:border-gray-700">
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    <span id="permSelectedCount">0</span> direct permission(s) selected
                </div>
                <div class="flex gap-2">
                    <button type="button" id="permCancelBtn"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="button" id="permSaveBtn"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-brand hover:bg-brand/90">
                        Save Permissions
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- DataTables (core only) -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<style>
    /* Direct Permission module blocks */
    .module-block { border: 1px solid rgb(229 231 235); border-radius: 0.75rem; overflow: hidden; }
    html.dark .module-block { border-color: rgb(55 65 81); }
    .module-heading {
        display: flex; align-items: center; justify-content: space-between;
        padding: 0.6rem 1rem; background: rgba(91,63,212,0.06);
        font-weight: 600; font-size: 0.85rem; color: #374151;
    }
    html.dark .module-heading { background: rgba(91,63,212,0.15); color: #e5e7eb; }
    .module-permissions {
        display: grid; grid-template-columns: repeat(2, minmax(0,1fr));
        gap: 0.4rem 1rem; padding: 0.75rem 1rem;
    }
    .module-select-all { font-size: 0.75rem; color: #5b3fd4; cursor: pointer; font-weight: 500; }
    .perm-via-role { opacity: .55; cursor: not-allowed; }
    .perm-via-role-tag { font-size: 0.65rem; color: #6b7280; margin-left: .25rem; }
    html.dark .perm-via-role-tag { color: #9ca3af; }
</style>

<script>
$(function () {

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    /* =========================================================
     * Helpers
     * ========================================================= */
    const $permModal = $('#permModal');
    let currentUserId = null;

    function esc(str) { return $('<div>').text(str).html(); }

    function openPermModal()  { $permModal.removeClass('hidden'); }
    function closePermModal() {
        $permModal.addClass('hidden');
        $('#permModalBody').empty();
        currentUserId = null;
    }

    /* =========================================================
     * Users DataTable
     * ========================================================= */
    const table = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: "{{ route('users.data') }}",
        columns: [
            { data: 'DT_RowIndex',        name: 'DT_RowIndex',        orderable: false, searchable: false },
            { data: 'name',               name: 'name',               orderable: true,  searchable: true  },
            { data: 'email',              name: 'email',              orderable: true,  searchable: true  },
            { data: 'roles',              name: 'roles',              orderable: false, searchable: false },
            { data: 'permissions_count',  name: 'permissions_count',  orderable: false, searchable: false },
            { data: 'action',             name: 'action',             orderable: false, searchable: false },
        ]
    });

    /* =========================================================
     * Permissions modal — build module blocks
     * ========================================================= */
    function renderModules(modules) {
        const $body = $('#permModalBody').empty();

        modules.forEach(function (mod) {
            const $block = $('<div class="module-block"></div>');

            const $heading = $(`
                <div class="module-heading">
                    <span>${esc(mod.name)}</span>
                    <span class="module-select-all" data-module="${mod.id}">Select all</span>
                </div>
            `);

            const $grid = $('<div class="module-permissions"></div>');

            mod.permissions.forEach(function (perm) {
                const viaRole  = !!perm.via_role;
                const checked  = (perm.checked || viaRole) ? 'checked' : '';
                const disabled = viaRole ? 'disabled' : '';
                const cls      = viaRole ? 'perm-via-role' : '';
                const tag      = viaRole ? '<span class="perm-via-role-tag">(via role)</span>' : '';

                $grid.append(`
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 ${cls}">
                        <input type="checkbox" class="perm-checkbox rounded border-gray-300 text-brand focus:ring-brand"
                               data-module="${mod.id}" value="${perm.id}" ${checked} ${disabled}>
                        ${esc(perm.name)}${tag}
                    </label>
                `);
            });

            $block.append($heading).append($grid);
            $body.append($block);
        });

        updateSelectedCount();
    }

    function updateSelectedCount() {
        $('#permSelectedCount').text($('.perm-checkbox:checked:not(:disabled)').length);
    }

    $(document).on('change', '.perm-checkbox', updateSelectedCount);

    $(document).on('click', '.module-select-all', function () {
        const moduleId = $(this).data('module');
        const $boxes = $(`.perm-checkbox[data-module="${moduleId}"]:not(:disabled)`);
        const allChecked = $boxes.length === $boxes.filter(':checked').length;
        $boxes.prop('checked', !allChecked);
        updateSelectedCount();
    });

    /* =========================================================
     * Open permissions modal
     * ========================================================= */
    $('#usersTable').on('click', '.manage-perm-btn', function () {
        const id = $(this).data('id');
        currentUserId = id;

        $.get(`/users/${id}/permissions`, function (res) {
            $('#permModalUserName').text(res.user.name);
            renderModules(res.modules);
            openPermModal();
        }).fail(function (xhr) {
            alert(xhr.responseJSON?.message ?? 'Unable to load permissions.');
        });
    });

    $('#permModalBackdrop, #permCancelBtn, #permCloseBtn').on('click', closePermModal);

    $(document).on('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (!$permModal.hasClass('hidden')) closePermModal();
    });

    /* =========================================================
     * Save permissions
     * ========================================================= */
    $('#permSaveBtn').on('click', function () {
        if (!currentUserId) return;

        const permissions = $('.perm-checkbox:checked:not(:disabled)').map(function () {
            return $(this).val();
        }).get();

        const $btn = $(this).prop('disabled', true).text('Saving...');

        $.ajax({
            url: `/users/${currentUserId}/permissions`,
            type: 'POST',
            data: { permissions: permissions },
            success: function () {
                closePermModal();
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Something went wrong. Please try again.');
            },
            complete: function () {
                $btn.prop('disabled', false).text('Save Permissions');
            }
        });
    });



    /* =========================================================
     * Delete user
     * ========================================================= */
    $('#usersTable').on('click', '.delete-user-btn', function () {
        const id = $(this).data('id');

        if (!confirm('Are you sure you want to delete this user?')) return;

        $.ajax({
            url: `/users/${id}/delete`,
            type: 'POST',
            success: function () {
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Unable to delete user.');
            }
        });
    });

});
</script>

@endsection