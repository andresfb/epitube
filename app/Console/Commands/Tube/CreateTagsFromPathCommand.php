<?php

namespace App\Console\Commands\Tube;

use App\Models\Tube\Tag;
use App\Traits\TagsProcessor;
use Exception;
use FilesystemIterator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

class CreateTagsFromPathCommand extends Command
{
    use TagsProcessor;

    protected $signature = 'create:tags';

    protected $description = 'Create Tags using the folder name where the content is stored';

    public function handle(): void
    {
        try {
            clear();
            intro('Importing Tags');

            $directories = $this->getDirectoryLists(
                Config::string('content.data_path')
            );

            $sharedTags = $this->prepareSharedTags();
            $bandedTags = Config::array('content.banded_tags');

            foreach ($directories as $directory) {
                $tags = collect();

                str($directory)
                    ->trim()
                    ->lower()
                    ->replace("'", '')
                    ->replace('step', ' ')
                    ->replace('    ', ' ')
                    ->replace('   ', ' ')
                    ->replace('  ', ' ')
                    ->explode(' - ')
                    ->map(fn (string $text): string => mb_trim($text))
                    ->reject(function (string $text) use($bandedTags): bool {
                        return blank($text) || in_array($text, $bandedTags, true);
                    })
                    ->unique()
                    ->each(function (string $text) use ($sharedTags, &$tags): void {
                        $this->collectTags($text, $tags, $sharedTags);
                    });

                $tags->each(function (string $tag) {
                    if (Tag::findFromStringOfAnyType($tag)->isNotEmpty()) {
                        return;
                    }

                    Tag::create([
                        'name' => $tag,
                        'type' => 'main',
                    ]);

                    echo '.';
                });
            }

            $this->newLine();
        } catch (Exception $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }

    private function getDirectoryLists(string $basePath): array
    {
        $rootPath = rtrim($basePath, DIRECTORY_SEPARATOR);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $rootPath,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $dirs = [];
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir()) {
                // Get the directory name without the full path
                $dirs[] = ucwords($fileInfo->getFilename());
            }
        }

        sort($dirs);

        // Remove possible duplicates and re‑index
        return array_values(array_unique($dirs));
    }
}
