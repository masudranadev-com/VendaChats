<?php

namespace Tests\Feature\Admin;

use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminOnboardingControllerTest extends TestCase
{
    public function test_product_step_normalizes_digital_to_subscription_before_forwarding(): void
    {
        Http::fake([
            'http://localhost:8082/api/admin/user/global/onboarding' => Http::response([
                'data' => [
                    'product_type' => 'subscription',
                ],
                'onboarding' => 'product',
                'next_step' => 'sub_domain',
            ], 200),
        ]);

        $response = $this
            ->withSession([
                'auth.refresh_token' => 'refresh-token',
            ])
            ->postJson(route('admin.onboardingContinue'), [
                'type' => 'product',
                'data' => [
                    'product_type' => 'digital',
                ],
            ]);

        $response->assertOk();

        Http::assertSent(function (HttpRequest $request): bool {
            $data = $request->data();

            return $request->url() === 'http://localhost:8082/api/admin/user/global/onboarding'
                && $request->hasHeader('user-refres-token', 'refresh-token')
                && ($data['type'] ?? null) === 'product'
                && ($data['data']['product_type'] ?? null) === 'subscription';
        });

        $response->assertJsonPath('data.product_type', 'subscription');
        $this->assertSame('subscription', session('admin.global_config.product_type'));
    }

    public function test_product_step_keeps_subscription_payload_unchanged(): void
    {
        Http::fake([
            'http://localhost:8082/api/admin/user/global/onboarding' => Http::response([
                'data' => [
                    'product_type' => 'subscription',
                ],
            ], 200),
        ]);

        $response = $this
            ->withSession([
                'auth.refresh_token' => 'refresh-token',
            ])
            ->postJson(route('admin.onboardingContinue'), [
                'type' => 'product',
                'data' => [
                    'product_type' => 'subscription',
                ],
            ]);

        $response->assertOk();

        Http::assertSent(function (HttpRequest $request): bool {
            $data = $request->data();

            return ($data['data']['product_type'] ?? null) === 'subscription';
        });
    }

    public function test_call_order_step_forwards_is_calling_and_preserves_false_values(): void
    {
        Http::fake([
            'http://localhost:8082/api/admin/user/global/onboarding' => Http::response([
                'data' => [
                    'is_calling' => false,
                    'recording_page_name' => 'A Metafy',
                    'recording_language' => 'english',
                    'calling_scope' => 'cod',
                ],
            ], 200),
        ]);

        $response = $this
            ->withSession([
                'auth.refresh_token' => 'refresh-token',
            ])
            ->postJson(route('admin.onboardingContinue'), [
                'type' => 'call_order',
                'data' => [
                    'is_calling' => false,
                    'recording_page_name' => 'A Metafy',
                    'recording_language' => 'english',
                    'calling_scope' => 'cod',
                ],
            ]);

        $response->assertOk();

        Http::assertSent(function (HttpRequest $request): bool {
            $data = $request->data();

            return ($data['type'] ?? null) === 'call_order'
                && array_key_exists('is_calling', $data['data'] ?? [])
                && ($data['data']['is_calling'] ?? true) === false
                && ($data['data']['recording_page_name'] ?? null) === 'A Metafy'
                && ($data['data']['recording_language'] ?? null) === 'english'
                && ($data['data']['calling_scope'] ?? null) === 'cod';
        });

        $response->assertJsonPath('data.is_calling', false);
        $this->assertFalse((bool) session('admin.global_config.is_calling'));
    }
}
