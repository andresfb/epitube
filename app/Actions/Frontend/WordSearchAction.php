<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\WordResultItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Meilisearch\Client;

final readonly class WordSearchAction
{
    /**
     * @return Collection<WordResultItem>
     */
    public function handle(string $term): Collection
    {
        $client = new Client(
            config('scout.meilisearch.host'),
            config('scout.meilisearch.key')
        );

        $index = $client->index(Config::string('content.search_word_index'));

        return collect(
            $index->search($term, [
                'limit' => 10,
            ])->getHits()
        )
            ->map(fn (array $result) => WordResultItem::from($result));
    }
}
