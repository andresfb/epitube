<?php

declare(strict_types=1);

use App\Actions\Frontend\ContentFeatureAction;
use App\Actions\Frontend\ContentViewedAction;
use App\Actions\Frontend\ContentWatchLaterAction;
use App\Contracts\FeedMirror;
use App\Enums\Selects;
use App\Models\Tube\Category;
use App\Models\Tube\Content;
use App\Models\Tube\WatchLater;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Mockery\MockInterface;

beforeEach(function (): void {
    Queue::fake();

    $this->mock(FeedMirror::class, function (MockInterface $mock): void {
        $mock->shouldReceive('updateBySlug')->andReturnNull();
    });
});

function createWatchLaterContent(array $overrides = []): Content
{
    $category = Category::query()->firstOrFail();

    return Content::query()->create([
        'category_id' => $category->id,
        'item_id' => (string) Str::uuid(),
        'file_hash' => hash('sha256', (string) Str::uuid()),
        'slug' => Str::lower(Str::random(12)),
        'title' => 'Watch Later Test',
        'active' => true,
        'og_path' => '/tmp/test.mp4',
        ...$overrides,
    ]);
}

it('adds a video to watch later', function (): void {
    $content = createWatchLaterContent();

    $result = resolve(ContentWatchLaterAction::class)->handle($content->slug);

    expect($result)->toBeTrue()
        ->and(WatchLater::query()->where('content_id', $content->id)->exists())->toBeTrue();
});

it('removes a video from watch later when toggled again', function (): void {
    $content = createWatchLaterContent();
    WatchLater::query()->create(['content_id' => $content->id]);

    $result = resolve(ContentWatchLaterAction::class)->handle($content->slug);

    expect($result)->toBeFalse()
        ->and(WatchLater::query()->where('content_id', $content->id)->exists())->toBeFalse();
});

it('returns watch later state from the toggle endpoint', function (): void {
    $content = createWatchLaterContent();

    $this->postJson(route('videos.watch-later', $content->slug))
        ->assertSuccessful()
        ->assertJsonPath('data.watch_later', true)
        ->assertJsonPath('data.status', 200);

    expect(WatchLater::query()->where('content_id', $content->id)->exists())->toBeTrue();
});

it('clears watch later when marked viewed', function (): void {
    $content = createWatchLaterContent();
    WatchLater::query()->create(['content_id' => $content->id]);

    resolve(ContentViewedAction::class)->handle($content->slug);

    expect(WatchLater::query()->where('content_id', $content->id)->exists())->toBeFalse()
        ->and($content->fresh()->viewed)->toBeTrue();
});

it('clears watch later when featured', function (): void {
    $content = createWatchLaterContent();
    WatchLater::query()->create(['content_id' => $content->id]);

    resolve(ContentFeatureAction::class)->handle($content->slug);

    expect(WatchLater::query()->where('content_id', $content->id)->exists())->toBeFalse()
        ->and($content->fresh()->featured)->toBeTrue()
        ->and($content->fresh()->viewed)->toBeTrue();
});

it('exposes watch later on the selects enum', function (): void {
    expect(Selects::WATCH_LATER->value)->toBe('watch-later')
        ->and(Selects::title(Selects::WATCH_LATER))->toBe('Watch Later')
        ->and(Selects::icon(Selects::WATCH_LATER))->toBe('⏱️');
});

it('relates content to watch later', function (): void {
    $content = createWatchLaterContent();
    $watchLater = WatchLater::query()->create(['content_id' => $content->id]);

    expect($content->fresh()->watchLater)->not->toBeNull()
        ->and($content->fresh()->watchLater->is($watchLater))->toBeTrue()
        ->and($watchLater->content->is($content))->toBeTrue();
});
