<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminWebsiteContentControllerTest extends TestCase
{
    public function test_slider_workspace_uses_existing_shop_settings_content_url(): void
    {
        $response = $this
            ->withSession($this->adminSession())
            ->get(route('admin.shop-settings.content'));

        $response->assertOk();
        $response->assertSeeText('Homepage sliders');
        $response->assertSeeText('Add Slider');
        $response->assertSeeText('Page Editor');
        $response->assertSeeText('Contact');
        $response->assertSeeText('Footer');
    }

    public function test_content_subpages_render_successfully(): void
    {
        $pages = [
            route('admin.shop-settings.content.page-editor') => 'Policy and static page editor',
            route('admin.shop-settings.content.contact') => 'Customer contact details',
            route('admin.shop-settings.content.footer') => 'Footer and trust content',
        ];

        foreach ($pages as $url => $expectedText) {
            $response = $this
                ->withSession($this->adminSession())
                ->get($url);

            $response->assertOk();
            $response->assertSeeText($expectedText);
        }
    }

    private function adminSession(): array
    {
        return [
            'auth.refresh_token' => 'refresh-token',
            'auth.expires_at' => now()->addHour()->timestamp,
            'admin.global_config' => [
                'onboarding' => 'completed',
            ],
        ];
    }
}
