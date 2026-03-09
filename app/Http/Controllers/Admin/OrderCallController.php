<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class OrderCallController extends Controller
{
    public function index(): View
    {
        $pageName = 'A Metafy';
        $productTitle = 'Premium Cotton T-Shirt';

        return view('admin.order-call.index', [
            'title' => 'Order Call Confirmation',
            'subtitle' => 'Premium AI-powered voice calls for order confirm and reject flows. Locked until subscription is activated.',
            'featureLocked' => true,
            'packagePrice' => 500,
            'pageName' => $pageName,
            'productTitle' => $productTitle,
            'stats' => [
                ['label' => 'Package Price', 'value' => 'BDT 500', 'meta' => 'Per month, per store', 'tone' => 'primary'],
                ['label' => 'Languages', 'value' => 'All', 'meta' => 'Auto language adaptation', 'tone' => 'success'],
                ['label' => 'Keypad Actions', 'value' => '1 / 2', 'meta' => 'Confirm or cancel order', 'tone' => 'warning'],
                ['label' => 'Editable Tokens', 'value' => '2', 'meta' => '{PAGE_NAME} and {PRODUCT_TITLE}', 'tone' => 'info'],
            ],
            'benefits' => [
                [
                    'title' => 'Call-based order confirmation',
                    'copy' => 'The AI caller tells the customer which product they ordered and asks for a keypad response.',
                ],
                [
                    'title' => 'Auto voice generation from product title',
                    'copy' => 'Every new product or updated product title produces a fresh call voice script automatically.',
                ],
                [
                    'title' => 'Bangla-first experience',
                    'copy' => 'Merchants can use a natural Bangla confirmation flow while still keeping an English fallback version ready.',
                ],
                [
                    'title' => 'Locked master script',
                    'copy' => 'Your team never edits the script body manually. The engine only swaps in page name and product title tokens.',
                ],
            ],
            'activationSteps' => [
                'Subscribe to the premium package for BDT 500 per month.',
                'Set your {PAGE_NAME} once from this page.',
                'Create or update a product title in the products section.',
                'AI auto-generates the recorded voice preview for that product.',
            ],
            'supportedLanguages' => [
                'Bangla',
                'English',
                'Arabic',
                'Hindi',
                'Urdu',
                'Spanish',
                'More...',
            ],
            'scripts' => [
                'bangla' => "Assalamu alaikum. {$pageName}-e apnake shagotom. Apni amader {$pageName} theke {$productTitle} order korechen. Apnar order ti confirm korte 1 chapun, cancel korte 2 chapun.",
                'english' => "Assalamu alaikum. Welcome to {$pageName}. You placed an order for {$productTitle} from {$pageName}. Press 1 to confirm your order, or press 2 to cancel it.",
            ],
            'recentVoices' => [
                [
                    'title' => 'Premium Cotton T-Shirt',
                    'duration' => '00:18',
                    'language' => 'Bangla + English',
                    'status' => 'Demo voice ready',
                ],
                [
                    'title' => 'Wireless Earbuds Pro',
                    'duration' => '00:17',
                    'language' => 'Bangla + English',
                    'status' => 'Queued after title sync',
                ],
                [
                    'title' => 'Leather Office Backpack',
                    'duration' => '00:19',
                    'language' => 'Bangla + English',
                    'status' => 'Locked until package activation',
                ],
            ],
            'scriptTokens' => [
                [
                    'token' => '{PAGE_NAME}',
                    'label' => 'Editable from this page',
                    'copy' => 'Use your store or Facebook page name. This becomes the greeting source for every voice call.',
                ],
                [
                    'token' => '{PRODUCT_TITLE}',
                    'label' => 'Comes from product create/edit',
                    'copy' => 'Automatically taken from the product title whenever you create or update a product.',
                ],
                [
                    'token' => 'Language',
                    'label' => 'Automatic for all languages',
                    'copy' => 'The script body stays locked. The AI engine handles customer language adaptation automatically.',
                ],
            ],
            'voiceWave' => [28, 42, 58, 34, 66, 48, 71, 36, 63, 44, 55, 30],
        ]);
    }
}
