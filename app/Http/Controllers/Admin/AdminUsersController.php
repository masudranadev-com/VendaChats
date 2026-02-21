<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUsersController extends Controller
{
    public function users(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'whatsapp' => (string) $request->query('whatsapp', 'all'),
            'emotion' => (string) $request->query('emotion', 'all'),
            'user_type' => (string) $request->query('user_type', 'all'),
        ];

        $allUsers = collect($this->usersDataset());

        $users = $allUsers
            ->filter(function (array $user) use ($filters): bool {
                if ($filters['q'] !== '') {
                    $needle = strtolower($filters['q']);
                    $haystack = strtolower(implode(' ', [
                        $user['name'] ?? '',
                        $user['user_id'] ?? '',
                        $user['sender_id'] ?? '',
                    ]));

                    if (! str_contains($haystack, $needle)) {
                        return false;
                    }
                }

                if ($filters['whatsapp'] === 'yes' && ! ($user['whatsapp'] ?? false)) {
                    return false;
                }

                if ($filters['whatsapp'] === 'no' && ($user['whatsapp'] ?? false)) {
                    return false;
                }

                if ($filters['emotion'] !== 'all') {
                    $hasEmotion = collect($user['emotions'] ?? [])
                        ->contains(fn (string $emotion): bool => strtolower($emotion) === strtolower($filters['emotion']));

                    if (! $hasEmotion) {
                        return false;
                    }
                }

                if ($filters['user_type'] !== 'all' && ($user['user_type'] ?? '') !== $filters['user_type']) {
                    return false;
                }

                return true;
            })
            ->values();

        $availableEmotions = $allUsers
            ->pluck('emotions')
            ->flatten()
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('admin.users.index', [
            'title' => 'Users',
            'subtitle' => 'Track buyer emotion, WhatsApp status, and user type in one place.',
            'users' => $users->all(),
            'totalUsers' => $allUsers->count(),
            'filteredUsers' => $users->count(),
            'whatsAppUsers' => $allUsers->where('whatsapp', true)->count(),
            'priceSensitiveUsers' => $allUsers->where('user_type', 'Price-sensitive')->count(),
            'availableEmotions' => $availableEmotions,
            'filters' => $filters,
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
                'important_messages' => [
                    'Hi',
                    'Can you give me best price?',
                    'I need fast delivery to Dhaka.',
                ],
                'product' => [
                    'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=600&q=80',
                    'title' => 'Premium Cotton T-Shirt',
                    'description' => 'Soft breathable cotton tee with regular fit and durable print quality.',
                    'url' => 'https://example.com/products/premium-cotton-tshirt',
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
                'important_messages' => [
                    'I want original quality only.',
                    'Do you have premium packaging?',
                    'Can you share more close-up photos?',
                ],
                'product' => [
                    'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80',
                    'title' => 'AirFlex Running Shoes',
                    'description' => 'Lightweight cushioned running shoe designed for daily comfort and long walks.',
                    'url' => 'https://example.com/products/airflex-running-shoes',
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
                'important_messages' => [
                    'Why is this more expensive than last week?',
                    'Need discount or I will skip.',
                    'Waiting for your final offer.',
                ],
                'product' => [
                    'image' => 'https://images.unsplash.com/photo-1581235720704-06d3acfcb36f?auto=format&fit=crop&w=600&q=80',
                    'title' => 'Wireless Earbuds Pro',
                    'description' => 'Noise-reduced earbuds with long battery backup and compact charging case.',
                    'url' => 'https://example.com/products/wireless-earbuds-pro',
                ],
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
                'important_messages' => [
                    'Need gift option.',
                    'Is this available in black?',
                    'Please confirm return policy.',
                ],
                'product' => [
                    'image' => 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=600&q=80',
                    'title' => 'Smart Casual Hoodie',
                    'description' => 'Minimal design hoodie with soft inner lining and wrinkle-resistant fabric.',
                    'url' => 'https://example.com/products/smart-casual-hoodie',
                ],
            ],
        ];
    }
}
