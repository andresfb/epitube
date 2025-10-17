<?php

namespace Database\Seeders;

use App\Enums\SpecialTagType;
use App\Models\Tube\SpecialTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SpecialTagSeeder extends Seeder
{
    public function run(): void
    {
        $this->importBanded();
        $this->importTitleTags();

        Cache::tags('special-tags')->flush();
    }

    private function importBanded(): void
    {
        $banded = Config::array('content.banded_tags');
        if (blank($banded)) {
            Log::error('No tags found for banded tags.');

            return;
        }

        $this->saveList($banded, SpecialTagType::BANDED);
    }

    private function importTitleTags(): void
    {
        $titleTags = Config::array('content.de_title_words');
        if (blank($titleTags)) {
            Log::error('No tags found for title tags.');

            return;
        }

        $this->saveList($titleTags, SpecialTagType::TITLE_WORDS);
    }

    private function saveList(array $tags, SpecialTagType $type): void
    {
        foreach ($tags as $item) {
            SpecialTag::updateOrCreate([
                'slug' => Str::slug($item),
                'type' => $type,
            ], [
                'tag' => $item,
            ]);
        }
    }
}
