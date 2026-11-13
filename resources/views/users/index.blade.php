@extends('backend.layouts.master')

@section('content')
    <div id="slot-content" class="max-w-5xl mx-auto p-6">

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">User Management</h1>
            <button type="button"
                class="inline-flex items-center gap-2 bg-brand hover:bg-brand/90 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition"
                id="createBtn">
                <span class="text-lg leading-none">+</span> Add User
            </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
            <div class="p-3 sm:p-4 overflow-x-auto">
                <table id="usersTable" class="w-full text-sm text-left text-gray-700 dark:text-gray-200 min-w-[700px]">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-2 py-2" style="width: 10%;">Id</th>
                        <th class="px-2 py-2" style="width: 20%;">Name</th>
                        <th class="px-2 py-2" style="width: 30%;">Email</th>
                        <th class="px-2 py-2" style="width: 70%;">Roles</th>
                        <th class="px-2 py-2" style="width: 30%;">Action</th>
                    </tr>


                    <tr class="bg-white dark:bg-gray-800 normal-case">
                        <th></th>
                        <th>
                            <!--          <input type="text" class="column-search w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-900 px-2 py-1 text-xs font-normal focus:outline-none focus:ring-1 focus:ring-brand"
                                                                       data-col="1" placeholder="Search name"> -->
                        </th>
                        <th>
                            <!--            <input type="text" class="column-search w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-900 px-2 py-1 text-xs font-normal focus:outline-none focus:ring-1 focus:ring-brand"
                                                                       data-col="2" placeholder="Search email"> -->
                        </th>
                        <th>
                            <!--   <input type="text" class="column-search w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-900 px-2 py-1 text-xs font-normal focus:outline-none focus:ring-1 focus:ring-brand"
                                                                       data-col="3" placeholder="Search role">  -->
                        </th>

                        <th></th>
                    </tr>
                </thead>
            </table>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- SHARED CREATE / EDIT MODAL (plain Tailwind, no Bootstrap)  -->
    <!-- Same markup is reused for both actions; JS toggles the     -->
    <!-- title, method and pre-fills fields when editing.           -->
    <!-- ========================================================= -->
    <div id="userModal" class="fixed inset-0 z-50 hidden">
        <!-- backdrop -->
        <div class="absolute inset-0 bg-black/50" id="userModalBackdrop"></div>

        <!-- panel -->
        <div class="relative min-h-full flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-xl">
                <form id="userForm">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h5 class="text-lg font-semibold text-gray-800 dark:text-gray-100" id="userModalLabel">Add User</h5>
                        <button type="button" id="closeModalBtn"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none">&times;</button>
                    </div>

                    <div class="px-5 py-4 space-y-4">

                        <input type="hidden" id="user_id" name="user_id">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                            <input type="text" id="name" name="name" required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                            <div class="text-red-600 text-xs mt-1" id="err_name"></div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input type="email" id="email" name="email" required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                            <div class="text-red-600 text-xs mt-1" id="err_email"></div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Password <span id="passwordHint" class="text-gray-400 font-normal"></span>
                            </label>
                            <input type="password" id="password" name="password"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                            <div class="text-red-600 text-xs mt-1" id="err_password"></div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Roles</label>
                            <div class="flex flex-wrap gap-x-4 gap-y-2">
                                @foreach ($roles as $role)
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input type="checkbox"
                                            class="role-checkbox rounded border-gray-300 text-brand focus:ring-brand"
                                            name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}">
                                        {{ $role->name }}
                                    </label>
                                @endforeach
                            </div>
                            <div class="text-red-600 text-xs mt-1" id="err_roles"></div>
                        </div>

                    </div>

                    <div class="flex justify-end gap-2 px-5 py-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" id="cancelBtn"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button type="submit" id="saveBtn"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-brand hover:bg-brand/90">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables (core only — no bootstrap css dependency, styled via tailwind below) -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <style>
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .badge.bg-info {
            background: #0dcaf0;
        }

        .badge.bg-warning {
            background: #ffc107;
            color: #000;
        }

        .badge.bg-danger {
            background: #dc3545;
        }

        .badge:hover {
            opacity: .85;
        }

    </style>

    <script>
        // Set up CSRF token once at the very top (outside ready, so it applies everywhere)
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {

            var modal = $('#userModal');

            function openModal() {
                modal.removeClass('hidden');
            }

            function closeModal() {
                modal.addClass('hidden');
            }

            // -------------------------
            // DataTable setup
            // -------------------------

            var table = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                orderCellsTop: true,
                ajax: "{{ route('users1.data') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'email',
                        name: 'email',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'roles',
                        name: 'roles',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // -------------------------
            // Reset form
            // -------------------------

            function resetForm() {
                $('#userForm')[0].reset();
                $('#user_id').val('');
                $('.role-checkbox').prop('checked', false);
                $('.text-red-600').text('');
            }

            // -------------------------
            // Open modal in CREATE mode
            // -------------------------

            $('#createBtn').on('click', function() {
                resetForm();
                $('#userModalLabel').text('Add User');
                $('#password').attr('required', true);
                $('#passwordHint').text('');
                openModal();
            });

            // -------------------------
            // EDIT button — delegated on document
            // -------------------------

            $(document).on('click', '.edit_btn', function() {
                var id = $(this).attr('data-id'); // use .attr() — safer than .data()
                console.log('Edit clicked, id =', id); // <- debug line

                resetForm();

                $.ajax({
                    url: '/users1/' + id + '/edit',
                    type: 'GET',

                    success: function(res) {
                        console.log('Edit response:', res);

                        $('#user_id').val(res.user.id);
                        $('#name').val(res.user.name);
                        $('#email').val(res.user.email);

                        res.roles.forEach(function(roleId) {
                            $('#role_' + roleId).prop('checked', true);
                        });

                        $('#userModalLabel').text('Edit User');
                        $('#password').removeAttr('required');
                        $('#passwordHint').text('(leave blank to keep current password)');

                        openModal();
                    },

                    error: function(xhr) {
                        console.log('Edit error:', xhr);
                        alert('Could not load user. Status: ' + xhr.status);
                    }
                });
            });

            // -------------------------
            // VIEW button
            // -------------------------

            $(document).on('click', '.view_btn', function() {
                var id = $(this).attr('data-id');

                $.ajax({
                    url: '/users1/' + id + '/view',
                    type: 'GET',
                    success: function(res) {
                        var u = res.data;
                        alert(
                            'Name: ' + u.name + '\n' +
                            'Email: ' + u.email + '\n' +
                            'Roles: ' + u.roles.join(', ')
                        );
                    },
                    error: function(xhr) {
                        alert('Could not load user. Status: ' + xhr.status);
                    }
                });
            });

            // -------------------------
            // Close modal
            // -------------------------

            $('#userModalBackdrop, #cancelBtn, #closeModalBtn').on('click', function() {
                closeModal();
            });

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModal();
                }
            });

            // -------------------------
            // Submit form (create or update)
            // -------------------------

            $('#userForm').on('submit', function(e) {
                e.preventDefault();
                $('.text-red-600').text('');

                var id = $('#user_id').val();

                var url;
                if (id) {
                    url = '/users1/' + id;
                } else {
                    url = "{{ route('users1.store') }}";
                }

                var formData = $(this).serialize();
                if (id) {
                    formData += '&_method=PUT';
                }

                $('#saveBtn').prop('disabled', true).text('Saving...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,

                    success: function() {
                        closeModal();
                        table.ajax.reload(null, false);
                    },

                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            for (var field in errors) {
                                var cleanField = field.replace('.*', '').replace('.0', '');
                                $('#err_' + cleanField).text(errors[field][0]);
                            }
                        } else {
                            var msg = 'Something went wrong. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            alert(msg);
                        }
                    },

                    complete: function() {
                        $('#saveBtn').prop('disabled', false).text('Save');
                    }
                });
            });

            // -------------------------
            // DELETE button — delegated on document
            // -------------------------

            $(document).on('click', '.delete_btn', function() {
                var id = $(this).attr('data-id');
                console.log('Delete clicked, id =', id); // <- debug line

                if (!confirm('Delete this user? This cannot be undone.')) {
                    return;
                }

                $.ajax({
                    url: '/users1/' + id,
                    type: 'DELETE',

                    success: function() {
                        table.ajax.reload(null, false);
                    },

                    error: function(xhr) {
                        var msg = 'Delete failed.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        alert(msg);
                    }
                });
            });

        });
    </script>
@endsection
