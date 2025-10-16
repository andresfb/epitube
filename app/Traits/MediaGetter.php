<?php

namespace App\Traits;

use App\Libraries\Tube\MediaNamesLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\Media;

trait MediaGetter
{
    protected function getMedia(Content $content): Media
    {
        $collection = MediaNamesLibrary::videos();
        if ($content->hasMedia(MediaNamesLibrary::transcoded())) {
            $collection = MediaNamesLibrary::transcoded();
        }

        return Media::where('model_id', $content->id)
            ->where('model_type', Content::class)
            ->where('collection_name', $collection)
            ->firstOrFail();
    }
}
