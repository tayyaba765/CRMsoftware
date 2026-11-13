<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    /**
     * Income bands for profiles. Adjust to your currency / market.
     * Poor    : income <  INCOME_POOR_BELOW
     * Average : INCOME_POOR_BELOW <= income < INCOME_RICH_FROM
     * Rich    : income >= INCOME_RICH_FROM
     */
    private const INCOME_POOR_BELOW = 30000;
    private const INCOME_RICH_FROM  = 100000;

    private const STATUSES = [
        'prospective' => 'Prospective',
        'working'     => 'Working',
        'postponed'   => 'Postponed',
        'closed'      => 'Closed',
    ];

    private const SOURCES = [
        'whatsapp'  => 'WhatsApp',
        'phone'     => 'Phone',
        'facebook'  => 'Facebook',
        'instagram' => 'Instagram',
        'in-person' => 'In-person',
        'other'     => 'Other',
    ];

    private const TIERS = [
        'tier 1' => 'Tier 1',
        'tier 2' => 'Tier 2',
        'tier 3' => 'Tier 3',
        'tier 4' => 'Tier 4',
    ];

    private const OWNERSHIP = [
        'private'    => 'Private',
        'public'     => 'Public',
        'non-profit' => 'Non Profit',
        'government' => 'Government',
    ];

    private const SIZES = [
        '2-15'    => '2-15',
        '15-50'   => '15-50',
        '50-100'  => '50-100',
        '100-500' => '100-500',
        '500+'    => '500+',
    ];

    private const BANDS = [
        'poor'    => 'Poor',
        'average' => 'Average',
        'rich'    => 'Rich',
        'unknown' => 'Not set',
    ];

    public function index()
    {
        $canProfiles = Gate::allows('viewAny', Profile::class);
        $canOrgs     = Gate::allows('viewAny', Organization::class);

        $profileStatus = $canProfiles ? $this->counts(Profile::class, 'status') : collect();
        $orgStatus     = $canOrgs ? $this->counts(Organization::class, 'status') : collect();

        // Income band x status (single query, bands derived in SQL).
              // Income band x status (bucketed in PHP so formats like "50,000" still work).
        $bandCounts = [];
        if ($canProfiles) {
            foreach (Profile::query()->select('status', 'income')->cursor() as $p) {
                $raw = is_string($p->income) ? preg_replace('/[^0-9.]/', '', $p->income) : $p->income;

                if ($raw === null || $raw === '') {
                    $band = 'unknown';
                } else {
                    $v = (float) $raw;
                    $band = $v < self::INCOME_POOR_BELOW ? 'poor'
                          : ($v < self::INCOME_RICH_FROM ? 'average' : 'rich');
                }

                $s = strtolower((string) $p->status);
                $bandCounts[$s][$band] = ($bandCounts[$s][$band] ?? 0) + 1;
            }
        }

        $bandTotals = collect(self::BANDS)->map(
            fn ($label, $key) => (int) collect($bandCounts)->sum(fn ($b) => $b[$key] ?? 0)
        );

        $incomeByStatus = [];
        foreach (self::BANDS as $bandKey => $bandLabel) {
            $incomeByStatus[] = [
                'label' => $bandLabel,
                'data'  => array_map(fn ($s) => $bandCounts[$s][$bandKey] ?? 0, array_keys(self::STATUSES)),
            ];
        }

        $charts = [
            'status' => [
                'labels'        => array_values(self::STATUSES),
                'profiles'      => $this->series(self::STATUSES, $profileStatus),
                'organizations' => $this->series(self::STATUSES, $orgStatus),
            ],
            'income' => [
                'labels' => array_values(self::BANDS),
                'data'   => collect(array_keys(self::BANDS))->map(fn ($k) => (int) ($bandTotals[$k] ?? 0))->all(),
            ],
            'incomeByStatus' => [
                'labels'   => array_values(self::STATUSES),
                'datasets' => $incomeByStatus,
            ],
            'source' => [
                'labels'        => array_values(self::SOURCES),
                'profiles'      => $this->series(self::SOURCES, $canProfiles ? $this->counts(Profile::class, 'source') : collect(), 'other'),
                'organizations' => $this->series(self::SOURCES, $canOrgs ? $this->counts(Organization::class, 'source') : collect(), 'other'),
            ],
            'tier' => [
                'labels' => array_values(self::TIERS),
                'data'   => $this->series(self::TIERS, $canOrgs ? $this->counts(Organization::class, 'tier') : collect()),
            ],
            'ownership' => [
                'labels' => array_values(self::OWNERSHIP),
                'data'   => $this->series(self::OWNERSHIP, $canOrgs ? $this->counts(Organization::class, 'ownership_type') : collect()),
            ],
            'size' => [
                'labels' => array_values(self::SIZES),
                'data'   => $this->series(self::SIZES, $canOrgs ? $this->counts(Organization::class, 'size') : collect()),
            ],
            'staff' => $this->staffWorkload($canProfiles, $canOrgs),
        ];

        $kpis = [
            'profiles'      => $canProfiles ? Profile::count() : 0,
            'organizations' => $canOrgs ? Organization::count() : 0,
            'working'       => (int) ($profileStatus['working'] ?? 0) + (int) ($orgStatus['working'] ?? 0),
            'prospective'   => (int) ($profileStatus['prospective'] ?? 0) + (int) ($orgStatus['prospective'] ?? 0),
            'closed'        => (int) ($profileStatus['closed'] ?? 0) + (int) ($orgStatus['closed'] ?? 0),
            'unassigned'    => ($canProfiles ? Profile::whereNull('assigned_to')->count() : 0)
                             + ($canOrgs ? Organization::whereNull('assigned_to')->count() : 0),
        ];

        $thresholds = [
            'poor' => self::INCOME_POOR_BELOW,
            'rich' => self::INCOME_RICH_FROM,
        ];

        return view('newdash', compact('charts', 'kpis', 'thresholds'));
    }

    /**
     * Count rows grouped by a column; keys are lower-cased.
     */
    private function counts(string $model, string $column): Collection
    {
        return $model::query()
            ->selectRaw("{$column} AS k, COUNT(*) AS total")
            ->groupBy($column)
            ->get()
            ->groupBy(fn ($r) => strtolower((string) $r->k))
            ->map(fn ($rows) => (int) $rows->sum('total'));
    }

    /**
     * Align counts to a fixed list of categories. Values that are not in the
     * list are added to $otherKey when given, otherwise ignored.
     */
    private function series(array $categories, Collection $counts, ?string $otherKey = null): array
    {
        $known = array_keys($categories);
        $leftover = $counts->except($known)->sum();

        return collect($known)->map(function ($key) use ($counts, $otherKey, $leftover) {
            $value = (int) ($counts[$key] ?? 0);
            return $key === $otherKey ? $value + (int) $leftover : $value;
        })->all();
    }

    /**
     * Profiles and organizations per assigned staff member.
     */
    private function staffWorkload(bool $canProfiles, bool $canOrgs): array
    {
        $load = fn (string $model) => $model::query()
            ->selectRaw('assigned_to, COUNT(*) AS total')
            ->groupBy('assigned_to')
            ->get()
            ->mapWithKeys(fn ($r) => [(int) ($r->assigned_to ?? 0) => (int) $r->total]);

        $profiles = $canProfiles ? $load(Profile::class) : collect();
        $orgs     = $canOrgs ? $load(Organization::class) : collect();
        $names    = User::pluck('name', 'id');

        $ids = $profiles->keys()->merge($orgs->keys())->unique()
            ->sortByDesc(fn ($id) => ($profiles[$id] ?? 0) + ($orgs[$id] ?? 0))
            ->values();

        return [
            'labels'        => $ids->map(fn ($id) => $id === 0 ? 'Unassigned' : ($names[$id] ?? "User #{$id}"))->all(),
            'profiles'      => $ids->map(fn ($id) => $profiles[$id] ?? 0)->all(),
            'organizations' => $ids->map(fn ($id) => $orgs[$id] ?? 0)->all(),
        ];
    }
}