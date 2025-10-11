<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Dtos\ContentItem;
use App\Models\Category;
use App\Models\Feed;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

final class HomeController extends Controller
{
    public function __invoke(): View
    {
        $cateorySlug = Session::get(
            'category',
            Config::string('constants.main_category')
        );

        $feed = Feed::query()
            ->where(
                'category_id',
                Category::getId($cateorySlug)
            )
            ->where('expires_at', '>', now())
            ->paginate(
                Config::integer('feed.per_page')
            )->map(function (Feed $item) {
                return ContentItem::from($item->content);
            });

        return view('home', $feed->toArray());
    }
}
