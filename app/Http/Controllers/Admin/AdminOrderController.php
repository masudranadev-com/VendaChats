<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function orders(Request $request): View
    {
        $refreshToken = (string) (
            $request->session()->get('auth.refresh_token')
            ?? $request->session()->get('refresh_token')
            ?? ''
        );

        $orders = collect($this->ordersDataset())
            ->map(fn (array $order): array => $this->withManualDiscount($order));
        $customerFilter = trim((string) $request->query('customer_id', ''));

        if ($customerFilter !== '') {
            $orders = $orders->filter(function (array $order) use ($customerFilter): bool {
                return ($order['customer']['id'] ?? '') === $customerFilter;
            })->values();
        }

        $ordersForList = $orders->map(function (array $order): array {
            return [
                'id' => $order['id'],
                'placed_at' => $order['placed_at'],
                'customer' => $order['customer']['name'],
                'customer_id' => $order['customer']['id'],
                'location' => $order['customer']['location'],
                'items' => collect($order['products'])->sum('qty'),
                'amount' => $this->formatBdt((float) $order['totals']['grand_total']),
                'payment' => $order['payment'],
                'channel' => $order['channel'],
                'status' => $order['status'],
                'progress' => (int) $order['progress'],
            ];
        })->values();

        $perPage = 3;
        $currentPage = max(1, (int) $request->query('page', 1));
        $lastPage = max(1, (int) ceil($ordersForList->count() / $perPage));
        $currentPage = min($currentPage, $lastPage);
        $ordersPaginator = new LengthAwarePaginator(
            $ordersForList->forPage($currentPage, $perPage)->values()->all(),
            $ordersForList->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.orders.index', [
            'title' => 'Orders',
            'subtitle' => 'Control payment checks, dispatch priorities, and delivery health from one clean operational view.',
            'ordersApiBaseUrl' => rtrim((string) config('services.backend.url', 'http://localhost:8082'), '/'),
            'ordersRefreshToken' => $refreshToken,
            'metrics' => [
                ['label' => 'Orders Today', 'value' => '142', 'meta' => '+19 vs yesterday'],
                ['label' => 'Gross Revenue', 'value' => 'BDT 218K', 'meta' => '+11.6% this week'],
                ['label' => 'Pending Dispatch', 'value' => '27', 'meta' => '9 need action in 2h'],
            ],
            'pipeline' => [
                ['name' => 'Total Order', 'count' => 12, 'tone' => 'primary'],
                ['name' => 'Rejected Order', 'count' => 18, 'tone' => 'danger'],
                ['name' => 'Pending Order', 'count' => 43, 'tone' => 'warning'],
                ['name' => 'Completed Order', 'count' => 6, 'tone' => 'success'],
            ],
            'orders' => $ordersPaginator,
            'watchlist' => [
                ['title' => '6 orders in exception queue need courier reassignment.', 'note' => 'Focus on Rajshahi and Sylhet lanes before 3:00 PM.'],
                ['title' => '4 COD orders above BDT 5,000 require call confirmation.', 'note' => 'Mark as verified before assigning riders.'],
                ['title' => 'Messenger-origin orders convert slower after 10 PM.', 'note' => 'Use instant payment link in handoff replies.'],
            ],
            'courierHealth' => [
                ['name' => 'Pathao', 'on_time' => 92],
                ['name' => 'SteadFast', 'on_time' => 84],
                ['name' => 'RedX', 'on_time' => 78],
                ['name' => 'Sundarban', 'on_time' => 69],
            ],
        ]);
    }

    public function view(Request $request, string $orderId): View
    {
        $refreshToken = (string) (
            $request->session()->get('auth.refresh_token')
            ?? $request->session()->get('refresh_token')
            ?? ''
        );

        return view('admin.orders.view', [
            'title' => 'Order Details',
            'subtitle' => 'Review customer profile, fraud risk, product line items, and fulfillment trail.',
            'invoiceEnabled' => true,
            'ordersApiBaseUrl' => rtrim((string) config('services.backend.url', 'http://localhost:8082'), '/'),
            'ordersRefreshToken' => $refreshToken,
            'orderId' => $orderId,
        ]);
    }

    public function applyDiscount(Request $request, string $orderId): RedirectResponse
    {
        $order = $this->findOrderById($orderId, false);
        abort_unless($order !== null, 404);

        $validated = $request->validate([
            'coupon_code' => ['nullable', 'string', 'max:32'],
            'discount_type' => ['required', 'in:fixed,percent'],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
        ]);

        $discountType = (string) $validated['discount_type'];
        $discountValue = (float) $validated['discount_value'];

        if ($discountType === 'percent' && $discountValue > 100) {
            return redirect()
                ->route('admin.orders.view', ['orderId' => $order['id']])
                ->withErrors(['discount_value' => 'Percentage discount cannot be more than 100%.'])
                ->withInput();
        }

        $manualAmount = $this->calculateManualDiscountAmount(
            $order['totals'],
            $discountType,
            $discountValue
        );

        if ($manualAmount <= 0) {
            return redirect()
                ->route('admin.orders.view', ['orderId' => $order['id']])
                ->withErrors(['discount_value' => 'Discount amount exceeds what can be applied to this order.'])
                ->withInput();
        }

        $couponCode = strtoupper(trim((string) ($validated['coupon_code'] ?? '')));

        $request->session()->put($this->discountSessionKey($order['id']), [
            'coupon_code' => $couponCode !== '' ? $couponCode : null,
            'type' => $discountType,
            'value' => $discountValue,
        ]);

        return redirect()
            ->route('admin.orders.view', ['orderId' => $order['id']])
            ->with('success', "Manual discount applied to {$order['id']}.");
    }

    public function removeDiscount(Request $request, string $orderId): RedirectResponse
    {
        $order = $this->findOrderById($orderId, false);
        abort_unless($order !== null, 404);

        $request->session()->forget($this->discountSessionKey($order['id']));

        return redirect()
            ->route('admin.orders.view', ['orderId' => $order['id']])
            ->with('success', "Manual discount removed from {$order['id']}.");
    }

    public function confirm(string $orderId): RedirectResponse
    {
        $order = $this->findOrderById($orderId);
        abort_unless($order !== null, 404);

        if ($this->isInvoiceDisabledForStatus((string) ($order['status'] ?? ''))) {
            return redirect()
                ->route('admin.orders.view', ['orderId' => $order['id']])
                ->withErrors(['invoice' => 'Invoice is disabled for canceled orders.']);
        }

        return redirect()
            ->route('admin.orders.invoice', ['orderId' => $order['id']])
            ->with('success', "Order {$order['id']} confirmed. Invoice generated with demo data.");
    }

    public function invoice(Request $request, string $orderId): View|RedirectResponse
    {
        $order = $this->findOrderForPreview($orderId, (string) $request->query('status', ''));
        abort_unless($order !== null, 404);

        if ($this->isInvoiceDisabledForStatus((string) ($order['status'] ?? ''))) {
            return redirect()
                ->route('admin.orders.view', ['orderId' => $order['id']])
                ->withErrors(['invoice' => 'Invoice is disabled for canceled orders.']);
        }

        return view('admin.orders.invoice', [
            'title' => 'Invoice',
            'subtitle' => 'Demo invoice preview generated from order data.',
            'order' => $order,
            'invoice' => [
                'code' => $order['invoice_code'],
                'issued_at' => now()->format('d M Y, h:i A'),
                'subtotal_text' => $this->formatBdt((float) $order['totals']['subtotal']),
                'shipping_text' => $this->formatBdt((float) $order['totals']['shipping_fee']),
                'discount_text' => $this->formatBdt((float) $order['totals']['discount']),
                'grand_total_text' => $this->formatBdt((float) $order['totals']['grand_total']),
            ],
        ]);
    }

    private function findOrderById(string $orderId, bool $withManualDiscount = true): ?array
    {
        foreach ($this->ordersDataset() as $order) {
            if (($order['id'] ?? '') === $orderId) {
                return $withManualDiscount ? $this->withManualDiscount($order) : $order;
            }
        }

        return null;
    }

    private function findOrderForPreview(string $orderId, string $statusHint = ''): ?array
    {
        $order = $this->findOrderById($orderId);
        if ($order === null) {
            $fallbackOrder = $this->ordersDataset()[0] ?? null;
            if (! is_array($fallbackOrder)) {
                return null;
            }

            $preview = $this->withManualDiscount($fallbackOrder);
            $preview['id'] = $orderId;
            $invoiceSuffix = trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', strtoupper($orderId)), '-');
            $preview['invoice_code'] = 'INV-' . ($invoiceSuffix !== '' ? $invoiceSuffix : 'DEMO');
            $order = $preview;
        }

        $normalizedHint = $this->normalizeStatus($statusHint);
        if ($normalizedHint !== '') {
            $order['status'] = $this->presentStatus($normalizedHint);
        }

        return $order;
    }

    private function isInvoiceDisabledForStatus(string $status): bool
    {
        return in_array(
            $this->normalizeStatus($status),
            ['cancel_on_called', 'cancel_on_confirmation'],
            true
        );
    }

    private function normalizeStatus(string $status): string
    {
        $normalized = strtolower(trim($status));
        $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized) ?? '';

        return trim($normalized, '_');
    }

    private function presentStatus(string $status): string
    {
        $parts = array_filter(explode('_', $this->normalizeStatus($status)));

        return implode(' ', array_map(static fn (string $part): string => ucfirst($part), $parts));
    }

    private function discountSessionKey(string $orderId): string
    {
        return "admin.orders.manual_discounts.{$orderId}";
    }

    private function withManualDiscount(array $order): array
    {
        $orderId = (string) ($order['id'] ?? '');
        $manual = session($this->discountSessionKey($orderId), []);

        if (! is_array($manual)) {
            $manual = [];
        }

        $discountType = in_array(($manual['type'] ?? ''), ['fixed', 'percent'], true)
            ? (string) $manual['type']
            : 'fixed';
        $discountValue = max(0, (float) ($manual['value'] ?? 0));
        $manualAmount = $this->calculateManualDiscountAmount($order['totals'], $discountType, $discountValue);

        $subtotal = max(0, (float) ($order['totals']['subtotal'] ?? 0));
        $shippingFee = max(0, (float) ($order['totals']['shipping_fee'] ?? 0));
        $baseDiscount = max(0, (float) ($order['totals']['discount'] ?? 0));
        $totalDiscount = $baseDiscount + $manualAmount;

        $couponCode = strtoupper(trim((string) ($manual['coupon_code'] ?? '')));

        $order['totals']['discount'] = $totalDiscount;
        $order['totals']['grand_total'] = max(0, $subtotal + $shippingFee - $totalDiscount);
        $order['manual_discount'] = [
            'is_applied' => $manualAmount > 0 || $couponCode !== '',
            'coupon_code' => $couponCode !== '' ? $couponCode : null,
            'type' => $discountType,
            'value' => $discountValue,
            'amount' => $manualAmount,
        ];

        return $order;
    }

    private function calculateManualDiscountAmount(array $totals, string $discountType, float $discountValue): float
    {
        $subtotal = max(0, (float) ($totals['subtotal'] ?? 0));
        $shippingFee = max(0, (float) ($totals['shipping_fee'] ?? 0));
        $baseDiscount = max(0, (float) ($totals['discount'] ?? 0));
        $maxManualDiscount = max(0, $subtotal + $shippingFee - $baseDiscount);

        $rawAmount = $discountType === 'percent'
            ? $subtotal * ($discountValue / 100)
            : $discountValue;
        $roundedAmount = max(0, round($rawAmount));

        return min($roundedAmount, $maxManualDiscount);
    }

    private function formatBdt(float $amount): string
    {
        return 'BDT ' . number_format($amount, 0);
    }

    private function ordersDataset(): array
    {
        return [
            [
                'id' => 'ORD-90341',
                'invoice_code' => 'INV-90341-A',
                'placed_at' => 'Today, 09:14 AM',
                'status' => 'Payment Review',
                'progress' => 28,
                'payment' => 'COD',
                'channel' => 'Messenger',
                'shipping_method' => 'Standard Home Delivery',
                'customer' => [
                    'id' => 'USR-1002',
                    'name' => 'Ayesha Rahman',
                    'email' => 'ayesha.rahman@example.com',
                    'phone' => '+8801711223344',
                    'location' => 'Dhanmondi, Dhaka',
                    'address' => 'House 14, Road 7, Dhanmondi R/A, Dhaka-1209',
                    'delivered_not_received_claims' => 1,
                ],
                'products' => [
                    [
                        'name' => 'Premium Cotton T-Shirt',
                        'sku' => 'SKU-TS-2109',
                        'variant' => 'Black / L',
                        'qty' => 1,
                        'unit_price' => 1150,
                        'image' => asset('assets/images/products/premium-cotton-tshirt.svg'),
                    ],
                    [
                        'name' => 'Smart Casual Hoodie',
                        'sku' => 'SKU-HD-1231',
                        'variant' => 'Navy / M',
                        'qty' => 1,
                        'unit_price' => 1890,
                        'image' => asset('assets/images/products/smart-casual-hoodie.svg'),
                    ],
                    [
                        'name' => 'Leather Office Backpack',
                        'sku' => 'SKU-BP-9920',
                        'variant' => 'Brown / 20L',
                        'qty' => 1,
                        'unit_price' => 2780,
                        'image' => asset('assets/images/products/leather-office-backpack.svg'),
                    ],
                ],
                'totals' => [
                    'subtotal' => 5820,
                    'shipping_fee' => 180,
                    'discount' => 1350,
                    'grand_total' => 4650,
                ],
                'previous_orders' => [
                    ['id' => 'ORD-90109', 'date' => '7 days ago', 'status' => 'Completed', 'amount' => 3250, 'issue' => 'None'],
                    ['id' => 'ORD-89987', 'date' => '21 days ago', 'status' => 'Delivered', 'amount' => 2810, 'issue' => 'Customer claimed not received'],
                    ['id' => 'ORD-89855', 'date' => '34 days ago', 'status' => 'Completed', 'amount' => 1790, 'issue' => 'None'],
                ],
            ],
            [
                'id' => 'ORD-90339',
                'invoice_code' => 'INV-90339-B',
                'placed_at' => 'Today, 08:42 AM',
                'status' => 'Ready to Dispatch',
                'progress' => 64,
                'payment' => 'Paid',
                'channel' => 'Website',
                'shipping_method' => 'Express Courier',
                'customer' => [
                    'id' => 'USR-1001',
                    'name' => 'Mahmud Hasan',
                    'email' => 'mahmud.hasan@example.com',
                    'phone' => '+8801888556677',
                    'location' => 'Uttara, Dhaka',
                    'address' => 'Sector 7, Uttara, Dhaka-1230',
                    'delivered_not_received_claims' => 0,
                ],
                'products' => [
                    [
                        'name' => 'Essential Sports Cap',
                        'sku' => 'SKU-CAP-3381',
                        'variant' => 'Charcoal / Free Size',
                        'qty' => 1,
                        'unit_price' => 1290,
                        'image' => asset('assets/images/products/airflex-running-shoes.svg'),
                    ],
                ],
                'totals' => [
                    'subtotal' => 1290,
                    'shipping_fee' => 0,
                    'discount' => 0,
                    'grand_total' => 1290,
                ],
                'previous_orders' => [
                    ['id' => 'ORD-90031', 'date' => '11 days ago', 'status' => 'Completed', 'amount' => 2410, 'issue' => 'None'],
                    ['id' => 'ORD-89772', 'date' => '48 days ago', 'status' => 'Delivered', 'amount' => 1580, 'issue' => 'None'],
                ],
            ],
            [
                'id' => 'ORD-90332',
                'invoice_code' => 'INV-90332-C',
                'placed_at' => 'Today, 07:55 AM',
                'status' => 'In Transit',
                'progress' => 82,
                'payment' => 'Paid',
                'channel' => 'WhatsApp',
                'shipping_method' => 'Priority Delivery',
                'customer' => [
                    'id' => 'USR-1004',
                    'name' => 'Nusrat Jahan',
                    'email' => 'nusrat.jahan@example.com',
                    'phone' => '+8801799446655',
                    'location' => 'Chawkbazar, Chattogram',
                    'address' => 'Chawkbazar Main Road, Chattogram',
                    'delivered_not_received_claims' => 0,
                ],
                'products' => [
                    [
                        'name' => 'AirFlex Running Shoes',
                        'sku' => 'SKU-SH-3318',
                        'variant' => 'White / 41',
                        'qty' => 1,
                        'unit_price' => 2450,
                        'image' => asset('assets/images/products/airflex-running-shoes.svg'),
                    ],
                    [
                        'name' => 'Performance Ankle Socks',
                        'sku' => 'SKU-SK-2002',
                        'variant' => 'Pair / Black',
                        'qty' => 1,
                        'unit_price' => 650,
                        'image' => asset('assets/images/products/premium-cotton-tshirt.svg'),
                    ],
                ],
                'totals' => [
                    'subtotal' => 3100,
                    'shipping_fee' => 120,
                    'discount' => 240,
                    'grand_total' => 2980,
                ],
                'previous_orders' => [
                    ['id' => 'ORD-89944', 'date' => '19 days ago', 'status' => 'Completed', 'amount' => 4320, 'issue' => 'None'],
                    ['id' => 'ORD-89322', 'date' => '61 days ago', 'status' => 'Completed', 'amount' => 1880, 'issue' => 'None'],
                ],
            ],
            [
                'id' => 'ORD-90318',
                'invoice_code' => 'INV-90318-D',
                'placed_at' => 'Today, 06:28 AM',
                'status' => 'Delayed',
                'progress' => 51,
                'payment' => 'COD',
                'channel' => 'Facebook Post',
                'shipping_method' => 'Outside Dhaka Delivery',
                'customer' => [
                    'id' => 'USR-1003',
                    'name' => 'Riad Karim',
                    'email' => 'riad.karim@example.com',
                    'phone' => '+8801677110099',
                    'location' => 'Rajshahi Sadar',
                    'address' => 'Upashahar, Rajshahi Sadar, Rajshahi',
                    'delivered_not_received_claims' => 2,
                ],
                'products' => [
                    [
                        'name' => 'Wireless Earbuds Pro',
                        'sku' => 'SKU-EB-4412',
                        'variant' => 'Pearl White',
                        'qty' => 1,
                        'unit_price' => 3250,
                        'image' => asset('assets/images/products/wireless-earbuds-pro.svg'),
                    ],
                    [
                        'name' => 'Smart Casual Hoodie',
                        'sku' => 'SKU-HD-1231',
                        'variant' => 'Gray / L',
                        'qty' => 1,
                        'unit_price' => 1890,
                        'image' => asset('assets/images/products/smart-casual-hoodie.svg'),
                    ],
                    [
                        'name' => 'Premium Cotton T-Shirt',
                        'sku' => 'SKU-TS-2109',
                        'variant' => 'White / XL',
                        'qty' => 1,
                        'unit_price' => 1150,
                        'image' => asset('assets/images/products/premium-cotton-tshirt.svg'),
                    ],
                    [
                        'name' => 'Urban Leather Belt',
                        'sku' => 'SKU-BT-5510',
                        'variant' => 'Black / 36',
                        'qty' => 1,
                        'unit_price' => 980,
                        'image' => asset('assets/images/products/leather-office-backpack.svg'),
                    ],
                ],
                'totals' => [
                    'subtotal' => 7270,
                    'shipping_fee' => 200,
                    'discount' => 1350,
                    'grand_total' => 6120,
                ],
                'previous_orders' => [
                    ['id' => 'ORD-90202', 'date' => '5 days ago', 'status' => 'Delivered', 'amount' => 3680, 'issue' => 'Customer claimed not received'],
                    ['id' => 'ORD-90011', 'date' => '17 days ago', 'status' => 'Delivered', 'amount' => 2490, 'issue' => 'Customer claimed not received'],
                    ['id' => 'ORD-89761', 'date' => '32 days ago', 'status' => 'Completed', 'amount' => 4210, 'issue' => 'None'],
                ],
            ],
            [
                'id' => 'ORD-90303',
                'invoice_code' => 'INV-90303-E',
                'placed_at' => 'Yesterday, 11:11 PM',
                'status' => 'Delivered',
                'progress' => 100,
                'payment' => 'Paid',
                'channel' => 'Instagram',
                'shipping_method' => 'Standard Home Delivery',
                'customer' => [
                    'id' => 'USR-1001',
                    'name' => 'Sumi Akter',
                    'email' => 'sumi.akter@example.com',
                    'phone' => '+8801555223344',
                    'location' => 'Sylhet Sadar',
                    'address' => 'Zindabazar, Sylhet Sadar, Sylhet',
                    'delivered_not_received_claims' => 0,
                ],
                'products' => [
                    [
                        'name' => 'Daily Cotton Scarf',
                        'sku' => 'SKU-SCF-7401',
                        'variant' => 'Beige',
                        'qty' => 1,
                        'unit_price' => 980,
                        'image' => asset('assets/images/products/premium-cotton-tshirt.svg'),
                    ],
                ],
                'totals' => [
                    'subtotal' => 980,
                    'shipping_fee' => 0,
                    'discount' => 0,
                    'grand_total' => 980,
                ],
                'previous_orders' => [
                    ['id' => 'ORD-89877', 'date' => '26 days ago', 'status' => 'Completed', 'amount' => 1890, 'issue' => 'None'],
                    ['id' => 'ORD-89412', 'date' => '72 days ago', 'status' => 'Completed', 'amount' => 1240, 'issue' => 'None'],
                ],
            ],
        ];
    }
}
