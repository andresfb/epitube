<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Frontend\RandomVideosAction;
use App\Actions\Frontend\TagGetListAction;
use App\Dtos\Tube\CategoryItem;
use App\Dtos\Tube\RandomVideoItem;
use App\Http\Requests\RandomVideoRequest;
use App\Models\Tube\Category;
use Illuminate\View\View;

final class RandomVideosController extends Controller
{
    public function __invoke(
        RandomVideoRequest $form,
        RandomVideosAction $videosAction,
        TagGetListAction $tagsAction
    ): View {
        $filters = RandomVideoItem::from($form);
        $randomList = $videosAction->handle($filters);

        return view(
            'random.list',
            [
                'feed' => $randomList->feed,
                'links' => $randomList->links,
                'count' => $randomList->total,
                'filters' => $filters,
                'categories' => Category::query()
                    ->get()
                    ->map(fn (Category $category): CategoryItem => CategoryItem::from($category)),
                'tags' => $tagsAction->handle(),
                'range' => [5, 25, 50, 75, 100],
            ]
        );
    }
}
