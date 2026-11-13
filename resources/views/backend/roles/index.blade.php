@extends('backend.layouts.master')

@section('content')

<div id="slot-content" class="max-w-5xl mx-auto p-6">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Roles &amp; Permissions</h1>
        <button type="button" id="addRoleBtn"
            class="inline-flex items-center gap-2 bg-brand hover:bg-brand/90 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition">
            <span class="text-lg leading-none">+</span> Add Role
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
        <div class="p-3 sm:p-4 overflow-x-auto">
            <table id="rolesTable" class="w-full text-sm text-left text-gray-700 dark:text-gray-200 min-w-[700px]">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-2 py-2" style="width: 7%;">Id</th>
                        <th class="px-2 py-2" style="width: 20%;">Role Name</th>
                        <th class="px-2 py-2" style="width: 18%;">Module</th>
                        <th class="px-2 py-2" style="width: 18%;">Parent Role</th>
                        <th class="px-2 py-2" style="width: 12%;">Permissions</th>
                        <th class="px-2 py-2" style="width: 25%;">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>

{{-- ============================================================ --}}
{{-- ADD / EDIT ROLE MODAL                                        --}}
{{-- ============================================================ --}}
<div id="roleModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" id="roleModalBackdrop"></div>
    <div class="relative min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-lg bg-white dark:bg-gray-800 rounded-xl shadow-xl">

            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                <h5 class="text-lg font-semibold text-gray-800 dark:text-gray-100" id="roleModalTitle">Add Role</h5>
                <button type="button" id="roleCloseBtn"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none">&times;</button>
            </div>

            <div class="px-5 py-4 space-y-4">
                <input type="hidden" id="roleId">

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role Name <span class="text-red-500">*</span></label>
                    <input type="text" id="roleName" placeholder="e.g. Manager"
                        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/50">
                    <p id="roleNameErr" class="text-xs text-red-500 mt-1 hidden"></p>
                </div>

                {{-- Module --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Module <span class="text-xs text-gray-400 font-normal">(optional)</span></label>
                    <select id="roleModule"
                        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/50">
                        <option value="">— None —</option>
                        @foreach ($modules as $mod)
                            <option value="{{ $mod->id }}">{{ $mod->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Parent Role --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Parent Role <span class="text-xs text-gray-400 font-normal">(optional)</span></label>
                    <select id="roleParent"
                        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/50">
                        <option value="">— None —</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r->id }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2 px-5 py-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" id="roleCancelBtn"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600">
                    Cancel
                </button>
                <button type="button" id="roleSaveBtn"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-brand hover:bg-brand/90">
                    Save Role
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- PERMISSIONS MODAL                                            --}}
{{-- ============================================================ --}}
<div id="permModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" id="permModalBackdrop"></div>
    <div class="relative min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-2xl bg-white dark:bg-gray-800 rounded-xl shadow-xl max-h-[85vh] flex flex-col">

            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                <h5 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                    Manage Permissions — <span id="permRoleName" class="text-brand"></span>
                </h5>
                <button type="button" id="permCloseBtn"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none">&times;</button>
            </div>

            <div id="permBody" class="overflow-y-auto p-5 flex-1">
                {{-- Module blocks injected by JS --}}
            </div>

            <div class="flex items-center justify-between px-5 py-4 border-t border-gray-200 dark:border-gray-700">
                <span class="text-xs text-gray-500 dark:text-gray-400"><span id="permCount">0</span> permission(s) selected</span>
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


{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<style>
    /* Role Permission module blocks */
    .mod-block  { border: 1px solid rgb(229 231 235); border-radius: 0.75rem; overflow: hidden; margin-bottom: 0.75rem; }
    html.dark .mod-block { border-color: rgb(55 65 81); }
    .mod-header { display: flex; justify-content: space-between; align-items: center;
                  padding: 0.6rem 1rem; background: rgba(91,63,212,0.06); font-weight: 600; font-size: 0.85rem; color: #374151; }
    html.dark .mod-header { background: rgba(91,63,212,0.15); color: #e5e7eb; }
    .mod-perms  { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 0.4rem 1rem; padding: 0.75rem 1rem; }
    .sel-all    { font-size: 0.75rem; color: #5b3fd4; cursor: pointer; font-weight: 500; }
</style>

<script>
$(function () {

    // CSRF token for all AJAX requests
    $.ajaxSetup({ headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") } });

    // ----------------------------------------------------------------
    // 1. DataTable
    // ----------------------------------------------------------------
    var table = $("#rolesTable").DataTable({
        processing : true,
        serverSide : true,
        autoWidth  : false,
        ajax       : "{{ route('roles.data') }}",
        columns    : [
            { data: "DT_RowIndex",       orderable: false, searchable: false },
            { data: "name",              orderable: true,  searchable: true  },
            { data: "module",            orderable: false, searchable: true  },
            { data: "parent",            orderable: false, searchable: false },
            { data: "permissions_count", orderable: false, searchable: false },
            { data: "action",            orderable: false, searchable: false },
        ]
    });

    // ----------------------------------------------------------------
    // 2. Role Modal — open / close helpers
    // ----------------------------------------------------------------
    function openRoleModal(id, name, moduleId, parentId) {
        // Reset form
        $("#roleNameErr").addClass("hidden").text("");
        $("#roleId").val(id || "");
        $("#roleName").val(name || "");
        $("#roleModule").val(moduleId || "");

        // Hide self in parent dropdown to prevent circular reference
        $("#roleParent option").show();
        if (id) $("#roleParent option[value='" + id + "']").hide();
        $("#roleParent").val(parentId || "");

        $("#roleModalTitle").text(id ? "Edit Role" : "Add Role");
        $("#roleSaveBtn").text(id ? "Update Role" : "Save Role");
        $("#roleModal").removeClass("hidden");
        setTimeout(function() { $("#roleName").focus(); }, 50);
    }

    function closeRoleModal() {
        $("#roleModal").addClass("hidden");
    }

    // Open for Add
    $("#addRoleBtn").on("click", function () {
        openRoleModal();
    });

    // Open for Edit — fetch current values from server
    $("#rolesTable").on("click", ".edit-role-btn", function () {
        var id = $(this).data("id");
        $.get("/roles/" + id + "/edit", function (res) {
            openRoleModal(res.role.id, res.role.name, res.role.module_id, res.role.parent_role_id);
        });
    });

    // Close modal buttons
    $("#roleCloseBtn, #roleCancelBtn").on("click", closeRoleModal);

    // ----------------------------------------------------------------
    // 3. Save Role (Add or Edit)
    // ----------------------------------------------------------------
    $("#roleSaveBtn").on("click", function () {
        var id       = $("#roleId").val();
        var name     = $("#roleName").val().trim();
        var moduleId = $("#roleModule").val();
        var parentId = $("#roleParent").val();

        // Basic client-side check
        if (!name) {
            $("#roleNameErr").removeClass("hidden").text("Role name is required.");
            return;
        }

        $("#roleSaveBtn").prop("disabled", true).text("Saving...");

        $.ajax({
            url  : id ? "/roles/" + id : "/roles",
            type : id ? "PUT" : "POST",
            data : { name: name, module_id: moduleId, parent_role_id: parentId },
            success: function (res) {
                if (res.status) {
                    closeRoleModal();
                    table.ajax.reload(null, false); // reload without resetting page
                } else {
                    alert(res.message || "Error saving role.");
                }
            },
            error: function (xhr) {
                var errors = xhr.responseJSON && xhr.responseJSON.errors;
                if (errors && errors.name) {
                    $("#roleNameErr").removeClass("hidden").text(errors.name[0]);
                } else {
                    alert("Something went wrong. Please try again.");
                }
            },
            complete: function () {
                var currentId = $("#roleId").val();
                $("#roleSaveBtn").prop("disabled", false).text(currentId ? "Update Role" : "Save Role");
            }
        });
    });

    // ----------------------------------------------------------------
    // 4. Delete Role
    // ----------------------------------------------------------------
    $("#rolesTable").on("click", ".delete-role-btn", function () {
        var id   = $(this).data("id");
        var name = $(this).data("name");

        if (!confirm('Delete role "' + name + '"? This cannot be undone.')) return;

        $.ajax({
            url  : "/roles/" + id,
            type : "DELETE",
            success: function (res) {
                if (res.status) {
                    table.ajax.reload(null, false);
                } else {
                    alert(res.message || "Could not delete role.");
                }
            },
            error: function (xhr) {
                alert(xhr.responseJSON && xhr.responseJSON.message || "Error deleting role.");
            }
        });
    });

    // ----------------------------------------------------------------
    // 5. Permissions Modal
    // ----------------------------------------------------------------
    var currentRoleId = null;

    function openPermModal()  { $("#permModal").removeClass("hidden"); }
    function closePermModal() { $("#permModal").addClass("hidden"); $("#permBody").empty(); currentRoleId = null; }

    // Build the module/permission checkboxes from the server response
    function renderPermissions(modules) {
        var $body = $("#permBody").empty();

        $.each(modules, function (i, mod) {
            var $block  = $('<div class="mod-block"></div>');
            var $header = $('<div class="mod-header">'
                + '<span>' + $("<div>").text(mod.name).html() + '</span>'
                + '<span class="sel-all" data-mod="' + mod.id + '">Select all</span>'
                + '</div>');
            var $grid   = $('<div class="mod-perms"></div>');

            $.each(mod.permissions, function (j, perm) {
                $grid.append(
                    '<label class="inline-flex items-center gap-2 text-sm text-gray-700">'
                    + '<input type="checkbox" class="perm-cb rounded border-gray-300" data-mod="' + mod.id + '" value="' + perm.id + '"'
                    + (perm.checked ? ' checked' : '') + '>'
                    + $("<div>").text(perm.name).html()
                    + '</label>'
                );
            });

            $block.append($header).append($grid);
            $body.append($block);
        });

        updateCount();
    }

    function updateCount() {
        $("#permCount").text($(".perm-cb:checked").length);
    }

    // Count changes
    $(document).on("change", ".perm-cb", updateCount);

    // Select all toggle per module
    $(document).on("click", ".sel-all", function () {
        var $boxes = $('.perm-cb[data-mod="' + $(this).data("mod") + '"]');
        var allChecked = $boxes.length === $boxes.filter(":checked").length;
        $boxes.prop("checked", !allChecked);
        updateCount();
    });

    // Open permissions modal
    $("#rolesTable").on("click", ".manage-perm-btn", function () {
        currentRoleId = $(this).data("id");
        $.get("/roles/" + currentRoleId + "/edit", function (res) {
            $("#permRoleName").text(res.role.name);
            renderPermissions(res.modules);
            openPermModal();
        }).fail(function () {
            alert("Could not load permissions.");
        });
    });

    // Close permissions modal
    $("#permCloseBtn, #permCancelBtn").on("click", closePermModal);

    // Save permissions
    $("#permSaveBtn").on("click", function () {
        if (!currentRoleId) return;

        // Collect checked permission IDs
        var ids = $(".perm-cb:checked").map(function () { return $(this).val(); }).get();

        $("#permSaveBtn").prop("disabled", true).text("Saving...");

        $.ajax({
            url  : "/roles/" + currentRoleId,
            type : "PUT",
            data : { permissions: ids },
            success: function () {
                closePermModal();
                table.ajax.reload(null, false);
            },
            error: function () {
                alert("Could not save permissions. Please try again.");
            },
            complete: function () {
                $("#permSaveBtn").prop("disabled", false).text("Save Permissions");
            }
        });
    });

    // Close modals with Escape key
    $(document).on("keydown", function (e) {
        if (e.key === "Escape") {
            closePermModal();
            closeRoleModal();
        }
    });

});
</script>

@endsection