<?php

namespace App\Traits;

use App\Models\Tube\SpecialTag;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;

trait TagsProcessor
{
    private function collectTags(string $text, Collection $tags, array $sharedTags): void
    {
        $tag =  str($text)->title();
        $tags->push($this->deTitle($tag));

        $key = $tag->lower()->hash('md5')->toString();
        if (! array_key_exists($key, $sharedTags)) {
            return;
        }

        foreach ($sharedTags[$key] as $sharedTag) {
            if ($tags->contains($sharedTag)) {
                continue;
            }

            $tags->push($sharedTag);
        }
    }

    private function deTitle(Stringable $text): string
    {
        // These words will be changed back to lower case
        $deTitleWords = SpecialTag::getDeTitle();
        foreach ($deTitleWords as $word) {
            $text = $text->replace(
                sprintf(" %s ", ucfirst($word)), " $word "
            );
        }

        // These words will be changed to Title or Upper case.
        SpecialTag::getReTitle()
            ->each(function (SpecialTag $word) use(&$text) {
                $text = $text->replace($word->tag, $word->value);
            });

        if ($text->is('Ai')) {
            $text = $text->replace('Ai', 'AI');
        }

        if ($text->startsWith('Ai ')) {
            $text = $text->replace('Ai ', 'AI ');
        }

        return $this->normalizeAgeString($text->toString());
    }

    private function normalizeAgeString(string $s): string {
        // 1-3 digits at the start, followed by “yo” or “yr” (any case), and nothing else
        return preg_replace('/^(\d{1,3})(Yo|Yr)$/i', '$1\L$2', $s);
    }
}
