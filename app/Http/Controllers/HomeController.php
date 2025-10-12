<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Dtos\ContentItem;
use App\Models\Tube\Feed;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

final class HomeController extends Controller
{
    public function __invoke(): View
    {
        $feed = Feed::query()
            ->where('category_id', 1)
            ->where('expires_at', '>', now())
            ->paginate(
                Config::integer('feed.per_page')
            )->map(function (Feed $item) {
                return ContentItem::from($item->content);
            });

        return view('home');
    }
}
