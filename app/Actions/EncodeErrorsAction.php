<?php

declare(strict_types=1);

namespace App\Actions;

use App\Dtos\Tube\EncodeErrorItem;
use App\Models\Tube\Content;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class EncodeErrorsAction
{
    public function handle(): Collection
    {
        return DB::table('notifications')
            ->where('notifiable_type', Content::class)
            ->where('read_at', null)
            ->get()
            ->map(function ($notification) {
                $data = $notification->data;
                $data['content_id'] = $notification->notification_id;

                return EncodeErrorItem::from($data);
            });
    }
}
