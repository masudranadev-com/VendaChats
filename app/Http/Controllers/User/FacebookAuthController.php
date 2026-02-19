<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class FacebookAuthController extends Controller
{
    public function oauthPage(Request $request): View
    {
        return view('user.facebook.oauth', [
            'missingConfig' => $this->missingConfig(),
            'redirectUri' => $this->facebookConfig('redirect_uri'),
            'graphVersion' => $this->facebookConfig('graph_version', 'v22.0'),
            'connectedUser' => $request->session()->get('facebook.user'),
            'connectedPages' => $request->session()->get('facebook.pages', []),
        ]);
    }

    public function redirectToFacebook(Request $request): RedirectResponse
    {
        $origin = $request->query('origin');
        $fromBotSettings = $origin === 'admin-bot-settings';
        $errorRoute = $fromBotSettings ? 'admin.bot-settings' : 'facebook.oauth';

        $missingConfig = $this->missingConfig();

        if ($missingConfig !== []) {
            return redirect()
                ->route($errorRoute)
                ->withErrors(['facebook' => 'Missing configuration: '.implode(', ', $missingConfig)]);
        }

        $request->session()->put(
            'facebook.oauth_success_redirect',
            $fromBotSettings ? route('admin.bot-settings') : route('facebook.dashboard')
        );
        $request->session()->put(
            'facebook.oauth_error_redirect',
            $fromBotSettings ? route('admin.bot-settings') : route('facebook.oauth')
        );

        $state = Str::random(40);
        $request->session()->put('facebook.oauth_state', $state);

        $query = http_build_query([
            'client_id' => $this->facebookConfig('app_id'),
            'redirect_uri' => $this->facebookConfig('redirect_uri'),
            'scope' => implode(',', $this->facebookConfig('scopes', [])),
            'response_type' => 'code',
            'state' => $state,
            'auth_type' => 'rerequest',
        ]);

        $oauthUrl = "https://www.facebook.com/{$this->facebookConfig('graph_version', 'v22.0')}/dialog/oauth?{$query}";

        return redirect()->away($oauthUrl);
    }

    public function handleFacebookCallback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            $message = $request->query('error_message', 'Facebook authorization failed.');

            return redirect()
                ->to($this->oauthErrorRedirect($request))
                ->withErrors(['facebook' => $message]);
        }

        $expectedState = $request->session()->pull('facebook.oauth_state');
        $incomingState = $request->query('state');
        if (! $expectedState || ! $incomingState || ! hash_equals($expectedState, $incomingState)) {
            return redirect()
                ->to($this->oauthErrorRedirect($request))
                ->withErrors(['facebook' => 'Invalid OAuth state. Please try again.']);
        }

        $code = $request->query('code');
        if (! $code) {
            return redirect()
                ->to($this->oauthErrorRedirect($request))
                ->withErrors(['facebook' => 'OAuth code missing from callback.']);
        }

        $tokenResponse = Http::acceptJson()->get(
            $this->graphUrl('oauth/access_token'),
            [
                'client_id' => $this->facebookConfig('app_id'),
                'client_secret' => $this->facebookConfig('app_secret'),
                'redirect_uri' => $this->facebookConfig('redirect_uri'),
                'code' => $code,
            ]
        );

        if ($tokenResponse->failed()) {
            return redirect()
                ->to($this->oauthErrorRedirect($request))
                ->withErrors(['facebook' => $this->graphErrorMessage($tokenResponse)]);
        }

        $userAccessToken = $tokenResponse->json('access_token');
        if (! $userAccessToken) {
            return redirect()
                ->to($this->oauthErrorRedirect($request))
                ->withErrors(['facebook' => 'Could not read access token from Facebook response.']);
        }

        $userResponse = Http::acceptJson()->get(
            $this->graphUrl('me'),
            [
                'fields' => 'id,name,email',
                'access_token' => $userAccessToken,
            ]
        );

        if ($userResponse->failed()) {
            return redirect()
                ->to($this->oauthErrorRedirect($request))
                ->withErrors(['facebook' => $this->graphErrorMessage($userResponse)]);
        }

        $pagesResponse = Http::acceptJson()->get(
            $this->graphUrl('me/accounts'),
            ['access_token' => $userAccessToken]
        );

        if ($pagesResponse->failed()) {
            return redirect()
                ->to($this->oauthErrorRedirect($request))
                ->withErrors(['facebook' => $this->graphErrorMessage($pagesResponse)]);
        }

        $request->session()->put('facebook.user_access_token', $userAccessToken);
        $request->session()->put('facebook.user', $userResponse->json());
        $request->session()->put('facebook.pages', $pagesResponse->json('data', []));

        return redirect()
            ->to($this->oauthSuccessRedirect($request))
            ->with('status', 'Facebook account connected successfully.');
    }

    public function dashboard(Request $request): View
    {
        return view('user.facebook.dashboard', [
            'user' => $request->session()->get('facebook.user'),
            'pages' => $request->session()->get('facebook.pages', []),
            'graphVersion' => $this->facebookConfig('graph_version', 'v22.0'),
            'verifyToken' => "AATTTRR7788GGHHY00",
            'webhookVerifyUrl' => route('facebook.webhook.verify'),
            'appId' => $this->facebookConfig('app_id'),
        ]);
    }

    public function sendMessage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'page_id' => ['required', 'string'],
            'recipient_psid' => ['required', 'string'],
            'message_text' => ['required', 'string', 'max:1000'],
        ]);

        $pageToken = $this->pageToken($request, $validated['page_id']);
        if (! $pageToken) {
            return redirect()
                ->route('facebook.dashboard')
                ->withErrors(['facebook' => 'Page token not found in session. Connect Facebook again.']);
        }

        $response = Http::acceptJson()->post(
            $this->graphUrl('me/messages', ['access_token' => $pageToken]),
            [
                'recipient' => ['id' => $validated['recipient_psid']],
                'message' => ['text' => $validated['message_text']],
            ]
        );

        if ($response->failed()) {
            return redirect()
                ->route('facebook.dashboard')
                ->withErrors(['facebook' => $this->graphErrorMessage($response)]);
        }

        return redirect()
            ->route('facebook.dashboard')
            ->with('status', 'Message request sent to Messenger API.')
            ->with('facebook_api_response', $response->json());
    }

    public function getPagePosts(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'page_id' => ['required', 'string'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $pageToken = $this->pageToken($request, $validated['page_id']);
        if (! $pageToken) {
            return response()->json([
                'message' => 'Page token not found in session. Connect Facebook again.',
            ], 422);
        }

        $response = Http::acceptJson()->get(
            $this->graphUrl($validated['page_id'].'/posts'),
            [
                'fields' => 'id,message,created_time,permalink_url',
                'limit' => $validated['limit'] ?? 25,
                'access_token' => $pageToken,
            ]
        );

        if ($response->failed()) {
            return response()->json([
                'message' => $this->graphErrorMessage($response),
                'error' => $response->json('error'),
            ], $response->status() ?: 500);
        }

        $posts = collect($response->json('data', []))
            ->map(static function (array $post): array {
                return [
                    'id' => $post['id'] ?? null,
                    'message' => $post['message'] ?? '',
                    'created_time' => $post['created_time'] ?? null,
                    'permalink_url' => $post['permalink_url'] ?? null,
                ];
            })
            ->values()
            ->all();

        return response()->json(['data' => $posts]);
    }

    public function getPostComments(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'page_id' => ['required', 'string'],
            'post_id' => ['required', 'string'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $pageToken = $this->pageToken($request, $validated['page_id']);
        if (! $pageToken) {
            return response()->json([
                'message' => 'Page token not found in session. Connect Facebook again.',
            ], 422);
        }

        $response = Http::acceptJson()->get(
            $this->graphUrl($validated['post_id'].'/comments'),
            [
                'fields' => 'id,message,created_time,from{id,name},comment_count,like_count',
                'filter' => 'stream',
                'limit' => $validated['limit'] ?? 50,
                'access_token' => $pageToken,
            ]
        );

        if ($response->failed()) {
            return response()->json([
                'message' => $this->graphErrorMessage($response),
                'error' => $response->json('error'),
            ], $response->status() ?: 500);
        }

        $comments = collect($response->json('data', []))
            ->map(static function (array $comment): array {
                return [
                    'id' => $comment['id'] ?? null,
                    'message' => $comment['message'] ?? '',
                    'created_time' => $comment['created_time'] ?? null,
                    'from' => [
                        'id' => $comment['from']['id'] ?? null,
                        'name' => $comment['from']['name'] ?? 'Unknown',
                    ],
                    'comment_count' => $comment['comment_count'] ?? 0,
                    'like_count' => $comment['like_count'] ?? 0,
                ];
            })
            ->values()
            ->all();

        return response()->json(['data' => $comments]);
    }

    public function replyToComment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'page_id' => ['required', 'string'],
            'comment_id' => ['required', 'string'],
            'comment_reply' => ['required', 'string', 'max:1000'],
        ]);

        $pageToken = $this->pageToken($request, $validated['page_id']);
        if (! $pageToken) {
            return redirect()
                ->route('facebook.dashboard')
                ->withErrors(['facebook' => 'Page token not found in session. Connect Facebook again.']);
        }

        $response = Http::acceptJson()->post(
            $this->graphUrl($validated['comment_id'].'/comments', ['access_token' => $pageToken]),
            ['message' => $validated['comment_reply']]
        );

        if ($response->failed()) {
            return redirect()
                ->route('facebook.dashboard')
                ->withErrors(['facebook' => $this->graphErrorMessage($response)]);
        }

        return redirect()
            ->route('facebook.dashboard')
            ->with('status', 'Comment reply request sent.')
            ->with('facebook_api_response', $response->json());
    }

    public function subscribeWebhook(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'page_id' => ['required', 'string'],
            'subscribed_fields' => ['required', 'string'],
        ]);

        $pageToken = $this->pageToken($request, $validated['page_id']);
        if (! $pageToken) {
            return redirect()
                ->route('facebook.dashboard')
                ->withErrors(['facebook' => 'Page token not found in session. Connect Facebook again.']);
        }

        $response = Http::acceptJson()->post(
            $this->graphUrl($validated['page_id'].'/subscribed_apps', ['access_token' => $pageToken]),
            ['subscribed_fields' => $validated['subscribed_fields']]
        );

        if ($response->failed()) {
            return redirect()
                ->route('facebook.dashboard')
                ->withErrors(['facebook' => $this->graphErrorMessage($response)]);
        }

        return redirect()
            ->route('facebook.dashboard')
            ->with('status', 'Webhook subscribed for the selected page.')
            ->with('facebook_api_response', $response->json());
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $origin = $request->input('origin');

        $request->session()->forget([
            'facebook.oauth_state',
            'facebook.oauth_success_redirect',
            'facebook.oauth_error_redirect',
            'facebook.user_access_token',
            'facebook.user',
            'facebook.pages',
        ]);

        $redirectRoute = $origin === 'admin-bot-settings' ? 'admin.bot-settings' : 'facebook.oauth';

        return redirect()
            ->route($redirectRoute)
            ->with('status', 'Facebook session data cleared.');
    }

    public function verifyWebhook(Request $request)
    {
        $mode = $request->query('hub.mode', $request->query('hub_mode'));
        $verifyToken = $request->query('hub.verify_token', $request->query('hub_verify_token'));
        $challenge = $request->query('hub.challenge', $request->query('hub_challenge'));

        if ($mode === 'subscribe' && $verifyToken === $this->facebookConfig('verify_token')) {
            return response($challenge, 200);
        }

        return response('Invalid verify token.', 403);
    }

    public function receiveWebhook(Request $request): JsonResponse
    {
        Log::info('Facebook webhook payload', $request->all());

        return response()->json(['status' => 'ok']);
    }

    private function pageToken(Request $request, string $pageId): ?string
    {
        $pages = $request->session()->get('facebook.pages', []);
        foreach ($pages as $page) {
            if (($page['id'] ?? null) === $pageId) {
                return $page['access_token'] ?? null;
            }
        }

        return null;
    }

    private function graphUrl(string $path, array $query = []): string
    {
        $base = "https://graph.facebook.com/{$this->facebookConfig('graph_version', 'v22.0')}/".ltrim($path, '/');

        if ($query === []) {
            return $base;
        }

        return $base.'?'.http_build_query($query);
    }

    private function graphErrorMessage($response): string
    {
        $errorMessage = $response->json('error.message');
        if ($errorMessage) {
            return $errorMessage;
        }

        return $response->body();
    }

    private function facebookConfig(string $key, $default = null)
    {
        return config("services.facebook.{$key}", $default);
    }

    private function missingConfig(): array
    {
        $required = [
            'FB_APP_ID' => $this->facebookConfig('app_id'),
            'FB_APP_SECRET' => $this->facebookConfig('app_secret'),
            'FB_REDIRECT_URI' => $this->facebookConfig('redirect_uri'),
            'FB_VERIFY_TOKEN' => "AATTTRR7788GGHHY00",
        ];

        return array_keys(array_filter($required, static fn ($value) => blank($value)));
    }

    private function oauthErrorRedirect(Request $request): string
    {
        return $request->session()->get('facebook.oauth_error_redirect', route('facebook.oauth'));
    }

    private function oauthSuccessRedirect(Request $request): string
    {
        $successRedirect = $request->session()->pull('facebook.oauth_success_redirect', route('facebook.dashboard'));
        $request->session()->forget('facebook.oauth_error_redirect');

        return $successRedirect;
    }
}
