<?php

declare(strict_types=1);

namespace App\Services\Tube;

use App\Models\Tube\Content;
use App\Models\Tube\SearchableWord;
use App\Models\Tube\SpecialTag;
use App\Traits\Screenable;
use Illuminate\Support\Facades\Config;

final class SearchableWordsService
{
    use Screenable;

    private readonly array $bandedWords;

    public function __construct()
    {
        $this->bandedWords = $this->getRemovableWords();
    }

    public function execute(Content $content): void
    {
        $words = $this->extractWords([$content->title]);
        $tags = $this->extractWords(
            $content->tags->pluck('name')->toArray()
        );

        $list = array_unique(array_merge($words, $tags));
        foreach ($list as $word) {
            $this->character('.');

            SearchableWord::updateOrCreate([
                'hash' => md5($word),
            ], [
                'words' => $word,
            ]);
        }
    }

    /**
     * @param  array<string>  $items
     */
    private function extractWords(array $items): array
    {
        $spaces = [
            '        ',
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
                ->replaceMatches('/[^A-Za-z0-9 \-]+/', ' ')
                ->replace($spaces, ' ');

            $words = $phrase->explode(' ')
                ->map(fn (string $w) => trim($w))
                ->reject(fn (string $w) => $w === ''
                    || mb_strlen($w) === 1
                    || in_array($w, $this->bandedWords, true)
                );

            $pairs = $words->sliding()
                ->map(fn ($w) => $w->implode(' '))
                ->reject(function (string $w): bool {
                    $parts = explode(' ', $w);

                    return trim($parts[0]) === trim($parts[1]);
                })
                ->all();

            $thirds = $words->sliding(3)
                ->map(fn ($w) => $w->implode(' '))
                ->reject(function (string $w): bool {
                    $parts = explode(' ', $w);

                    $equal = trim($parts[0]) === trim($parts[1]);
                    if ($equal) {
                        return true;
                    }

                    if (count($parts) <= 2) {
                        return false;
                    }

                    return trim($parts[1]) === trim($parts[2]);
                })
                ->all();

            $list->push($words);
            $list->push($pairs);
            $list->push($thirds);
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
            ->sort()
            ->toArray();
    }
}
