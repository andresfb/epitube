<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class EncodeErrorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $caller,
        private readonly int $mediaId,
        private readonly string $ogPath,
        private readonly string $error,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return $this->toArray($notifiable);
    }

    public function toArray($notifiable): array
    {
        return [
            'caller' => $this->caller,
            'media_id' => $this->mediaId,
            'og_path' => $this->ogPath,
            'error' => $this->error,
        ];
    }
}
