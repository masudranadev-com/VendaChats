<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
     public function orders()
    {
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
            'orders' => [
                [
                    'id' => 'ORD-90341',
                    'placed_at' => 'Today, 09:14 AM',
                    'customer' => 'Ayesha Rahman',
                    'location' => 'Dhanmondi, Dhaka',
                    'items' => 3,
                    'amount' => 'BDT 4,650',
                    'payment' => 'COD',
                    'channel' => 'Messenger',
                    'status' => 'Payment Review',
                    'progress' => 28,
                ],
                [
                    'id' => 'ORD-90339',
                    'placed_at' => 'Today, 08:42 AM',
                    'customer' => 'Mahmud Hasan',
                    'location' => 'Uttara, Dhaka',
                    'items' => 1,
                    'amount' => 'BDT 1,290',
                    'payment' => 'Paid',
                    'channel' => 'Website',
                    'status' => 'Ready to Dispatch',
                    'progress' => 64,
                ],
                [
                    'id' => 'ORD-90332',
                    'placed_at' => 'Today, 07:55 AM',
                    'customer' => 'Nusrat Jahan',
                    'location' => 'Chawkbazar, Chattogram',
                    'items' => 2,
                    'amount' => 'BDT 2,980',
                    'payment' => 'Paid',
                    'channel' => 'WhatsApp',
                    'status' => 'In Transit',
                    'progress' => 82,
                ],
                [
                    'id' => 'ORD-90318',
                    'placed_at' => 'Today, 06:28 AM',
                    'customer' => 'Riad Karim',
                    'location' => 'Rajshahi Sadar',
                    'items' => 4,
                    'amount' => 'BDT 6,120',
                    'payment' => 'COD',
                    'channel' => 'Facebook Post',
                    'status' => 'Delayed',
                    'progress' => 51,
                ],
                [
                    'id' => 'ORD-90303',
                    'placed_at' => 'Yesterday, 11:11 PM',
                    'customer' => 'Sumi Akter',
                    'location' => 'Sylhet Sadar',
                    'items' => 1,
                    'amount' => 'BDT 980',
                    'payment' => 'Paid',
                    'channel' => 'Instagram',
                    'status' => 'Delivered',
                    'progress' => 100,
                ],
            ],
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
}
