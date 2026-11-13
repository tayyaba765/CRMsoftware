@extends('layouts_new.page-template')

@section('title', 'Analytics')

@section('page', 'dashboard')

@section('heading', 'Analytics')

@section('content')

    <!-- VIEW 1: Analytics dashboard -->
    <div class="space-y-5">

        <!-- Stat cards -->
        <section id="stats" class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        </section>

        <!-- Charts row -->
        <section class="grid gap-5 xl:grid-cols-3">

            <!-- Bar chart -->
            <div class="card p-5 xl:col-span-2">

                <div class="flex flex-wrap items-start justify-between gap-3">

                    <div>
                        <h2 class="text-lg font-medium">
                            Registrations
                        </h2>

                        <p class="text-sm text-muted">
                            Hover a month to see values, click to select it.
                        </p>
                    </div>

                    <div id="barLegend" class="flex gap-2 text-xs">

                        <button type="button" data-s="Registered"
                            class="lg flex items-center gap-1.5 rounded-full border border-gray-200 px-3 py-1 text-muted">
                            <i class="h-2.5 w-2.5 rounded-full" style="background:#6ccf9f"></i>

                            Registered
                        </button>

                        <button type="button" data-s="Roles assigned"
                            class="lg flex items-center gap-1.5 rounded-full border border-gray-200 px-3 py-1 text-muted">
                            <i class="h-2.5 w-2.5 rounded-full" style="background:#e88a90"></i>

                            Roles assigned
                        </button>

                    </div>
                </div>

                <div id="barChart" class="mt-3"></div>

                <p id="barCaption" class="mt-2 text-sm text-muted"></p>

            </div>

            <!-- Pie chart -->
            <div class="card p-5">

                <h2 class="text-lg font-medium">
                    Users by role
                </h2>

                <p class="text-sm text-muted">
                    Click a slice to filter the table below.
                </p>

                <div id="pieChart" class="mt-3"></div>

                <p id="pieCaption" class="mt-1 min-h-[1.25rem] text-center text-sm text-muted"></p>

                <div id="pieLegend"
                    class="mt-3 flex flex-wrap justify-center gap-1.5 border-t border-dashed border-gray-200 pt-4 text-xs">
                </div>

            </div>

        </section>

        <!-- Details table -->
        <section id="detail" class="card p-5">

            <div class="mb-1 flex flex-wrap items-center gap-3">

                <h2 id="detailTitle" class="text-lg font-medium"></h2>

                <span id="roleChip"></span>

            </div>

            <p id="selInfo" class="mb-4 text-sm text-muted"></p>

            <div id="tblDash"></div>

        </section>

    </div>

@endsection

@push('scripts')
    <script src="{{ asset('pages/dashboard.js') }}"></script>
@endpush
