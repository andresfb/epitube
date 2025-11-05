<?php

declare(strict_types=1);

namespace App\Services\Tube;

use App\Mail\EncodeErrorsMail;
use App\Models\Tube\Content;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

final class CheckEncodingErrorsService
{
    public function execute(): void
    {
        $pendingNotifications = DB::table('notifications')
            ->where('notifiable_type', Content::class)
            ->whereNull('read_at')
            ->count();

        if ($pendingNotifications <= 0) {
            return;
        }

        Mail::to(
            users: Config::string('constants.admin_email'),
        )->send(
            mailable: new EncodeErrorsMail($pendingNotifications)
        );
    }
}
