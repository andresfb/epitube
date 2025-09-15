<?php

namespace App\Libraries;

use Illuminate\Support\Str;

class TitleParserLibrary
{
    private string $title;

    public function parseFileName(array $fileInfo): string
    {
        return $this->cleanString($fileInfo['filename'])
            ->removeDashes()
            ->stripBrackets()
            ->addDirectoriesToTitle($fileInfo['dirname'])
            ->changeWords()
            ->removeStartingNumbers()
            ->removeSpaces();
    }

    private function cleanString(string $value, string $replace = ' '): self
    {
        $this->title = Str::of($value)
            ->lower()
            ->replace(
                ['.', '_', '~', '?', ':', ','],
                $replace
            )
            ->replace(['|', '/'], '')
            ->replace(['(', ')'], ' - ')
            ->trim()
            ->toString();

        return $this;
    }

    private function removeDashes(): self
    {
        // replace dashes that are presided by words (i.e. solo-company = solo company)
        $titled = preg_replace('/(?<=\w)-(?=\w)/', ' ', $this->title);
        $this->title = trim($titled);

        return $this;
    }

    private function addDirectoriesToTitle(string $directory): self
    {
        $titled = Str::of($this->title);

        $dirArray = Str::of($directory)
            ->explode('/')
            ->map(fn ($item) => $this->cleanString($item, '')->removeSpaces())
            ->reject(fn ($item) => empty(trim($item)))
            ->toArray();

        $titled = $titled->replace($dirArray, '')
            ->replace(
                array_map(static fn ($item) => str_replace(' ', '', $item), $dirArray),
                ''
            )
            ->trim()
            ->ltrim('-')
            ->trim();

        foreach (collect($dirArray)->reverse() as $item) {
            $titled = $titled->prepend("$item - ");
        }

        $this->title = $titled->trim()->toString();

        return $this;
    }

    private function stripBrackets(): self
    {
        $str = $this->title;

        // 1. Remove [ ... ] segments
        $str = preg_replace('/\[[^]]*]/', '', $str);

        // 2. Collapse multiple spaces into one
        $str = preg_replace('/\s+/', ' ', $str);

        // 3. Remove any space that ends up before the file extension
        $str = preg_replace('/\s+\./', '.', $str);

        // 4. Trim leading / trailing spaces
        $this->title = trim($str);

        return $this;
    }

    private function changeWords(): self
    {
        $titled = Str::of($this->title)
            ->replace(
                [
                    'step',
                    'step-',
                    'xxx',
                    '360p',
                    '480p',
                    '720p',
                    '1080p',
                    '2160p',
                    'hevc',
                    'x265',
                    'mp4',
                    'wrb',
                    'wr',
                    'internal',
                    'vsex',
                    'prt',
                    'vs',
                    'kt',
                    'xleech',
                    'worldmkv',
                    'mov',
                    '[xv',
                    'xvid',
                ],
                ''
            )
            ->replace('...', '.')
            ->replace('..', '.')
            ->trim()
            ->rtrim('v')
            ->rtrim('-');

        foreach ($this->getWordMatrix() as $word => $replacements) {
            if (! $titled->contains($word)) {
                continue;
            }

            $titled = $titled->replace(
                $word,
                collect($replacements)->random(),
            );
        }

        $this->title = $titled->trim()->toString();

        return $this;
    }

    private function getWordMatrix(): array
    {
        return [
            'brother' => $this->getBoyGeneric(),
            'bro' => $this->getBoyGeneric(),
            'sister' => $this->getGirlGeneric(),
            'sis' => $this->getGirlGeneric(),
            'father' => $this->getBoyGeneric(),
            'dad' => $this->getBoyGeneric(),
            'daddy' => $this->getBoyGeneric(),
            'son' => $this->getBoyGeneric(),
            's0n' => $this->getBoyGeneric(),
            'mother' => $this->getGirlGeneric(),
            'mom' => $this->getGirlGeneric(),
            'mommy' => $this->getGirlGeneric(),
            'daughter' => $this->getBoyGeneric(),
            'aunt' => $this->getGeneric(),
            'uncle' => $this->getBoyGeneric(),
            'niece' => $this->getGeneric(),
            'nephew' => $this->getBoyGeneric(),
            'cousin' => $this->getGeneric(),
            'in-law' => $this->getGeneric(),
            'family' => ['group', 'friends', 'pals']
        ];
    }

    private function getGeneric(): array
    {
        return [
            'friend',
            'college',
            'boss',
            'stranger',
            'pal',
            'bff',
            'bar-tender',
        ];
    }

    private function getBoyGeneric(): array
    {
        return array_merge(
            $this->getGeneric(),
            [
                'girlfriend',
                'sister-in-law',
                'mother-in-law',
                'hostess',
                'waitress',
                'comadre',
            ]
        );
    }

    private function getGirlGeneric(): array
    {
        return array_merge(
            $this->getGeneric(),
            [
                'boy-friend',
                'brother-in-law',
                'father-in-law',
                'fireman',
                'waiter',
                'compadre',
            ]
        );
    }

    private function removeStartingNumbers(): self
    {
        // Remove the number groups that directly follow the starting words
        // i.e. "collaborate 01 02 25 in the code 1080p" = "collaborate in the code 1080p"
        $titled = preg_replace('/^(.+?)\s+(?:\d+\s+)+/', '$1 ', $this->title);
        $this->title = trim($titled);

        return $this;
    }

    private function removeSpaces(): string
    {
        return Str::of($this->title)
            ->replace('    ', ' ') // Quadruple space
            ->replace('   ', ' ') // Triple space
            ->replace('  ', ' ') // Double space
            ->replace('- -', '-')
            ->trim()
            ->toString();
    }
}
