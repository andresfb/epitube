<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Dtos\ContentItem;
use App\Models\Content;
use App\Models\Feed;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

final class TestAppCommand extends Command
{
    protected $signature = 'test:app';

    protected $description = 'Test app command';

    public function handle(): void
    {
        Log::notice('Test started at: '. now()->format('Y-m-d H:i:s'));

        try {
            clear();
            intro('Starting test');

//            $content = Content::where('id', 6)
//                ->firstOrFail();
//            dump(ContentItem::withRelated($content)->toArray());

//            $content = Content::inRandomOrder()
//                ->firstOrFail();
//
//            Feed::generate($content);

            $feed = Feed::where('id', 8)->firstOrFail();
            dump($feed->toArray());
        } catch (Exception $e) {
            error($e->getMessage());
        } finally {
            Log::notice('Test finished at: '. now()->format('Y-m-d H:i:s'));
            $this->newLine();
            outro('Done');
        }
    }
}
