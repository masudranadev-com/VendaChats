<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class BotSettingsController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.bot-settings', [
            'title' => 'Bot Settings',
            'subtitle' => 'Enable and control each automation capability for Messenger and Comment workflows.',
            'pageId' => '1',
            'pageName' => '1',
        ]);
    }

    public function connectFacebook(Request $request): RedirectResponse
    {
        if (! empty($request->session()->get('facebook.user')) && ! empty($request->session()->get('facebook.pages', []))) {
            return redirect()
                ->route('admin.bot-settings')
                ->with('facebook_status', 'Facebook already connected. System is active.');
        }

        $appId = config('services.facebook.app_id');
        $appSecret = config('services.facebook.app_secret');
        $verifyToken = config('services.facebook.verify_token');

        $missingConfig = array_keys(array_filter([
            'FB_APP_ID' => $appId,
            'FB_APP_SECRET' => $appSecret,
            'FB_VERIFY_TOKEN' => $verifyToken,
        ], static fn ($value) => blank($value)));

        if ($missingConfig !== []) {
            return redirect()
                ->route('admin.bot-settings')
                ->withErrors(['facebook' => 'Missing Facebook configuration: '.implode(', ', $missingConfig)]);
        }

        $state = Str::random(40);
        $redirectUri = route('admin.bot-settings.facebook.callback');

        $request->session()->put('bot_settings.facebook.oauth_state', $state);
        $request->session()->put('bot_settings.facebook.redirect_uri', $redirectUri);

        $query = http_build_query([
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'scope' => implode(',', config('services.facebook.scopes', [])),
            'response_type' => 'code',
            'state' => $state,
            'auth_type' => 'rerequest',
        ]);

        $graphVersion = config('services.facebook.graph_version', 'v22.0');
        $oauthUrl = "https://www.facebook.com/{$graphVersion}/dialog/oauth?{$query}";

        return redirect()->away($oauthUrl);
    }

    public function callbackFacebook(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            $message = $request->query('error_message', 'Facebook authorization was canceled or failed.');

            return redirect()
                ->route('admin.bot-settings')
                ->withErrors(['facebook' => $message]);
        }

        $expectedState = $request->session()->pull('bot_settings.facebook.oauth_state');
        $incomingState = $request->query('state');
        if (! $expectedState || ! $incomingState || ! hash_equals($expectedState, $incomingState)) {
            return redirect()
                ->route('admin.bot-settings')
                ->withErrors(['facebook' => 'Invalid OAuth state. Please connect again.']);
        }

        $code = $request->query('code');
        if (! $code) {
            return redirect()
                ->route('admin.bot-settings')
                ->withErrors(['facebook' => 'OAuth code missing in callback response.']);
        }

        $appId = config('services.facebook.app_id');
        $appSecret = config('services.facebook.app_secret');
        $graphVersion = config('services.facebook.graph_version', 'v22.0');
        $redirectUri = $request->session()->pull('bot_settings.facebook.redirect_uri', route('admin.bot-settings.facebook.callback'));

        $tokenResponse = Http::acceptJson()->get(
            "https://graph.facebook.com/{$graphVersion}/oauth/access_token",
            [
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]
        );

        if ($tokenResponse->failed()) {
            $errorMessage = $tokenResponse->json('error.message') ?: $tokenResponse->body();

            return redirect()
                ->route('admin.bot-settings')
                ->withErrors(['facebook' => $errorMessage]);
        }

        $userAccessToken = $tokenResponse->json('access_token');
        if (! $userAccessToken) {
            return redirect()
                ->route('admin.bot-settings')
                ->withErrors(['facebook' => 'Could not read access token from Facebook response.']);
        }

        $pagesResponse = Http::acceptJson()->get(
            "https://graph.facebook.com/{$graphVersion}/me/accounts",
            ['access_token' => $userAccessToken]
        );

        if ($pagesResponse->failed()) {
            $errorMessage = $pagesResponse->json('error.message') ?: $pagesResponse->body();

            return redirect()
                ->route('admin.bot-settings')
                ->withErrors(['facebook' => $errorMessage]);
        }

        $pages = $pagesResponse->json('data', []);
        if ($pages === []) {
            $request->session()->forget(['bot_settings.selected_page_id', 'bot_settings.page_services']);

            return redirect()
                ->route('admin.bot-settings')
                ->withErrors(['facebook' => 'Facebook connected, but no pages were found.']);
        }

         if (!isset($pages[0])) {
            return redirect()
                ->route('admin.bot-settings')
                ->withErrors(['facebook' => 'Facebook page id missing. Reconnect and try again.']);
        }

        $impPageAccessToken = $pages[0]['access_token'] ?? "";
        $impPageId = $pages[0]['id'] ?? "";
        $impPageName = $pages[0]['name'] ?? "";
       
        // Here connect with  
        $token = $request->session()->get('auth.refresh_token');

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'x-refresh-token' => $token,
        ])->post('http://localhost:8082/api/admin/facebook-auth', [
            'page_name' => $impPageName,
            'page_id' => $impPageId,
            'page_access_token' => $impPageAccessToken,
        ]);

        // Check if successful
        if (!$response->successful()) {
            return redirect()
                ->route('admin.bot-settings')
                ->withErrors(['facebook' => $response->json()['error']]);
        }
        

        return redirect()
            ->route('admin.bot-settings')
            ->with('facebook_status', "Facebook connected. '{$impPageName}' is active. No extra page setup needed.");
    }

    public function disconnectFacebook(Request $request): RedirectResponse
    {
        $request->session()->forget([
            'bot_settings.facebook.oauth_state',
            'bot_settings.facebook.redirect_uri',
            'facebook.user_access_token',
            'facebook.user',
            'facebook.pages',
            'bot_settings.selected_page_id',
            'bot_settings.page_services',
        ]);

        return redirect()
            ->route('admin.bot-settings')
            ->with('facebook_status', 'Facebook disconnected successfully.');
    }

    public function infoFacebook(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'sender_id' => ['required', 'string'],
            'page_id' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $senderId = (string) $validated['sender_id'];
        $pageId = $validated['page_id'] ?? $request->session()->get('bot_settings.selected_page_id');

        if (! $pageId) {
            return response()->json([
                'message' => 'Missing page_id. Provide page_id or reconnect Facebook in bot settings.',
            ], 422);
        }

        $pages = $request->session()->get('facebook.pages', []);
        $pageToken = null;

        foreach ($pages as $page) {
            if (($page['id'] ?? null) === $pageId) {
                $pageToken = $page['access_token'] ?? null;
                break;
            }
        }

        if (! $pageToken) {
            return response()->json([
                'message' => 'Page access token not found in session. Reconnect Facebook and try again.',
            ], 422);
        }

        $graphVersion = config('services.facebook.graph_version', 'v22.0');
        $response = Http::acceptJson()->get(
            "https://graph.facebook.com/{$graphVersion}/{$senderId}",
            [
                'fields' => 'id,first_name,last_name,name,profile_pic',
                'access_token' => $pageToken,
            ]
        );

        if ($response->failed()) {
            return response()->json([
                'message' => $response->json('error.message') ?: $response->body(),
                'error' => $response->json('error'),
            ], $response->status() ?: 500);
        }

        $profile = $response->json();

        return response()->json([
            'data' => [
                'sender_id' => $profile['id'] ?? $senderId,
                'first_name' => $profile['first_name'] ?? null,
                'last_name' => $profile['last_name'] ?? null,
                'name' => $profile['name'] ?? trim(($profile['first_name'] ?? '').' '.($profile['last_name'] ?? '')),
                'profile_pic' => $profile['profile_pic'] ?? null,
                'page_id' => $pageId,
            ],
        ]);
    }
}
