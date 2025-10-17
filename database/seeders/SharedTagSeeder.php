<?php

namespace Database\Seeders;

use App\Models\SharedTagItem;
use App\Models\Tube\SharedTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SharedTagSeeder extends Seeder
{
    public function run(): void
    {
        $list = Config::array('content.shared_tags');
        if (blank($list)) {
            Log::error('No shared tags found');

            return;
        }

        foreach ($list as $name => $tags) {
            $sharedTag = SharedTag::updateOrCreate([
                'hash' => md5(mb_strtolower($name)),
            ], [
                'name' => $name,
                'active' => true,
            ]);

            foreach ($tags as $tag) {
                SharedTagItem::updateOrCreate([
                    'shared_tag_id' => $sharedTag->id,
                    'hash' => md5(mb_strtolower($tag)),
                ], [
                    'tag' => $tag,
                    'active' => true,
                ]);
            }
        }
    }
}
