<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function analytics()
    {
        return $this->page('Analytics', 'Track revenue, conversion, and campaign performance trends.');
    }

    public function orders()
    {
        return $this->page('Orders', 'Manage order flow, approvals, and fulfillment status.');
    }

    public function conversations()
    {
        return $this->page('Conversations', 'Monitor AI and human replies across all customer threads.');
    }

    public function customers()
    {
        return $this->page('Customers', 'View customer segments, behavior, and lifecycle insights.');
    }

    public function products()
    {
        return $this->page('Products', 'Control catalog, inventory, and pricing from one place.');
    }

    public function botSettings()
    {
        return $this->page('Bot Settings', 'Configure reply rules, tone, and automation priorities.');
    }

    public function bargaining()
    {
        return $this->page('Bargaining Rules', 'Set floor price, negotiation steps, and approval boundaries.');
    }

    public function whatsappRecovery()
    {
        return $this->page('WhatsApp Recovery', 'Recover dropped buyers with timed follow-up sequences.');
    }

    public function campaigns()
    {
        return $this->page('Campaigns', 'Create launch, retargeting, and upsell campaigns.');
    }

    public function competition()
    {
        return $this->page('Competition Monitor', 'Track market movement and competitor pricing signals.');
    }

    public function coach()
    {
        return $this->page('Performance Coach', 'Review weekly recommendations and growth actions.');
    }

    public function courier()
    {
        return $this->page('Courier Manager', 'Compare shipping options and dispatch performance.');
    }

    public function settings()
    {
        return $this->page('Shop Settings', 'Update brand profile, preferences, and account controls.');
    }

    public function billing()
    {
        return $this->page('Billing', 'Manage subscriptions, invoices, and payment methods.');
    }

    private function page(string $title, string $subtitle)
    {
        return view('admin.page', compact('title', 'subtitle'));
    }
}
