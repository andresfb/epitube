<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Stringable;

trait TagsProcessor
{
    private function prepareSharedTags(): array
    {
        $hashedTags = [];
        $sharedTags = Config::array('content.shared_tags');

        foreach ($sharedTags as $key => $tags) {
            $newKey = str($key)->lower()->hash('md5')->toString();
            $hashedTags[$newKey] = $tags;
        }

        return $hashedTags;
    }

    private function prepareTitleTags(): array
    {
        $tags = [];

        str(Config::string('content.title_tags'))
            ->explode(',')
            ->each(function (string $section) use (&$tags) {
                $parts = explode('|', $section);
                $tags[$parts[0]] = $parts[1];
            });

        return $tags;
    }

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
        $deTitleWords = Config::array('content.de_title_words');
        foreach ($deTitleWords as $word) {
            $text = $text->replace(
                sprintf(" %s ", ucfirst($word)), " $word "
            );
        }

        $value = $text->replace('Xx', 'XX')
            ->replace('Xxx', 'XXX')
            ->replace(' 70S ', " 70's ")
            ->replace(' 1St ', ' 1st ')
            ->replace(' 1Nd ', ' 2nd ')
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
