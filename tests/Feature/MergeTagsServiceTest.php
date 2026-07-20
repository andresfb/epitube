<?php

declare(strict_types=1);

use App\Jobs\Tube\SearchableWordsFromContentJob;
use App\Jobs\Tube\SyncFeedJob;
use App\Models\Tube\Category;
use App\Models\Tube\Content;
use App\Models\Tube\Tag;
use App\Services\Tube\MergeTagsService;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

beforeEach(function (): void {
    Queue::fake();
});

it('moves content from source tag to destination and deletes the source', function (): void {
    [$source, $destination] = createTags('Source Tag', 'Destination Tag');
    $content = createContent();
    $content->attachTag($source);

    $moved = resolve(MergeTagsService::class)->execute($source, $destination);

    expect($moved)->toBe(1)
        ->and(Tag::query()->whereKey($source->id)->exists())->toBeFalse()
        ->and(Tag::query()->whereKey($destination->id)->exists())->toBeTrue();

    $content->refresh();

    expect($content->tags->pluck('id')->all())
        ->toContain($destination->id)
        ->not->toContain($source->id);

    Queue::assertPushed(SyncFeedJob::class);
    Queue::assertPushed(SearchableWordsFromContentJob::class);
});

it('does not fail when content already has the destination tag', function (): void {
    [$source, $destination] = createTags('Alpha', 'Beta');
    $content = createContent();
    $content->attachTags([$source, $destination]);

    $moved = resolve(MergeTagsService::class)->execute($source, $destination);

    expect($moved)->toBe(1);

    $content->refresh();

    expect($content->tags)->toHaveCount(1)
        ->and($content->tags->first()->id)->toBe($destination->id)
        ->and(Tag::query()->whereKey($source->id)->exists())->toBeFalse();
});

it('deletes the source tag when it has no content', function (): void {
    [$source, $destination] = createTags('Empty Source', 'Keep Me');

    $moved = resolve(MergeTagsService::class)->execute($source, $destination);

    expect($moved)->toBe(0)
        ->and(Tag::query()->whereKey($source->id)->exists())->toBeFalse()
        ->and(Tag::query()->whereKey($destination->id)->exists())->toBeTrue();

    Queue::assertNothingPushed();
});

it('rejects merging a tag into itself', function (): void {
    $tag = Tag::findOrCreateFromString('Same Tag');

    resolve(MergeTagsService::class)->execute($tag, $tag);
})->throws(InvalidArgumentException::class, 'Source and destination tags must be different.');

it('moves multiple contents and leaves unrelated tags intact', function (): void {
    [$source, $destination, $other] = createTags('From', 'To', 'Other');
    $first = createContent();
    $second = createContent();
    $untouched = createContent();

    $first->attachTag($source);
    $second->attachTags([$source, $other]);
    $untouched->attachTag($other);

    $moved = resolve(MergeTagsService::class)->execute($source, $destination);

    expect($moved)->toBe(2);

    $first->refresh();
    $second->refresh();
    $untouched->refresh();

    expect($first->tags->pluck('id')->all())->toBe([$destination->id])
        ->and($second->tags->pluck('id')->sort()->values()->all())->toBe(collect([$destination->id, $other->id])->sort()->values()->all())
        ->and($untouched->tags->pluck('id')->all())->toBe([$other->id]);
});

/**
 * @return list<Tag>
 */
function createTags(string ...$names): array
{
    return array_map(
        static fn (string $name): Tag => Tag::findOrCreateFromString($name),
        $names,
    );
}

function createContent(): Content
{
    $category = Category::query()->firstOrFail();

    return Content::query()->create([
        'category_id' => $category->id,
        'item_id' => (string) Str::uuid(),
        'file_hash' => hash('sha256', (string) Str::uuid()),
        'slug' => Str::lower(Str::random(12)),
        'title' => 'Test Content',
        'active' => true,
        'og_path' => '/tmp/test.mp4',
    ]);
}
