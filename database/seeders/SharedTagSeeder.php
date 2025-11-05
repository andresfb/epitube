<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tube\SharedTag;
use App\Models\Tube\SharedTagItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

final class SharedTagSeeder extends Seeder
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
