<?php

namespace App\Http\Resources\Api\V1;

use App\Factories\FeedItemFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;
use Override;

class FeedDetailResource extends JsonApiResource
{
    #[Override]
    public function toAttributes(Request $request): array
    {
        return FeedItemFactory::forDetailArray($this->resource);
    }
}
