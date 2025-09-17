<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class TitleParserLibrary
{
    private string $title;

    private string $rootDirectory = '';

    public function parseFileName(array $fileInfo): Stringable
    {
        Log::notice("Parsing Title for file: {$fileInfo['filename']}");

        return $this->cleanString($fileInfo['filename'])
            ->removeDashes()
            ->stripBrackets()
            ->addDirectoriesToTitle($fileInfo['dirname'])
            ->changeWords()
            ->removeStartingNumbers()
            ->removeFormatedDates()
            ->removeSpaces();
    }

    public function getRootDirectory(): string
    {
        return $this->rootDirectory;
    }

    public function replaceWords(Stringable|string $source): string
    {
        $titled = $source instanceof Stringable ? $source : Str::of($source);

        foreach ($this->getWordMatrix() as $word => $replacements) {
            if (! $titled->contains($word)) {
                continue;
            }

            $titled = $titled->replace(
                $word,
                collect($replacements)->random(),
            );
        }

        return $titled->toString();
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
            ->replace('@', '')
            ->trim()
            ->toString();

        return $this;
    }

    private function removeDashes(): self
    {
        // replace dashes that are presided by words (i.e. solo-company = solo company)
        $titled = preg_replace('/(?<=\w)-(?=\w)/', ' ', $this->title);
        $this->title = trim((string) $titled);

        return $this;
    }

    private function addDirectoriesToTitle(string $directory): self
    {
        $titled = Str::of($this->title);

        $dirList = Str::of($directory)
            ->explode('/')
            ->map(fn ($item): \Illuminate\Support\Stringable => $this->cleanString($item, '')->removeSpaces())
            ->reject(fn ($item): bool => empty(trim((string) $item)));

        $dirList->shift();
        $this->rootDirectory = strtolower($dirList->first());

        $titled = $titled->replace($dirList, '')
            ->replace(
                array_map(static fn ($item): string|array => str_replace(' ', '', $item), $dirList->toArray()),
                ''
            )
            ->trim()
            ->ltrim('-')
            ->trim();

        foreach ($dirList->reverse() as $item) {
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
        $str = preg_replace('/\s+/', ' ', (string) $str);

        // 3. Remove any space that ends up before the file extension
        $str = preg_replace('/\s+\./', '.', (string) $str);

        // 4. Trim leading / trailing spaces
        $this->title = trim((string) $str);

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
                    '-wr',
                    ' rq',
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

        $this->title = $this->replaceWords($titled);

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
            'colleague',
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
        $pattern = '/\b(\d{2})\s+(\d{2})\s+(\d{2})\b/';
        if (in_array(preg_match($pattern, $this->title, $matches), [0, false], true)) {
            return $this;
        }

        $this->title = trim(
            str_replace($matches[0], '', $this->title)
        );

        return $this;
    }

    private function removeFormatedDates(): self
    {
        $pattern = '/\((\d{2}[.\s]\d{2}[.\s]\d{4})\)/';
        if (in_array(preg_match($pattern, $this->title, $matches), [0, false], true)) {
            return $this;
        }

        $this->title = trim(
            str_replace($matches[0], '', $this->title)
        );

        return $this;
    }

    private function removeSpaces(): Stringable
    {
        return Str::of($this->title)
            ->replace(['(', ')'], ' - ')
            ->replace('    ', ' ') // Quadruple space
            ->replace('   ', ' ') // Triple space
            ->replace('  ', ' ') // Double space
            ->replace('- -', '-')
            ->trim();
    }
}
