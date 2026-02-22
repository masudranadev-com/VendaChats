<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class CourierController extends Controller
{
    public function index(Request $request): View
    {
        $allLogs = collect([
            ['time' => 'Today 10:42 AM', 'provider' => 'SteadFast', 'event' => 'Consignment Create', 'status' => 'Success', 'request_id' => 'SF-REQ-88012'],
            ['time' => 'Today 09:16 AM', 'provider' => 'RedX', 'event' => 'Pickup Request', 'status' => 'Failed', 'request_id' => 'RDX-REQ-90144'],
            ['time' => 'Today 08:55 AM', 'provider' => 'SteadFast', 'event' => 'Rate Fetch', 'status' => 'Success', 'request_id' => 'SF-REQ-88003'],
            ['time' => 'Today 08:24 AM', 'provider' => 'RedX', 'event' => 'Consignment Create', 'status' => 'Success', 'request_id' => 'RDX-REQ-90131'],
            ['time' => 'Today 07:38 AM', 'provider' => 'SteadFast', 'event' => 'Status Sync', 'status' => 'Success', 'request_id' => 'SF-REQ-87998'],
            ['time' => 'Yesterday 11:44 PM', 'provider' => 'RedX', 'event' => 'Rate Fetch', 'status' => 'Success', 'request_id' => 'RDX-REQ-90102'],
            ['time' => 'Yesterday 10:07 PM', 'provider' => 'SteadFast', 'event' => 'Pickup Request', 'status' => 'Failed', 'request_id' => 'SF-REQ-87990'],
            ['time' => 'Yesterday 08:31 PM', 'provider' => 'SteadFast', 'event' => 'Status Sync', 'status' => 'Success', 'request_id' => 'SF-REQ-87961'],
            ['time' => 'Yesterday 07:16 PM', 'provider' => 'RedX', 'event' => 'Webhook Verify', 'status' => 'Success', 'request_id' => 'RDX-REQ-90074'],
            ['time' => 'Yesterday 04:18 PM', 'provider' => 'RedX', 'event' => 'Rate Fetch', 'status' => 'Success', 'request_id' => 'RDX-REQ-90027'],
            ['time' => 'Yesterday 02:52 PM', 'provider' => 'SteadFast', 'event' => 'Webhook Verify', 'status' => 'Success', 'request_id' => 'SF-REQ-87910'],
            ['time' => 'Yesterday 12:06 PM', 'provider' => 'RedX', 'event' => 'Status Sync', 'status' => 'Failed', 'request_id' => 'RDX-REQ-89995'],
            ['time' => '2 days ago 09:22 PM', 'provider' => 'SteadFast', 'event' => 'Consignment Create', 'status' => 'Success', 'request_id' => 'SF-REQ-87871'],
            ['time' => '2 days ago 05:47 PM', 'provider' => 'RedX', 'event' => 'Pickup Request', 'status' => 'Success', 'request_id' => 'RDX-REQ-89920'],
        ]);

        $perPage = 5;
        $currentPage = max(1, (int) $request->query('logs_page', 1));
        $lastPage = max(1, (int) ceil($allLogs->count() / $perPage));
        $currentPage = min($currentPage, $lastPage);

        $logs = new LengthAwarePaginator(
            $allLogs->forPage($currentPage, $perPage)->values()->all(),
            $allLogs->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'logs_page',
            ]
        );

        return view('admin.courier.index', [
            'title' => 'Courier Manager',
            'subtitle' => 'Configure SteadFast and RedX API connections and optimize dispatch routing.',
            'providers' => [
                [
                    'key' => 'steadfast',
                    'name' => 'SteadFast Courier',
                    'status' => 'Connected',
                    'status_class' => 'badge-success',
                    'base_url' => 'https://api.steadfast.com.bd/v1',
                    'merchant_field' => 'Merchant ID',
                    'merchant_value' => 'SF-MERCHANT-101',
                    'mode' => 'Live',
                ],
                [
                    'key' => 'redx',
                    'name' => 'RedX Courier',
                    'status' => 'Needs Update',
                    'status_class' => 'badge-warning',
                    'base_url' => 'https://openapi.redx.com.bd/v1',
                    'merchant_field' => 'Store ID',
                    'merchant_value' => 'RDX-STORE-982',
                    'mode' => 'Sandbox',
                ],
            ],
            'zones' => [
                ['zone' => 'Dhaka Metro', 'steadfast' => '৳70', 'redx' => '৳75', 'preferred' => 'SteadFast'],
                ['zone' => 'Sub Urban', 'steadfast' => '৳90', 'redx' => '৳88', 'preferred' => 'RedX'],
                ['zone' => 'Outside Dhaka', 'steadfast' => '৳130', 'redx' => '৳120', 'preferred' => 'RedX'],
                ['zone' => 'Remote Area', 'steadfast' => '৳165', 'redx' => '৳175', 'preferred' => 'SteadFast'],
            ],
            'logs' => $logs,
        ]);
    }
}
