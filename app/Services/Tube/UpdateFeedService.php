<?php

namespace App\Services\Tube;

use App\Models\Tube\Content;
use App\Traits\Screenable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Config;

class UpdateFeedService
{
    use Screenable;

    public function execute(?CarbonInterface $froDate = null, int $limit = 0): void
    {
        if ($froDate === null) {
            $froDate = now()->subDay()->endOfDay();
        }

        if ($limit === 0) {
            $limit = Config::integer('content.max_import_videos') * 2;
        }

        $this->info("Feed update with Contents created before {$froDate->toDateTimeString()} with a count of $limit\n");

        Content::query()
            ->usable()
            ->whereDate('created_at', '<=', $froDate)
            ->latest()
            ->limit($limit)
            ->each(function (Content $content): void {
                $content->touch();
                $this->character('.');
            });

        $this->line();
    }
}
