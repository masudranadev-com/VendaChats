<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function orders(Request $request): View
    {
        $orders = collect($this->ordersDataset());
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
        })->all();

        return view('admin.orders.index', [
            'title' => 'Orders',
            'subtitle' => 'Control payment checks, dispatch priorities, and delivery health from one clean operational view.',
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
            'orders' => $ordersForList,
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

    public function view(string $orderId): View
    {
        $order = $this->findOrderById($orderId);
        abort_unless($order !== null, 404);

        $previousOrders = collect($order['previous_orders']);
        $claims = (int) ($order['customer']['delivered_not_received_claims'] ?? 0);

        return view('admin.orders.view', [
            'title' => 'Order Details',
            'subtitle' => 'Review customer profile, fraud risk, product line items, and fulfillment trail.',
            'order' => $order,
            'fraud' => [
                'is_flagged' => $claims > 0,
                'claims' => $claims,
                'delivered' => $previousOrders->where('status', 'Delivered')->count(),
                'completed' => $previousOrders->where('status', 'Completed')->count(),
            ],
        ]);
    }

    public function confirm(string $orderId): RedirectResponse
    {
        $order = $this->findOrderById($orderId);
        abort_unless($order !== null, 404);

        return redirect()
            ->route('admin.orders.invoice', ['orderId' => $order['id']])
            ->with('success', "Order {$order['id']} confirmed. Invoice generated with demo data.");
    }

    public function invoice(string $orderId): View
    {
        $order = $this->findOrderById($orderId);
        abort_unless($order !== null, 404);

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

    private function findOrderById(string $orderId): ?array
    {
        foreach ($this->ordersDataset() as $order) {
            if (($order['id'] ?? '') === $orderId) {
                return $order;
            }
        }

        return null;
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
