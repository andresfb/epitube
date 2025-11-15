<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SpecialTagType;
use App\Libraries\Tube\CacheLibrary;
use App\Models\Tube\SpecialTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class SpecialTagSeeder extends Seeder
{
    public function run(): void
    {
        $this->importBanded();
        $this->importDeTitleTags();
        $this->importReTitleTags();

        CacheLibrary::clear(['special-tags']);
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

    private function importDeTitleTags(): void
    {
        $titleTags = Config::array('content.de_title_words');
        if (blank($titleTags)) {
            Log::error('No tags found for De Title tags.');

            return;
        }

        $this->saveList($titleTags, SpecialTagType::DE_TITLE_WORDS);
    }

    private function saveList(array $tags, SpecialTagType $type): void
    {
        foreach ($tags as $item) {
            SpecialTag::updateOrCreate([
                'slug' => Str::slug($item),
                'type' => $type,
            ], [
                'tag' => $item,
                'active' => true,
            ]);
        }
    }

    private function importReTitleTags(): void
    {
        $tags = Config::array('content.re_title_words');
        if (blank($tags)) {
            Log::error('No tags found for Re Title tags.');

            return;
        }

        $order = 1;
        foreach ($tags as $item) {
            $parts = explode('|', $item);

            SpecialTag::updateOrCreate([
                'slug' => md5($parts[0]),
                'type' => SpecialTagType::RE_TITLE_WORDS,
            ], [
                'tag' => $parts[0],
                'value' => $parts[1],
                'active' => true,
                'order' => $order,
            ]);

            $order++;
        }
    }
}
