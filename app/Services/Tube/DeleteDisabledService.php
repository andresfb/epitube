<?php

namespace App\Services\Tube;

use App\Libraries\Tube\MediaNamesLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\Feed;
use App\Models\Tube\Rejected;
use App\Traits\Screenable;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\confirm;

class DeleteDisabledService
{
    use Screenable;

    public function execute(): void
    {
        try {
            $this->warning('Started deleting disabled content process...');
            $contents = Content::query()
                ->withTrashed()
                ->whereActive(false)
                ->get();

            if ($contents->isEmpty()) {
                $this->warning('No disabled contents found');

                return;
            }

            if ($this->toScreen && !confirm("Deleting {$contents->count()} disabled contents. Continue?", false)) {
                return;
            }

            $contents->each(function (Content $content) {
                $this->info("Staring deleting Content Id: $content->id");

                [$duration, $height, $width] = $this->getVideoInfo($content);

                $this->notice('Deleting Media records...');
                foreach (MediaNamesLibrary::all() as $mediaName) {
                    $this->notice("Looking for media collection $mediaName");

                    $medias = $content->getMedia($mediaName);
                    if ($medias->isEmpty()) {
                        $this->warning('None found');

                        continue;
                    }

                    $this->notice("Deleting media items for $mediaName");
                    foreach ($medias as $media) {
                        $this->character('.');
                        $media->forceDelete();
                    }
                    $this->character("\n");
                }
                $this->notice('Done deleting media records');

                $this->notice('Deleting Tags...');
                $tags = $content->tags->pluck('name')->toArray();
                $content->detachTags($tags);
                $this->notice('Done deleting tags');

                $this->notice('Deleting related Contents...');
                DB::table('content_related')
                    ->where('content_id', $content->id)
                    ->orWhere('related_content_id', $content->id)
                    ->delete();
                $this->notice('Done deleting related Contents');

                $this->notice('Adding Content to the Rejected table...');
                Rejected::updateOrCreate([
                    'item_id' => $content->item_id
                ], [
                    'og_path' => $content->og_path,
                    'reason' => "Deleted disabled content $content->id | $content->slug",
                    'duration' => $duration,
                    'height' => $height,
                    'width' => $width,
                ]);
                $this->notice('Done deleting related Contents');

                $this->notice('Deleting Feed...');
                Feed::query()
                    ->where('slug', $content->slug)
                    ->forceDelete();
                $this->notice('Done deleting related Feed');

                $this->notice('Deleting Content...');
                $content->forceDelete();
                $this->info('Done deleting Contents');

                $this->character("\n\n");

                if ($this->toScreen) {
                    sleep(2);
                }
            });
        } finally {
            $this->warning('Finished deleting disabled content process');
        }
    }

    private function getVideoInfo(Content $content): array
    {
        $collection = MediaNamesLibrary::videos();
        if ($content->hasMedia(MediaNamesLibrary::transcoded())) {
            $collection = MediaNamesLibrary::transcoded();
        }

        $media = $content->getMedia($collection)->firstOrFail();

        return [
            (int)$media->getCustomProperty('duration', 0),
            (int)$media->getCustomProperty('height', 0),
            (int)$media->getCustomProperty('width', 0),
        ];
    }
}
