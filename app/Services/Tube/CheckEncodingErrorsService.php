<?php

namespace App\Services\Tube;

use App\Mail\EncodeErrorsMail;
use App\Models\Tube\Content;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CheckEncodingErrorsService
{
    public function execute(): void
    {
        $pendingNotifications = DB::table('notifications')
            ->where('notifiable_type', Content::class)
            ->where('read_at', null)
            ->count();

        if (blank($pendingNotifications)) {
            return;
        }

        Mail::to(
            users: Config::get('constants.admin_email'),
        )->send(
            mailable: new EncodeErrorsMail($pendingNotifications)
        );
    }
}
