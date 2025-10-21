<?php

namespace App\Traits;

use App\Enums\SpecialTagType;
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
        $deTitleWords = SpecialTag::getList(SpecialTagType::TITLE_WORDS);
        foreach ($deTitleWords as $word) {
            $text = $text->replace(
                sprintf(" %s ", ucfirst($word)), " $word "
            );
        }

        // These words will be capitalized
        $value = $text->replace('Xx', 'XX')
            ->replace('Xxx', 'XXX')
            ->replace(' 70S ', " 70's ")
            ->replace(' 1St ', ' 1st ')
            ->replace(' 2Nd ', ' 2nd ')
            ->replace(' Tv ', ' TV ')
            ->replace(' Dp ', ' DP ')
            ->replace(' Mvp ', ' MVP ')
            ->replace(' Kpop ', ' KPop ')
            ->replace(' Lut ', ' LUT ')
            ->replace(' Bbc ', ' BBC ')
            ->replace(' Hq ', ' HQ ')
            ->replace('Hd ', 'HD ')
            ->replace('- the ', '- The ')
            ->replace('- a ', '- A ')
            ->replace('- in ', '- In ')
            ->replace('- my ', '- My ')
            ->replace(' S ', "'s ")
            ->replace(' Hd', ' HD');

        if ($value->is('Ai')) {
            $value = $value->replace('Ai', 'AI');
        }

        if ($value->startsWith('Ai ')) {
            $value = $value->replace('Ai ', 'AI ');
        }

        return $this->normalizeAgeString($value->toString());
    }

    private function normalizeAgeString(string $s): string {
        // 1‑3 digits at the start, followed by “yo” (any case) and nothing else
        return preg_replace('/^(\d{1,3})Yo$/i', '$1yo', $s);
    }
}
