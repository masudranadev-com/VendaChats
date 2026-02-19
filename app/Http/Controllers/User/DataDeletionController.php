<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DataDeletionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DataDeletionController extends Controller
{
    public function index(): View
    {
        return view('user.data-deletion');
    }

    public function callback(Request $request): JsonResponse
    {
        $signedRequest = (string) $request->input('signed_request', '');
        if ($signedRequest === '') {
            return response()->json([
                'error' => 'signed_request is required.',
            ], 400);
        }

        $parsedPayload = $this->parseSignedRequest($signedRequest);
        if ($parsedPayload === null) {
            return response()->json([
                'error' => 'Invalid signed_request.',
            ], 400);
        }

        $facebookUserId = $parsedPayload['user_id'] ?? null;
        if (! is_string($facebookUserId) || $facebookUserId === '') {
            $facebookUserId = null;
        }

        $confirmationCode = $this->generateConfirmationCode();

        $requestRecord = DataDeletionRequest::create([
            'facebook_user_id' => $facebookUserId,
            'confirmation_code' => $confirmationCode,
            'status' => DataDeletionRequest::STATUS_PENDING,
            'requested_at' => now(),
            'notes' => 'Deletion request received from Meta.',
        ]);

        $this->deleteFacebookData($facebookUserId);

        $requestRecord->status = DataDeletionRequest::STATUS_COMPLETED;
        $requestRecord->completed_at = now();
        if ($facebookUserId === null) {
            $requestRecord->notes = 'Deletion request received, but user_id was not present in signed_request.';
        }
        $requestRecord->save();

        $statusUrl = route('data-deletion.status', ['code' => $confirmationCode]);

        return response()->json([
            'url' => $statusUrl,
            'confirmation_code' => $confirmationCode,
        ]);
    }

    public function status(string $code): View
    {
        $requestRecord = DataDeletionRequest::query()
            ->where('confirmation_code', $code)
            ->firstOrFail();

        return view('user.data-deletion-status', [
            'requestRecord' => $requestRecord,
        ]);
    }

    private function parseSignedRequest(string $signedRequest): ?array
    {
        if (! str_contains($signedRequest, '.')) {
            return null;
        }

        [$encodedSignature, $payload] = explode('.', $signedRequest, 2);
        $signature = $this->base64UrlDecode($encodedSignature);
        $decodedPayload = $this->base64UrlDecode($payload);

        if ($signature === null || $decodedPayload === null) {
            return null;
        }

        $payloadData = json_decode($decodedPayload, true);
        if (! is_array($payloadData)) {
            return null;
        }

        $algorithm = strtoupper((string) ($payloadData['algorithm'] ?? ''));
        if ($algorithm !== 'HMAC-SHA256') {
            return null;
        }

        $appSecret = (string) config('services.facebook.app_secret');
        if ($appSecret === '') {
            Log::warning('Meta data deletion callback failed: FB_APP_SECRET is not configured.');

            return null;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $appSecret, true);
        if (! hash_equals($expectedSignature, $signature)) {
            Log::warning('Meta data deletion callback failed: signature mismatch.');

            return null;
        }

        return $payloadData;
    }

    private function base64UrlDecode(string $value): ?string
    {
        $remainder = strlen($value) % 4;
        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return $decoded === false ? null : $decoded;
    }

    private function generateConfirmationCode(): string
    {
        do {
            $code = Str::upper(Str::random(12));
        } while (
            DataDeletionRequest::query()->where('confirmation_code', $code)->exists()
        );

        return $code;
    }

    private function deleteFacebookData(?string $facebookUserId): void
    {
        // Placeholder for deletion logic once Facebook data is persisted in DB tables.
        Log::info('Processing Meta data deletion request.', [
            'facebook_user_id' => $facebookUserId,
        ]);
    }
}
