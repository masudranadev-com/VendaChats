<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
            $request->session()->put('bot_settings.selected_page_id', $selectedPageId);
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

    public function saveFacebookPageServices(Request $request): RedirectResponse
    {
        $availablePageIds = collect($request->session()->get('facebook.pages', []))
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        if ($availablePageIds === []) {
            return redirect()
                ->route('admin.bot-settings')
                ->withErrors(['facebook' => 'Connect your Facebook account first to select a page.']);
        }

        $validated = $request->validate([
            'page_id' => ['required', 'string'],
        ]);

        $pageId = $validated['page_id'];
        if (! in_array($pageId, $availablePageIds, true)) {
            return redirect()
                ->route('admin.bot-settings')
                ->withErrors(['facebook' => 'Selected Facebook page is not available in this session.']);
        }

        $pageServices = $request->session()->get('bot_settings.page_services', []);
        $pageServices[$pageId] = [
            'service_messenger' => true,
            'service_comments' => true,
        ];

        $request->session()->put('bot_settings.selected_page_id', $pageId);
        $request->session()->put('bot_settings.page_services', $pageServices);

        return redirect()
            ->route('admin.bot-settings')
            ->with('facebook_status', 'Facebook page and services updated.');
    }

    /*
    ===========
    // Facebook
    ===========
    */
    public function connectFacebook()
    {

    }

    public function assignFacebookPage()
    {

    }

    public function disconnectFacebook()
    {

    }

    public function handleFacebookCallback()
    {

    }
}
