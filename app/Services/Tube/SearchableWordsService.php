<?php

namespace App\Services\Tube;

use App\Models\Tube\Content;
use App\Models\Tube\SearchableWord;
use App\Models\Tube\SpecialTag;
use Illuminate\Support\Facades\Config;

class SearchableWordsService
{
    private array $bandedWords = [];

    public function execute(Content $content): void
    {
        $this->bandedWords = $this->getRemovableWords();

        $words = $this->extractWords([$content->title]);

        $tags = $this->extractWords(
            $content->tags->pluck('name')->toArray()
        );

        $list = array_unique(array_merge($words, $tags));

        dd($list);;

        foreach ($list as $word) {
            SearchableWord::updateOrCreate([
                'hash' => md5($word),
            ], [
                'word' => $word,
            ]);
        }
    }

    /**
     * @param array<string> $items
     */
    private function extractWords(array $items): array
    {
        $spaces = [
            '       ',
            '      ',
            '     ',
            '    ',
            '   ',
            '  ',
        ];

        $list = collect();
        foreach ($items as $item) {
            $phrase = str($item)
                ->lower()
                ->replace("'s", '')
                ->replace("'", '')
                ->replace([' - ', '- ', ' -'], ' ')
                ->replaceMatches('/\d+/', ' ')
                ->replace($this->bandedWords, ' ')
                ->replaceMatches('/[^A-Za-z0-9 \-]+/', ' ')
                ->replace($spaces, ' ');

            $words = $phrase->explode(' ')
                ->map(fn (string $w) => trim($w))
                ->reject(fn (string $w) => $w === '' || strlen($w) === 1);

            $pairs = $words->sliding()
                ->map(fn($w) => $w->implode(' '))
                ->reject(function (string $w): bool {
                    $parts = explode(' ', $w);
                    return trim($parts[0]) === trim($parts[1]);
                })
                ->all();

            $list->push($words);
            $list->push($pairs);
        }

        return $list->flatten()
            ->unique()
            ->map(fn (string $word) => trim($word))
            ->reject(fn (string $word) => $word === '')
            ->toArray();
    }

    private function getRemovableWords(): array
    {
        return collect(array_unique(array_merge(
                SpecialTag::getRemovable(),
                Config::array('constants.removable_words')
        )))
        ->flatten()
        ->map(fn(string $item) => sprintf(' %s ', mb_trim($item)))
        ->toArray();
    }
}
