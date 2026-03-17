<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class ShopSettingsControllerTest extends TestCase
{
    public function test_shop_settings_page_shows_first_time_setup_sections(): void
    {
        $response = $this
            ->withSession([
                'auth.refresh_token' => 'refresh-token',
                'admin.global_config' => [
                    'onboarding' => 'completed',
                    'product_type' => 'subscription',
                    'timezone' => 'America/New_York',
                    'admin_panel_language' => 'english',
                    'website_language' => 'bangla',
                ],
            ])
            ->get(route('admin.shop-settings'));

        $response->assertOk();
        $response->assertSee('First-time setup');
        $response->assertSee('Choose your product type');
        $response->assertSee('Locale');
        $response->assertSee('Shop Settings Sections');
        $response->assertSee('Save product type');
        $response->assertSee('Save locale');
    }

    public function test_theme_page_shows_live_theme_configuration_sections(): void
    {
        $response = $this
            ->withSession([
                'auth.refresh_token' => 'refresh-token',
                'admin.global_config' => [
                    'connected_domain' => 'store.ametafy.shop',
                    'domain_type' => 'sub_domain',
                    'domain_status' => 'Connected',
                    'subdomain_base' => 'ametafy.shop',
                ],
            ])
            ->get(route('admin.shop-settings.theme'));

        $response->assertOk();
        $response->assertSee('Theme Experience Summary');
        $response->assertSee('Auto-Generated Theme Library');
        $response->assertSee('Advanced Visual Settings');
        $response->assertSee('Storefront Feature Switches');
        $response->assertSee('Studio Luxe v2');
    }
}
