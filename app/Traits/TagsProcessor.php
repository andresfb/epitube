<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

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
        $tag =  str($text)
            ->title()
            ->replace('Xxx', 'XXX')
            ->replace('Xx', 'XX')
            ->replace(' The ', ' the ')
            ->replace(' In ', ' in ')
            ->replace(' Is ', ' is ')
            ->replace(' Are ', ' are ')
            ->replace(' Was ', ' was ')
            ->replace(' Were ', ' were ')
            ->replace(' At ', ' at ')
            ->replace(' And ', ' and ')
            ->replace(' To ', ' to ')
            ->replace(' Of ', ' of ')
            ->replace(' My ', ' my ')
            ->replace(' By ', ' by ')
            ->replace(' For ', ' for ')
            ->replace(' A ', ' a ')
            ->replace(' 70S ', " 70's ")
            ->replace(' 1St ', ' 1st ')
            ->replace(' De ', ' de ')
            ->replace('Hd ', 'HD ')
            ->replace(' Hd', ' HD');

        if ($tag->is('Ai')) {
            $tag = $tag->replace('Ai', 'AI');
        }

        $tags->push($tag->toString());

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
}
