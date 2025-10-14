<?php

namespace App\Console\Commands\Tube;

use Exception;
use FilesystemIterator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Spatie\Tags\Tag;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

class CreateTagsFromPathCommand extends Command
{
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

            foreach ($directories as $directory) {
                Tag::updateOrCreate([
                    'name' => $directory,
                ], [
                    'type' => 'main',
                ]);

                echo '.';
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
