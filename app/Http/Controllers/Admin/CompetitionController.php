<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    public function index(Request $request): View
    {
        $competitors = $this->loadAndRefreshCompetitors($request);

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', 'all'),
        ];

        $rows = collect($competitors)
            ->filter(function (array $item) use ($filters): bool {
                if ($filters['status'] !== 'all' && ($item['status'] ?? '') !== $filters['status']) {
                    return false;
                }

                if ($filters['q'] !== '' && ! str_contains(strtolower($item['domain'] ?? ''), strtolower($filters['q']))) {
                    return false;
                }

                return true;
            })
            ->sortByDesc('last_scan_at')
            ->map(function (array $item): array {
                return [
                    'id' => (int) ($item['id'] ?? 0),
                    'domain' => (string) ($item['domain'] ?? ''),
                    'total_products' => (int) ($item['total_products'] ?? 0),
                    'last_scan_human' => $this->humanTimeAgo((string) ($item['last_scan_at'] ?? now()->toIso8601String())),
                    'status' => (string) ($item['status'] ?? 'processing'),
                ];
            })
            ->values()
            ->all();

        return view('admin.competition.index', [
            'title' => 'Competition Monitor',
            'subtitle' => 'Add competitor domains, auto-process by AI pipeline, and inspect historical reports.',
            'filters' => $filters,
            'rows' => $rows,
            'stats' => [
                'tracked' => count($competitors),
                'processing' => collect($competitors)->where('status', 'processing')->count(),
                'success' => collect($competitors)->where('status', 'success')->count(),
                'failed' => collect($competitors)->where('status', 'failed')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (is_array($request->input('domain'))) {
            return redirect()
                ->route('admin.competition')
                ->withErrors(['domain' => 'Please enter one domain per submission.'])
                ->withInput();
        }

        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255'],
        ]);

        $domain = $this->normalizeDomain($validated['domain']);
        if ($domain === null) {
            return redirect()
                ->route('admin.competition')
                ->withErrors(['domain' => 'Invalid domain. Example: example.com'])
                ->withInput();
        }

        $competitors = $this->loadAndRefreshCompetitors($request);
        if (collect($competitors)->contains(fn (array $item): bool => ($item['domain'] ?? '') === $domain)) {
            return redirect()
                ->route('admin.competition')
                ->withErrors(['domain' => 'This domain is already in the list.'])
                ->withInput();
        }

        $seed = abs(crc32($domain));
        $products = 400 + ($seed % 2600);

        $competitors[] = [
            'id' => $this->nextId($competitors),
            'domain' => $domain,
            'status' => 'processing',
            'queued_at' => now()->toIso8601String(),
            'last_scan_at' => now()->subMinutes(2)->toIso8601String(),
            'total_products' => $products,
        ];

        $this->saveCompetitors($request, $competitors);

        return redirect()
            ->route('admin.competition')
            ->with('competition_status', "Domain {$domain} added. AI processing started.");
    }

    public function sync(Request $request, int $competitor): RedirectResponse
    {
        $competitors = $this->loadAndRefreshCompetitors($request);
        $index = collect($competitors)->search(fn (array $item): bool => (int) ($item['id'] ?? 0) === $competitor);

        if ($index === false) {
            return redirect()
                ->route('admin.competition')
                ->withErrors(['domain' => 'Competitor not found.']);
        }

        $item = $competitors[$index];
        $item['status'] = 'processing';
        $item['queued_at'] = now()->toIso8601String();
        $item['last_scan_at'] = now()->toIso8601String();
        $item['total_products'] = max(50, (int) ($item['total_products'] ?? 0) + (($competitor % 9) - 4));
        $competitors[$index] = $item;

        $this->saveCompetitors($request, $competitors);

        return redirect()
            ->route('admin.competition')
            ->with('competition_status', "Re-scan queued for {$item['domain']}. AI processing started.");
    }

    public function view(Request $request, int $competitor): View
    {
        $competitors = $this->loadAndRefreshCompetitors($request);
        $item = collect($competitors)->first(fn (array $row): bool => (int) ($row['id'] ?? 0) === $competitor);

        abort_if($item === null, 404);

        $report = $this->buildDemoReport($item);

        return view('admin.competition.view', [
            'title' => 'Competition Report',
            'subtitle' => 'Comprehensive AI report view (demo mode) generated from domain + products.',
            'competitor' => $item,
            'report' => $report,
        ]);
    }

    private function loadAndRefreshCompetitors(Request $request): array
    {
        $competitors = $request->session()->get('competition.competitors');
        if (! is_array($competitors)) {
            $competitors = $this->defaultCompetitors();
        }

        $now = now();
        $updated = false;

        foreach ($competitors as $index => $item) {
            if (($item['status'] ?? '') !== 'processing') {
                continue;
            }

            $queuedAt = isset($item['queued_at']) ? Carbon::parse($item['queued_at']) : $now->copy()->subMinutes(5);
            if ($queuedAt->diffInSeconds($now) < 25) {
                continue;
            }

            $seed = abs(crc32((string) ($item['domain'] ?? '').'-'.$queuedAt->timestamp));
            $isSuccess = ($seed % 100) >= 18;
            $item['status'] = $isSuccess ? 'success' : 'failed';
            $item['last_scan_at'] = $now->toIso8601String();

            if ($isSuccess) {
                $item['total_products'] = max(100, (int) ($item['total_products'] ?? 0) + (($seed % 11) - 5));
            }

            $competitors[$index] = $item;
            $updated = true;
        }

        if ($updated) {
            $this->saveCompetitors($request, $competitors);
        } else {
            // Ensure session is always initialized for later updates.
            $request->session()->put('competition.competitors', $competitors);
        }

        return $competitors;
    }

    private function saveCompetitors(Request $request, array $competitors): void
    {
        $request->session()->put('competition.competitors', array_values($competitors));
    }

    private function nextId(array $competitors): int
    {
        $max = collect($competitors)->max(fn (array $row): int => (int) ($row['id'] ?? 0));

        return ((int) $max) + 1;
    }

    private function defaultCompetitors(): array
    {
        return [
            [
                'id' => 1001,
                'domain' => 'trendhive.com',
                'status' => 'success',
                'queued_at' => now()->subDays(2)->toIso8601String(),
                'last_scan_at' => now()->subHours(25)->subMinutes(1)->toIso8601String(),
                'total_products' => 2662,
            ],
            [
                'id' => 1002,
                'domain' => 'streetnook.shop',
                'status' => 'processing',
                'queued_at' => now()->subMinutes(12)->toIso8601String(),
                'last_scan_at' => now()->subMinutes(12)->toIso8601String(),
                'total_products' => 1731,
            ],
            [
                'id' => 1003,
                'domain' => 'gadgetharbor.io',
                'status' => 'failed',
                'queued_at' => now()->subHours(9)->toIso8601String(),
                'last_scan_at' => now()->subHours(9)->toIso8601String(),
                'total_products' => 905,
            ],
        ];
    }

    private function normalizeDomain(string $input): ?string
    {
        $value = trim(strtolower($input));
        $value = preg_replace('/^https?:\/\//', '', $value) ?? $value;
        $value = explode('/', $value)[0] ?? $value;
        $value = explode('?', $value)[0] ?? $value;

        if ($value === '' || ! filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return null;
        }

        return $value;
    }

    private function humanTimeAgo(string $datetime): string
    {
        $time = Carbon::parse($datetime);
        $seconds = max(0, $time->diffInSeconds(now()));

        $days = intdiv($seconds, 86400);
        $seconds %= 86400;
        $hours = intdiv($seconds, 3600);
        $seconds %= 3600;
        $minutes = intdiv($seconds, 60);

        if ($days > 0) {
            return "{$days}d {$hours}h ago";
        }

        if ($hours > 0) {
            return "{$hours}h {$minutes}m ago";
        }

        return max(1, $minutes).'m ago';
    }

    private function buildDemoReport(array $competitor): array
    {
        $domain = (string) ($competitor['domain'] ?? 'unknown-domain.com');
        $seed = abs(crc32($domain));
        $products = [
            ['name' => 'Premium Cotton T-Shirt', 'our_price' => 18.00],
            ['name' => 'Smart Casual Hoodie', 'our_price' => 35.00],
            ['name' => 'Wireless Earbuds Pro', 'our_price' => 52.00],
            ['name' => 'AirFlex Running Shoes', 'our_price' => 62.00],
        ];

        $rows = [];
        $gapSum = 0.0;
        foreach ($products as $index => $product) {
            $variant = (($seed + ($index * 31)) % 13) - 6; // -6..+6
            $deltaPct = $variant / 100;
            $compPrice = round($product['our_price'] * (1 + $deltaPct), 2);
            $gap = round((($compPrice - $product['our_price']) / $product['our_price']) * 100, 1);
            $gapSum += $gap;

            $rows[] = [
                'product' => $product['name'],
                'our_price' => '$'.number_format($product['our_price'], 2),
                'competitor_price' => '$'.number_format($compPrice, 2),
                'gap' => ($gap > 0 ? '+' : '').$gap.'%',
                'signal' => $gap <= -3 ? 'Undercut' : ($gap >= 3 ? 'Advantage' : 'Parity'),
            ];
        }

        $avgGap = round($gapSum / max(1, count($rows)), 1);
        $risk = $avgGap < -2 ? 'High' : ($avgGap < 0.5 ? 'Medium' : 'Low');

        return [
            'generated_at' => now()->format('M d, Y h:i A'),
            'summary_cards' => [
                ['label' => 'Domain', 'value' => $domain, 'note' => 'Scanned target'],
                ['label' => 'Status', 'value' => strtoupper((string) ($competitor['status'] ?? 'processing')), 'note' => 'Pipeline state'],
                ['label' => 'Avg Gap', 'value' => ($avgGap > 0 ? '+' : '').$avgGap.'%', 'note' => 'Competitor vs our pricing'],
                ['label' => 'Risk', 'value' => $risk, 'note' => 'AI risk level (demo)'],
            ],
            'rows' => $rows,
            'dynamic_sections' => $this->dynamicSections($domain, $risk, $avgGap),
        ];
    }

    private function dynamicSections(string $domain, string $risk, float $avgGap): array
    {
        return [
            [
                'tag' => 'section',
                'attrs' => ['class' => 'competition-dynamic-block'],
                'children' => [
                    ['tag' => 'h3', 'attrs' => ['class' => 'competition-dynamic-title'], 'text' => 'Executive Summary'],
                    ['tag' => 'p', 'attrs' => ['class' => 'competition-dynamic-text'], 'text' => "AI demo scan for {$domain} indicates {$risk} competitive pressure with average gap ".(($avgGap > 0 ? '+' : '').$avgGap)."%. Focus on high-intent product pages first."],
                ],
            ],
            [
                'tag' => 'section',
                'attrs' => ['class' => 'competition-dynamic-block'],
                'children' => [
                    ['tag' => 'h3', 'attrs' => ['class' => 'competition-dynamic-title'], 'text' => 'Recommended Actions'],
                    [
                        'tag' => 'ul',
                        'attrs' => ['class' => 'competition-dynamic-list'],
                        'children' => [
                            ['tag' => 'li', 'text' => 'Launch comparison creatives for products where competitor is undercutting.'],
                            ['tag' => 'li', 'text' => 'Increase remarketing budget for products with parity pricing and high intent.'],
                            ['tag' => 'li', 'text' => 'Push social proof + review snippets to improve conversion rate.'],
                        ],
                    ],
                ],
            ],
            [
                'tag' => 'section',
                'attrs' => ['class' => 'competition-dynamic-block'],
                'children' => [
                    ['tag' => 'h3', 'attrs' => ['class' => 'competition-dynamic-title'], 'text' => 'Pipeline Note'],
                    ['tag' => 'p', 'attrs' => ['class' => 'competition-dynamic-text'], 'text' => 'Current UI uses demo data. Later, this block can be fully generated by your AI bot and rendered through dynamic tags from controller.'],
                ],
            ],
        ];
    }
}
