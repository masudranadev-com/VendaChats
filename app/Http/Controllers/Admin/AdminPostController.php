<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdminPostController extends Controller
{
    public function posts(Request $request): View
    {
        $filters = [
            'order_by' => (string) $request->query('order_by', 'latest'),
            'days' => (string) $request->query('days', 'all'),
        ];

        $now = now();
        $allPosts = collect($this->postsDataset($now));

        $posts = $allPosts
            ->filter(function (array $post) use ($filters, $now): bool {
                $postedAt = $post['posted_at'];

                return match ($filters['days']) {
                    'today' => $postedAt->isSameDay($now),
                    '7' => $postedAt->greaterThanOrEqualTo($now->copy()->subDays(7)),
                    '30' => $postedAt->greaterThanOrEqualTo($now->copy()->subDays(30)),
                    default => true,
                };
            });

        if ($filters['order_by'] === 'highest_comments') {
            $posts = $posts->sortByDesc('total_comments');
        } else {
            $posts = $posts->sortByDesc('posted_at');
        }

        $posts = $posts
            ->values()
            ->map(function (array $post) use ($now): array {
                return [
                    'title' => $post['title'],
                    'total_comments' => (int) $post['total_comments'],
                    'time_ago' => $this->formatTimeAgo($post['posted_at'], $now),
                    'posted_at' => $post['posted_at'],
                ];
            });

        return view('admin.posts.index', [
            'title' => 'Posts',
            'subtitle' => 'Review post activity and prioritize reply opportunities by time and engagement.',
            'posts' => $posts->all(),
            'filters' => $filters,
            'totalPosts' => $allPosts->count(),
            'totalComments' => $allPosts->sum('total_comments'),
            'highestCommentCount' => (int) $allPosts->max('total_comments'),
            'countdownSeconds' => 610,
        ]);
    }

    private function formatTimeAgo(Carbon $postedAt, Carbon $now): string
    {
        $seconds = max(0, $postedAt->diffInSeconds($now));
        $days = intdiv($seconds, 86400);
        $seconds = $seconds % 86400;
        $hours = intdiv($seconds, 3600);
        $seconds = $seconds % 3600;
        $minutes = intdiv($seconds, 60);

        if ($days > 0) {
            return "{$days}d {$hours}h ago";
        }

        if ($hours > 0) {
            return "{$hours}h {$minutes}m ago";
        }

        return max(1, $minutes)."m ago";
    }

    private function postsDataset(Carbon $now): array
    {
        return [
            [
                'title' => 'How to select the perfect summer outfit for daily wear',
                'total_comments' => 123,
                'posted_at' => $now->copy()->subMinutes(16),
            ],
            [
                'title' => 'Weekend flash sale on premium cotton t-shirt collection',
                'total_comments' => 6252,
                'posted_at' => $now->copy()->subHours(12)->subMinutes(10),
            ],
            [
                'title' => 'Limited stock alert for AirFlex performance running shoes',
                'total_comments' => 987,
                'posted_at' => $now->copy()->subDay()->subHours(3),
            ],
            [
                'title' => 'Customer spotlight review with before and after photos',
                'total_comments' => 452,
                'posted_at' => $now->copy()->subDays(2)->subHours(4),
            ],
            [
                'title' => 'Top 5 gift bundle ideas for upcoming festival season',
                'total_comments' => 781,
                'posted_at' => $now->copy()->subDays(5)->subHours(7),
            ],
            [
                'title' => 'New arrival: wireless earbuds pro with launch discount',
                'total_comments' => 1450,
                'posted_at' => $now->copy()->subDays(8)->subHours(2),
            ],
            [
                'title' => 'Behind the scenes packaging quality and dispatch process',
                'total_comments' => 304,
                'posted_at' => $now->copy()->subDays(12)->subHours(11),
            ],
            [
                'title' => 'Back in stock update for smart casual hoodie in black',
                'total_comments' => 2198,
                'posted_at' => $now->copy()->subDays(19)->subHours(6),
            ],
            [
                'title' => 'Delivery timeline update during high traffic campaign period',
                'total_comments' => 76,
                'posted_at' => $now->copy()->subDays(25)->subHours(9),
            ],
            [
                'title' => 'Month end clearance deal with up to 40 percent off',
                'total_comments' => 3421,
                'posted_at' => $now->copy()->subDays(34)->subHours(8),
            ],
            [
                'title' => 'How to measure shoe size correctly before ordering online',
                'total_comments' => 1180,
                'posted_at' => $now->copy()->subDays(47)->subHours(5),
            ],
        ];
    }
}
