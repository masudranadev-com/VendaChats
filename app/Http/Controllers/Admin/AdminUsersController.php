<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminUsersController extends Controller
{
    public function users()
    {
        return view('admin.page', [
            'title' => 'Users',
            'heading' => 'Users',
            'subtitle' => 'Manage admin and customer access, status, and account details.',
        ]);
    }
}
