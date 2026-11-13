@extends('backend.layouts.master')
@section('content')
    <div id="slot-content" class="p-6">
        <h1 class="text-xl font-semibold mb-6">Analytics</h1>

        {{-- KPIs --}}
        <section class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 mb-6">
            @foreach ([['Profiles', $kpis['profiles'], 'text-brand'], ['Organizations', $kpis['organizations'], 'text-brand'], ['Prospective', $kpis['prospective'], 'text-amber-500'], ['Working', $kpis['working'], 'text-emerald-500'], ['Closed', $kpis['closed'], 'text-gray-500'], ['Unassigned', $kpis['unassigned'], 'text-rose-500']] as [$label, $value, $tone])
                <div class="card p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
                    <p class="mt-1 text-3xl font-bold {{ $tone }}">{{ number_format($value) }}</p>
                </div>
            @endforeach
        </section>

        {{-- Status and income --}}
        <section class="grid gap-6 lg:grid-cols-3 mb-6">
            <div class="card p-5 lg:col-span-2">
                <h2 class="font-medium mb-4">Accounts by status</h2>
                <div class="relative h-64"><canvas id="chStatus"></canvas></div>
            </div>
            <div class="card p-5">
                <h2 class="font-medium mb-1">Profiles by income</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    Poor under {{ number_format($thresholds['poor']) }}, average up to
                    {{ number_format($thresholds['rich'] - 1) }}, rich from {{ number_format($thresholds['rich']) }}
                </p>
                <div class="relative h-56"><canvas id="chIncome"></canvas></div>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2 mb-6">
            <div class="card p-5">
                <h2 class="font-medium mb-4">Income band within each status (profiles)</h2>
                <div class="relative h-64"><canvas id="chIncomeStatus"></canvas></div>
            </div>
            <div class="card p-5">
                <h2 class="font-medium mb-4">Acquisition source</h2>
                <div class="relative h-64"><canvas id="chSource"></canvas></div>
            </div>
        </section>

        {{-- Organization profile --}}
        <section class="grid gap-6 lg:grid-cols-3 mb-6">
            <div class="card p-5">
                <h2 class="font-medium mb-4">Organizations by tier</h2>
                <div class="relative h-56"><canvas id="chTier"></canvas></div>
            </div>
            <div class="card p-5">
                <h2 class="font-medium mb-4">Organizations by ownership type</h2>
                <div class="relative h-56"><canvas id="chOwnership"></canvas></div>
            </div>
            <div class="card p-5">
                <h2 class="font-medium mb-4">Organizations by employee headcount</h2>
                <div class="relative h-56"><canvas id="chSize"></canvas></div>
            </div>
        </section>

        <section class="grid gap-6">
            <div class="card p-5">
                <h2 class="font-medium mb-4">Workload by assigned staff</h2>
                <div class="relative" style="height: {{ max(16, count($charts['staff']['labels']) * 2.4) }}rem">
                    <canvas id="chStaff"></canvas>
                </div>
            </div>
        </section>
    </div>

    <style>
        #slot-content .card {
            background: #fff;
            border-radius: .9rem;
            box-shadow: 0 2px 10px rgba(14, 23, 38, .05);
        }

        html.dark #slot-content .card {
            background: #111827;
            box-shadow: none;
            border: 1px solid #1f2937;
        }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script>
        (function dashboardCharts() {
            const D = @json($charts);

            const C = {
                profiles: '#6366f1',
                organizations: '#14b8a6',
                status: ['#f59e0b', '#10b981', '#a855f7', '#9ca3af'],
                bands: ['#f43f5e', '#f59e0b', '#10b981', '#9ca3af'],
                cats: ['#6366f1', '#14b8a6', '#f59e0b', '#ec4899', '#0ea5e9', '#9ca3af'],
            };

            const charts = {};
            const isDark = () => document.documentElement.classList.contains('dark');

            function make(id, config) {
                const el = document.getElementById(id);
                if (!el) return;
                if (charts[id]) charts[id].destroy();
                config.options = Object.assign({
                    responsive: true,
                    maintainAspectRatio: false
                }, config.options || {});
                charts[id] = new Chart(el, config);
            }

            const grouped = (labels, a, b) => ({
                labels,
                datasets: [{
                        label: 'Profiles',
                        data: a,
                        backgroundColor: C.profiles,
                        borderRadius: 4
                    },
                    {
                        label: 'Organizations',
                        data: b,
                        backgroundColor: C.organizations,
                        borderRadius: 4
                    },
                ],
            });
            const counts = {
                ticks: {
                    precision: 0
                },
                beginAtZero: true
            };
            const hideLegend = {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: counts
                }
            };

            function render() {
                Chart.defaults.color = isDark() ? '#9ca3af' : '#6b7280';
                Chart.defaults.borderColor = isDark() ? '#1f2937' : '#eef0f3';

                make('chStatus', {
                    type: 'bar',
                    data: grouped(D.status.labels, D.status.profiles, D.status.organizations),
                    options: {
                        scales: {
                            y: counts
                        }
                    },
                });

                const inc = D.income.labels.map((l, i) => ({
                        l,
                        v: D.income.data[i]
                    }))
                    .filter((x, i) => i < 3 || x.v > 0);

                make('chIncome', {
                    type: 'doughnut',
                    data: {
                        labels: inc.map(x => x.l),
                        datasets: [{
                            data: inc.map(x => x.v),
                            backgroundColor: C.bands.slice(0, inc.length),
                            borderWidth: 0
                        }]
                    },
                    options: {
                        cutout: '62%',
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    },
                });

                make('chIncomeStatus', {
                    type: 'bar',
                    data: {
                        labels: D.incomeByStatus.labels,
                        datasets: D.incomeByStatus.datasets.map((ds, i) => ({
                            label: ds.label,
                            data: ds.data,
                            backgroundColor: C.bands[i],
                        })),
                    },
                    options: {
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true,
                                ...counts
                            }
                        }
                    },
                });

                make('chSource', {
                    type: 'bar',
                    data: grouped(D.source.labels, D.source.profiles, D.source.organizations),
                    options: {
                        scales: {
                            y: counts
                        }
                    },
                });

                make('chTier', {
                    type: 'doughnut',
                    data: {
                        labels: D.tier.labels,
                        datasets: [{
                            data: D.tier.data,
                            backgroundColor: C.cats,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        cutout: '62%',
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    },
                });

                make('chOwnership', {
                    type: 'bar',
                    data: {
                        labels: D.ownership.labels,
                        datasets: [{
                            data: D.ownership.data,
                            backgroundColor: C.organizations,
                            borderRadius: 4
                        }]
                    },
                    options: hideLegend,
                });

                make('chSize', {
                    type: 'bar',
                    data: {
                        labels: D.size.labels,
                        datasets: [{
                            data: D.size.data,
                            backgroundColor: C.profiles,
                            borderRadius: 4
                        }]
                    },
                    options: hideLegend,
                });

                make('chStaff', {
                    type: 'bar',
                    data: {
                        labels: D.staff.labels,
                        datasets: [{
                                label: 'Profiles',
                                data: D.staff.profiles,
                                backgroundColor: C.profiles
                            },
                            {
                                label: 'Organizations',
                                data: D.staff.organizations,
                                backgroundColor: C.organizations
                            },
                        ],
                    },
                    options: {
                        indexAxis: 'y',
                        scales: {
                            x: {
                                stacked: true,
                                ...counts
                            },
                            y: {
                                stacked: true
                            }
                        }
                    },
                });
            }

            window.addEventListener('cork:theme', (e) => {
                document.documentElement.classList.toggle('dark', e.detail.dark);
                render();
            });
            document.documentElement.classList.toggle('dark', localStorage.getItem('cork-theme') === 'dark');

            render();
        })();
    </script>
@endsection
