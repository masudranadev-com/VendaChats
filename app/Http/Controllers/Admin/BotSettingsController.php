<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class BotSettingsController extends Controller
{
    public function index(Request $request): View
    {
        $pages = collect($request->session()->get('facebook.pages', []))
            ->map(static function (array $page): array {
                return [
                    'id' => $page['id'] ?? null,
                    'name' => $page['name'] ?? 'Untitled Page',
                ];
            })
            ->filter(static fn (array $page): bool => filled($page['id']))
            ->values()
            ->all();

        $selectedPageId = $request->session()->get('bot_settings.selected_page_id');
        if (! $selectedPageId && isset($pages[0]['id'])) {
            $selectedPageId = $pages[0]['id'];
        }

        if ($selectedPageId && ! collect($pages)->contains('id', $selectedPageId)) {
            $selectedPageId = $pages[0]['id'] ?? null;
        }

        if ($selectedPageId) {
            $pageServices = $request->session()->get('bot_settings.page_services', []);
            $pageServices[$selectedPageId] = [
                'service_messenger' => true,
                'service_comments' => true,
            ];

            $request->session()->put('bot_settings.selected_page_id', $selectedPageId);
            $request->session()->put('bot_settings.page_services', $pageServices);
        }

        $pageServices = $request->session()->get('bot_settings.page_services', []);
        $selectedPageServices = $pageServices[$selectedPageId] ?? [];
        $selectedPageServices['service_messenger'] = true;
        $selectedPageServices['service_comments'] = true;

        return view('admin.bot-settings', [
            'title' => 'Bot Settings',
            'subtitle' => 'Enable and control each automation capability for Messenger and Comment workflows.',
            'facebookUser' => $request->session()->get('facebook.user'),
            'facebookPages' => $pages,
            'selectedFacebookPageId' => $selectedPageId,
            'selectedPageServices' => $selectedPageServices,
        ]);
    }

    public function connectFacebook(Request $request): RedirectResponse
    {
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

    public function assignPageFacebook(Request $request): RedirectResponse
    {
        $pages = $request->session()->get('facebook.pages', []);
        if ($pages === []) {
            return redirect()
                ->route('admin.bot-settings')
                ->withErrors(['facebook' => 'Connect Facebook first, then select a page.']);
        }

        $availablePageIds = collect($pages)->pluck('id')->filter()->values()->all();
        if ($availablePageIds === []) {
            return redirect()
                ->route('admin.bot-settings')
                ->withErrors(['facebook' => 'No valid Facebook page ids found.']);
        }

        $requestedPageId = $request->query('page_id');
        $pageId = $requestedPageId ?: ($request->session()->get('bot_settings.selected_page_id') ?: $availablePageIds[0]);

        if (! in_array($pageId, $availablePageIds, true)) {
            return redirect()
                ->route('admin.bot-settings')
                ->withErrors(['facebook' => 'Selected page is not available in your connected account.']);
        }

        $pageServices = $request->session()->get('bot_settings.page_services', []);
        $pageServices[$pageId] = [
            'service_messenger' => true,
            'service_comments' => true,
        ];

        $request->session()->put('bot_settings.selected_page_id', $pageId);
        $request->session()->put('bot_settings.page_services', $pageServices);

        $pageName = 'Selected page';
        foreach ($pages as $page) {
            if (($page['id'] ?? null) === $pageId) {
                $pageName = $page['name'] ?? $pageName;
                break;
            }
        }

        return redirect()
            ->route('admin.bot-settings')
            ->with('facebook_status', "Facebook page '{$pageName}' activated with Messenger and auto comment reply.");
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

        // Tokens
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

        // user response
        $userResponse = Http::acceptJson()->get(
            "https://graph.facebook.com/{$graphVersion}/me",
            [
                'fields' => 'id,name,email',
                'access_token' => $userAccessToken,
            ]
        );
        if ($userResponse->failed()) {
            $errorMessage = $userResponse->json('error.message') ?: $userResponse->body();
            return redirect()
                ->route('admin.bot-settings')
                ->withErrors(['facebook' => $errorMessage]);
        }

        // Pages
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
        $defaultPageId = $pages[0]['id'] ?? null;
        if (! $defaultPageId) {
            return redirect()
                ->route('admin.bot-settings')
                ->withErrors(['facebook' => 'Facebook page id missing. Reconnect and try again.']);
        }
        $pageServices = $request->session()->get('bot_settings.page_services', []);
        $pageServices[$defaultPageId] = [
            'service_messenger' => true,
            'service_comments' => true,
        ];

        // Logs
        Log::info('Facebook OAuth successful', [
            'user_access_token' => $userAccessToken,
            'user' => $userResponse->json(),
            'pages' => $pages,
            'page_services' => $pageServices,
            'default_pageId' => $defaultPageId,
            'pages_connected' => count($pages),
        ]);

        // Pages
        $request->session()->put('facebook_user_access_token', $userAccessToken);
        $request->session()->put('facebook_user', $userResponse->json());
        $request->session()->put('facebook_pages', $pages);
        $request->session()->put('bot_settings_selected_page_id', $defaultPageId);
        $request->session()->put('bot_settings_page_services', $pageServices);

        $pageName = 'Selected page';
        foreach ($pages as $page) {
            if (($page['id'] ?? null) === $defaultPageId) {
                $pageName = $page['name'] ?? $pageName;
                break;
            }
        }

        return redirect()
            ->route('admin.bot-settings')
            ->with('facebook_status', "Facebook connected. '{$pageName}' is now active for Messenger and auto comment reply.");
    }

    public function disconnectFacebook(Request $request): RedirectResponse
    {
        $request->session()->forget([
            'bot_settings.facebook.oauth_state',
            'bot_settings.facebook.redirect_uri',
            'facebook.oauth_state',
            'facebook.oauth_success_redirect',
            'facebook.oauth_error_redirect',
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
}
