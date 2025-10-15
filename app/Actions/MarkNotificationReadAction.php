<?php

declare(strict_types=1);

namespace App\Actions;

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
                ->where('read_at', null)
                ->update(['read_at' => now()]);
        });
    }
}
