<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Models\Tube\Content;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class MarkNotificationReadAction
{
    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        DB::transaction(static function (): void {
            DB::table('notifications')
                ->where('notifiable_type', Content::class)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        });
    }
}
