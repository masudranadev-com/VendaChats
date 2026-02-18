<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index()
    {
        return view('user.index');
    }

    // webhook
    public function webhook(Request $request)
    {
        if ($request->isMethod('get')) {
            $mode = $request->input('hub_mode');
            $challenge = $request->input('hub_challenge');
            $verifyToken = $request->input('hub_verify_token');

            if ($mode === 'subscribe' && $verifyToken === config('services.meta.verify_token')) {
                return response($challenge, 200)->header('Content-Type', 'text/plain');
            }

            return response('Forbidden', 403);
        }

        $payload = $request->all();
        Log::info('Webhook event received', $payload);

        if (($payload['object'] ?? null) !== 'page') {
            return response('EVENT_IGNORED', 200);
        }

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['messaging'] ?? [] as $event) {
                if (($event['message']['is_echo'] ?? false) === true) {
                    continue;
                }

                $senderId = $event['sender']['id'] ?? null;
                $incomingText = trim((string) ($event['message']['text'] ?? ''));

                if (!$senderId || $incomingText === '') {
                    continue;
                }

                $replyText = $this->resolveReplyForMessage($incomingText);

                if ($replyText === null) {
                    continue;
                }

                $this->sendSimpleMessengerReply($senderId, $replyText);
            }
        }

        return response('EVENT_RECEIVED', 200);
    }

    protected function resolveReplyForMessage(string $incomingText): ?string
    {
        $message = strtolower(trim($incomingText));

        $exactReplies = [
            'hi' => 'Hello! How can I help you?',
            'hello' => 'Hello! How can I help you?',
            'price' => 'Please share the product name and I will send the price.',
            'help' => 'You can ask about price, delivery, and order process.',
            'delivery' => 'Delivery usually takes 2 to 5 business days.',
        ];

        if (isset($exactReplies[$message])) {
            return $exactReplies[$message];
        }

        if (str_contains($message, 'price')) {
            return 'Please share the product name and I will send the price.';
        }

        if (str_contains($message, 'delivery')) {
            return 'Delivery usually takes 2 to 5 business days.';
        }

        return null;
    }

    protected function sendSimpleMessengerReply(string $recipientId, string $text): void
    {
        $pageAccessToken = config('services.meta.page_access_token');
        $graphApiVersion = config('services.meta.graph_api_version', 'v22.0');

        if (!$pageAccessToken) {
            Log::warning('META_PAGE_ACCESS_TOKEN is missing. Reply not sent.', [
                'recipient_id' => $recipientId,
            ]);
            return;
        }

        $response = Http::asJson()
            ->withQueryParameters([
                'access_token' => $pageAccessToken,
            ])
            ->post("https://graph.facebook.com/{$graphApiVersion}/me/messages", [
                'messaging_type' => 'RESPONSE',
                'recipient' => ['id' => $recipientId],
                'message' => ['text' => $text],
            ]);

        if ($response->failed()) {
            Log::error('Failed to send Messenger reply.', [
                'recipient_id' => $recipientId,
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);
        }
    }
}
