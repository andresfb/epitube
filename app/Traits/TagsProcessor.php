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
        $deTitleWords = SpecialTag::getList(SpecialTagType::TITLE_WORDS);
        foreach ($deTitleWords as $word) {
            $text = $text->replace(
                sprintf(" %s ", ucfirst($word)), " $word "
            );
        }

        $value = $text->replace('Xx', 'XX')
            ->replace('Xxx', 'XXX')
            ->replace(' 70S ', " 70's ")
            ->replace(' 1St ', ' 1st ')
            ->replace(' 2Nd ', ' 2nd ')
            ->replace('Hd ', 'HD ')
            ->replace(' Hd', ' HD');

        if ($value->is('Ai')) {
            $value = $value->replace('Ai', 'AI');
        }

        if ($value->startsWith('Ai ')) {
            $value = $value->replace('Ai ', 'AI ');
        }

        return $value->toString();
    }
}
