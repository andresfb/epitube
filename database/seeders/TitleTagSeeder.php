<?php

namespace Database\Seeders;

use App\Models\Tube\TitleTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class TitleTagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [];
        str(Config::string('content.title_tags'))
            ->explode(',')
            ->each(function (string $section) use (&$tags) {
                $parts = explode('|', $section);
                $tags[$parts[0]] = $parts[1];
            });

        if (blank($tags)) {
            Log::error('No title tags found');

            return;
        }

        foreach ($tags as $word => $tag) {
            TitleTag::updateOrCreate([
                'hash' => md5($word),
            ], [
                'word' => $word,
                'tag' => $tag,
                'active' => true,
            ]);
        }

        Cache::tags('title-tags')->flush();
    }
}
