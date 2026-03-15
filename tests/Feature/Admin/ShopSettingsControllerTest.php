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
}
