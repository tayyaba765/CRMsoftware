@extends('layouts_new.page-template')

@section('title', 'Dashboard')

@section('page', 'dashboard')

@section('heading', 'Dashboard')

@section('content')

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Montserrat', 'ui-sans-serif', 'system-ui', 'sans-serif']
                    },
                    colors: {
                        brand: {
                            DEFAULT: '#5b3fd4',
                            dark: '#4629b8',
                            soft: '#f1eefc'
                        },
                        page: '#f1f2f3',
                        ink: '#0e1726',
                        muted: '#6b7280'
                    }
                }
            }
        };
    </script>




    <!--
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">

                <div class="rounded-lg bg-white p-5 shadow">
                    <h2 class="text-sm text-muted">Students</h2>
                    <p class="mt-2 text-2xl font-bold">120</p>
                </div>

                <div class="rounded-lg bg-white p-5 shadow">
                    <h2 class="text-sm text-muted">Teachers</h2>
                    <p class="mt-2 text-2xl font-bold">25</p>
                </div>

            </div>
        -->
@endsection

@push('scripts')
    <script src="{{ asset('pages/dashboard.js') }}"></script>
@endpush
