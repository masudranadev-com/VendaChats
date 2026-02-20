<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class CourierController extends Controller
{
    public function index(): View
    {
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
            'logs' => [
                ['time' => 'Today 10:42 AM', 'provider' => 'SteadFast', 'event' => 'Consignment Create', 'status' => 'Success', 'request_id' => 'SF-REQ-88012'],
                ['time' => 'Today 09:16 AM', 'provider' => 'RedX', 'event' => 'Pickup Request', 'status' => 'Failed', 'request_id' => 'RDX-REQ-90144'],
                ['time' => 'Yesterday 08:31 PM', 'provider' => 'SteadFast', 'event' => 'Status Sync', 'status' => 'Success', 'request_id' => 'SF-REQ-87961'],
                ['time' => 'Yesterday 04:18 PM', 'provider' => 'RedX', 'event' => 'Rate Fetch', 'status' => 'Success', 'request_id' => 'RDX-REQ-90027'],
            ],
        ]);
    }
}
