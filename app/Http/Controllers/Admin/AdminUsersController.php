<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUsersController extends Controller
{
    public function users(Request $request): View
    {
        $refreshToken = (string) (
            $request->session()->get('auth.refresh_token')
            ?? $request->session()->get('refresh_token')
            ?? ''
        );

        return view('admin.users.index', [
            'title' => 'Users',
            'subtitle' => 'Track buyer emotion, WhatsApp status, and user type in one place.',
            'usersApiBaseUrl' => rtrim((string) config('services.backend.url', 'http://localhost:8082'), '/'),
            'usersRefreshToken' => $refreshToken,
        ]);
    }

    public function usersViews(Request $request): View
    {
        $users = collect($this->usersDataset());
        $requestedUserId = (string) $request->query('user_id', '');

        $user = $users->firstWhere('user_id', $requestedUserId) ?? $users->first();

        abort_unless($user !== null, 404);

        return view('admin.users.view', [
            'title' => 'User Profile',
            'subtitle' => 'Live buyer profile, emotion context, and active product discussion.',
            'user' => $user,
        ]);
    }

    private function usersDataset(): array
    {
        return [
            [
                'user_id' => 'USR-1001',
                'sender_id' => '26808322615423668',
                'page_id' => '988224154376053',
                'name' => 'Unknown Messenger User',
                'profile_pic' => 'https://ui-avatars.com/api/?background=1352DC&color=fff&name=Messenger+User',
                'channels' => ['Messenger', 'Facebook', 'Website'],
                'whatsapp' => true,
                'whatsapp_number' => '8801711001001',
                'emotions' => ['Curious', 'Sad'],
                'user_type' => 'Price-sensitive',
                'orders' => [
                    'total' => 7,
                    'successful' => 4,
                    'cancelled' => 3,
                ],
                'product' => [
                    'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=600&q=80',
                    'title' => 'Premium Cotton T-Shirt',
                    'description' => 'Soft breathable cotton tee with regular fit and durable print quality.',
                    'url' => 'https://example.com/products/premium-cotton-tshirt',
                ],
                'commented_products' => [
                    [
                        'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=200&q=80',
                        'title' => 'Premium Cotton T-Shirt',
                        'short_description' => 'Soft breathable cotton tee with regular fit and durable print quality.',
                        'url' => 'https://example.com/products/premium-cotton-tshirt',
                        'comment_count' => 12,
                        'first_comment_at' => date('Y-m-d H:i:s', strtotime('-152 days')),
                        'last_comment_at' => date('Y-m-d H:i:s', strtotime('-5 hours')),
                    ],
                    [
                        'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=200&q=80',
                        'title' => 'AirFlex Running Shoes',
                        'short_description' => 'Lightweight cushioned running shoe designed for daily comfort and long walks.',
                        'url' => 'https://example.com/products/airflex-running-shoes',
                        'comment_count' => 3,
                        'first_comment_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
                        'last_comment_at' => date('Y-m-d H:i:s', strtotime('-45 minutes')),
                    ],
                ],
            ],
            [
                'user_id' => 'USR-1002',
                'sender_id' => '24800000011122233',
                'page_id' => '988224154376053',
                'name' => 'Ayesha Rahman',
                'profile_pic' => 'https://i.pravatar.cc/120?img=15',
                'channels' => ['Website', 'Instagram', 'WhatsApp'],
                'whatsapp' => true,
                'whatsapp_number' => '8801811001002',
                'emotions' => ['Happy', 'Excited'],
                'user_type' => 'Quality-focused',
                'orders' => [
                    'total' => 14,
                    'successful' => 13,
                    'cancelled' => 1,
                ],
                'product' => [
                    'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80',
                    'title' => 'AirFlex Running Shoes',
                    'description' => 'Lightweight cushioned running shoe designed for daily comfort and long walks.',
                    'url' => 'https://example.com/products/airflex-running-shoes',
                ],
                'commented_products' => [
                    [
                        'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=200&q=80',
                        'title' => 'AirFlex Running Shoes',
                        'short_description' => 'Lightweight cushioned running shoe designed for daily comfort and long walks.',
                        'url' => 'https://example.com/products/airflex-running-shoes',
                        'comment_count' => 27,
                        'first_comment_at' => date('Y-m-d H:i:s', strtotime('-200 days')),
                        'last_comment_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                    ],
                ],
            ],
            [
                'user_id' => 'USR-1003',
                'sender_id' => '24800000033344455',
                'page_id' => '988224154376053',
                'name' => 'Kabir Hasan',
                'profile_pic' => 'https://i.pravatar.cc/120?img=12',
                'channels' => ['Facebook', 'Messenger'],
                'whatsapp' => false,
                'whatsapp_number' => null,
                'emotions' => ['Angry', 'Impatient'],
                'user_type' => 'Price-sensitive',
                'orders' => [
                    'total' => 3,
                    'successful' => 1,
                    'cancelled' => 2,
                ],
                'product' => [
                    'image' => 'https://images.unsplash.com/photo-1581235720704-06d3acfcb36f?auto=format&fit=crop&w=600&q=80',
                    'title' => 'Wireless Earbuds Pro',
                    'description' => 'Noise-reduced earbuds with long battery backup and compact charging case.',
                    'url' => 'https://example.com/products/wireless-earbuds-pro',
                ],
                'commented_products' => [],
            ],
            [
                'user_id' => 'USR-1004',
                'sender_id' => '24800000055566677',
                'page_id' => '988224154376053',
                'name' => 'Nabila Islam',
                'profile_pic' => 'https://i.pravatar.cc/120?img=44',
                'channels' => ['Website', 'Facebook', 'Messenger', 'WhatsApp'],
                'whatsapp' => true,
                'whatsapp_number' => '8801911001004',
                'emotions' => ['Neutral'],
                'user_type' => 'Quality-focused',
                'orders' => [
                    'total' => 9,
                    'successful' => 8,
                    'cancelled' => 1,
                ],
                'product' => [
                    'image' => 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=600&q=80',
                    'title' => 'Smart Casual Hoodie',
                    'description' => 'Minimal design hoodie with soft inner lining and wrinkle-resistant fabric.',
                    'url' => 'https://example.com/products/smart-casual-hoodie',
                ],
                'commented_products' => [
                    [
                        'image' => 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=200&q=80',
                        'title' => 'Smart Casual Hoodie',
                        'short_description' => 'Minimal design hoodie with soft inner lining and wrinkle-resistant fabric.',
                        'url' => 'https://example.com/products/smart-casual-hoodie',
                        'comment_count' => 5,
                        'first_comment_at' => date('Y-m-d H:i:s', strtotime('-60 days')),
                        'last_comment_at' => date('Y-m-d H:i:s', strtotime('-20 minutes')),
                    ],
                    [
                        'image' => 'https://images.unsplash.com/photo-1581235720704-06d3acfcb36f?auto=format&fit=crop&w=200&q=80',
                        'title' => 'Wireless Earbuds Pro',
                        'short_description' => 'Noise-reduced earbuds with long battery backup and compact charging case.',
                        'url' => 'https://example.com/products/wireless-earbuds-pro',
                        'comment_count' => 1,
                        'first_comment_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
                        'last_comment_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
                    ],
                ],
            ],
        ];
    }
}
