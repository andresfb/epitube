<?php

namespace App\Services;

use Aliziodev\LaravelTaxonomy\Enums\TaxonomyType;
use Aliziodev\LaravelTaxonomy\Models\Taxonomy;
use App\Models\Content;

class ParseTagsService
{
    public function execute(Content $content, array $fileInfo): void
    {
        $tags = $this->extractTags($fileInfo);

        $tagIds = [];
        foreach ($tags as $tagName) {
            $tag = Taxonomy::updateOrCreate([
                'name' => $tagName,
                'type' => TaxonomyType::Tag->value,
            ]);

            $tagIds[] = $tag->id;
        }

        $content->attachTaxonomies($tagIds);
    }

    private function extractTags(array $fileInfo): array
    {
        return str($fileInfo['dirname'])
            ->replace(config('content.data_path'), '')
            ->lower()
            ->explode('/')
            ->map(fn ($tag) => trim($tag))
            ->reject(fn(string $part) => empty($part))
            ->toArray();
    }
}
