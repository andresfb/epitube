<?php

declare(strict_types=1);

namespace App\Libraries\Tube;

use App\Traits\DirectoryChecker;
use App\Traits\TagsProcessor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

final class TitleParserLibrary
{
    use DirectoryChecker;
    use TagsProcessor;

    private string $title;

    private string $rootDirectory = '';

    private array $extraTags = [];

    public function getExtraTags(): array
    {
        return $this->extraTags;
    }

    public function parseFileName(array $fileInfo): Stringable
    {
        Log::notice("Parsing Title for file: {$fileInfo['filename']}");

        $this->processTitleTags($fileInfo['filename']);

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

    public function removeWords(Stringable|string $source): Stringable
    {
        $titled = $source instanceof Stringable ? $source : Str::of($source);
        return $titled->replace(
            [
                'step',
                'step-',
                'xxx',
                '240p',
                '360p',
                '480p',
                '720p',
                '1080p',
                '2160p',
                'hevc',
                'x264',
                'x265',
                'mp4',
                'wrb',
                '-wr',
                ' rq',
                'internal',
                'vsex',
                '-vs',
                'prt',
                'kt',
                'xleech',
                'worldmkv',
                '[xv',
                '.xvid',
                '-xvid',
                '_xvid',
                'xvid1',
                'xvid-',
                ' tg',
                ' hd ',
                ' bbc',
                'webrip',
            ], '')
            ->replace('...', '.')
            ->replace('..', '.')
            ->trim()
            ->rtrim('vs')
            ->rtrim('full')
            ->rtrim('v')
            ->rtrim('xvid')
            ->rtrim(' mp')
            ->rtrim('-');
    }

    private function cleanString(string $value, string $replace = ' '): self
    {
        $this->title = Str::of($value)
            ->lower()
            ->replace(
                ['.', '_', '~', '?', ':', ','],
                $replace
            )
            ->replace(['|', '｜', '/'], '')
            ->replace('@', '')
            ->trim()
            ->toString();

        return $this;
    }

    private function removeDashes(): self
    {
        // replace dashes that are presided by words (i.e., solo-company = solo company)
        $titled = preg_replace('/(?<=\w)-(?=\w)/', ' ', $this->title);
        $this->title = mb_trim((string) $titled);

        return $this;
    }

    private function addDirectoriesToTitle(string $directory): self
    {
        $titled = Str::of($this->title);

        /** @var Collection<Stringable> $dirList */
        $dirList = Str::of($directory)
            ->explode('/')
            ->map(fn ($item): Stringable => $this->cleanString($item, '')->removeSpaces())
            ->reject(function (Stringable $item): bool {
                $value = $item->toString();

                return blank($value) || $this->isHash($value);
            });

        $dirList->shift();
        $this->rootDirectory = str($dirList->first())->lower()->toString();

        $dirList->each(function (Stringable $item) use (&$titled): void {
            if ($titled->startsWith($item)) {
                $titled = $titled->replace($item, '');
            }

            $noSpacesItem = $item->replace(' ', '');
            if ($titled->startsWith($noSpacesItem)) {
                $titled = $titled->replace($noSpacesItem, '');
            }

            $noNumbersItem = $item->replaceMatches('/\d+/', '')
                ->replace('  ', '')
                ->trim();

            if ($titled->startsWith($noNumbersItem)) {
                $titled = $titled->replace($noNumbersItem, '');
            }

            $noSpacesNoNumbersItem = $noSpacesItem->replaceMatches('/\d+/', '')
                ->replace('  ', '')
                ->trim();

            if (! $titled->startsWith($noSpacesNoNumbersItem)) {
                return;
            }

            $titled = $titled->replace($noSpacesNoNumbersItem, '');
        });

        $titled = $titled->replaceMatches('/^\d+\s*/', '')
            ->trim()
            ->ltrim('-')
            ->rtrim('-')
            ->trim();

        /** @var Stringable $item */
        foreach ($dirList->reverse() as $item) {
            $titled = $titled->prepend("{$item->toString()} - ");
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
        $this->title = mb_trim((string) $str);

        return $this;
    }

    private function changeWords(): self
    {
        $this->title = $this->replaceWords(
            $this->removeWords($this->title)
        );

        return $this;
    }

    private function getWordMatrix(): array
    {
        return [
            'brother' => $this->getBoyGeneric(),
            'bro' => $this->getBoyGeneric(),
            'hermano' => $this->getBoyGeneric(),
            'hermanastro' => $this->getBoyGeneric(),
            'sister' => $this->getGirlGeneric(),
            'sis' => $this->getGirlGeneric(),
            'hermana' => $this->getGirlGeneric(),
            'hermanastra' => $this->getGirlGeneric(),
            'padrastro' => $this->getBoyGeneric(),
            'father' => $this->getBoyGeneric(),
            'dad' => $this->getBoyGeneric(),
            'daddy' => $this->getBoyGeneric(),
            'papi' => $this->getBoyGeneric(),
            'son' => $this->getBoyGeneric(),
            's0n' => $this->getBoyGeneric(),
            'hijastro' => $this->getBoyGeneric(),
            'madrastra' => $this->getGirlGeneric(),
            'mother' => $this->getGirlGeneric(),
            'mom' => $this->getGirlGeneric(),
            'mommy' => $this->getGirlGeneric(),
            'mami' => $this->getGirlGeneric(),
            'daughter' => $this->getGirlGeneric(),
            'd@ughter' => $this->getGirlGeneric(),
            'hijastra' => $this->getGirlGeneric(),
            'aunt' => $this->getGirlGeneric(),
            'uncle' => $this->getBoyGeneric(),
            'niece' => $this->getGirlGeneric(),
            'nephew' => $this->getBoyGeneric(),
            'cousin' => $this->getGeneric(),
            'grandmother' => $this->getGirlGeneric(),
            'grandfather' => $this->getBoyGeneric(),
            'granny' => $this->getGirlGeneric(),
            'in-law' => $this->getGeneric(),
            'in law' => $this->getGeneric(),
            'family' => $this->getGroupGeneric(),
            'parents' => $this->getGroupGeneric(),
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
            'cop',
            'lawyer',
            'doctor',
            'widower',
            'lover',
            'acquaintance',
            'associate',
            'teammate',
            'neighbor',
            'mentor',
            'client',
            'patient',
            'contractor',
            'manager',
            'director',
            'editor',
            'researcher',
            'consultant',
            'technician',
            'accountant',
            'architect',
            'journalist',
            'photographer',
            'performer',
            'volunteer',
            'vampire',
            'assistant',
        ];
    }

    private function getGirlGeneric(): array
    {
        return array_merge(
            $this->getGeneric(),
            [
                'wife',
                'mistress',
                'girlfriend',
                'sister-in-law',
                'mother-in-law',
                'bride',
                'duchess',
                'governess',
                'heiress',
                'heroine',
                'maiden',
                'princess',
                'queen',
                'sorceress',
                'nun',
                'vicar‑wife',
                'hostess',
                'waitress',
                'comadre',
                'matriarch',
                'midwife',
                'empress',
                'baroness',
                'countess',
                'stewardess',
                'secretary',
            ]
        );
    }

    private function getBoyGeneric(): array
    {
        return array_merge(
            $this->getGeneric(),
            [
                'husband',
                'marido',
                'boyfriend',
                'brother-in-law',
                'father-in-law',
                'fireman',
                'waiter',
                'compadre',
                'groom',
                'duke',
                'governor',
                'heir',
                'hero',
                'bachelor',
                'knight',
                'squire',
                'baron',
                'count',
                'lord',
                'monarch',
                'priest',
                'pastor',
                'imam',
                'rabbi',
                'sheriff',
                'steward',
                'fire‑fighter',
                'bartender',
            ]
        );
    }

    private function getGroupGeneric(): array
    {
        return [
            'friends',
            'pals',
            'coworkers',
            'pupils',
            'students',
            'fans',
            'influencers',
            'strangers',
            'associates',
            'teammates',
            'neighbor',
            'clients',
            '',
        ];
    }

    private function removeStartingNumbers(): self
    {
        $pattern = '/\b(\d{2})\s+(\d{2})\s+(\d{2})\b/';
        if (in_array(preg_match($pattern, $this->title, $matches), [0, false], true)) {
            return $this;
        }

        $this->title = mb_trim(
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

        $this->title = mb_trim(
            str_replace($matches[0], '', $this->title)
        );

        return $this;
    }

    private function removeSpaces(): Stringable
    {
        $spaces = [
            '     ',
            '    ',
            '   ',
            '  ',
        ];

        return Str::of($this->title)
            ->replace(['(', ')'], ' - ')
            ->replace($spaces, ' ')
            ->replace('- - - -', '-')
            ->replace('- - -', '-')
            ->replace('- -', '-')
            ->replace('---', '-')
            ->replace('--', '-')
            ->replace('-', '- ')
            ->replace($spaces, ' ')
            ->rtrim('- ', '')
            ->rtrim('-', '')
            ->trim();
    }

    private function processTitleTags(string $filename): void
    {
        $this->extraTags = [];

        $titleTags = $this->prepareTitleTags();
        if (blank($titleTags)) {
            return;
        }

        $title = str($filename)->lower()->trim();

        foreach ($titleTags as $key => $tag) {
            if (! $title->contains($key)) {
                continue;
            }

            $this->extraTags[] = $tag;
        }
    }
}
