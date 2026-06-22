<?php

namespace App\Http\Resources\Api\V1;

use App\Factories\FeedItemFactory;
use App\Models\Tube\Feed;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;
use Override;

/** @mixin Feed */
class FeedListResource extends JsonApiResource
{
    #[Override]
    public function toAttributes(Request $request): array
    {
        return FeedItemFactory::forListingArray($this->resource);
    }
}
