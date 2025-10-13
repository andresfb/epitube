<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Tube\Rejected;

final readonly class HasRejectedAction
{
    /**
     * Execute the action.
     */
    public function handle(): bool
    {
        return Rejected::query()
            ->whereDate('created_at', '>', now()->subDay())
            ->exists();
    }
}
