<?php

namespace Modules\JellyfinApi\Traits\JellyfinAPI;

use Psr\Http\Message\StreamInterface;
use Exception;

trait Items
{
    /**
     * @throws Exception
     */
    public function getItems(): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Items";

        $this->setRequestsQuery([
            'recursive' => 'true',
            'includeItemTypes' => 'Movie',
            'fields' => 'Path,Width,Height',
        ]);

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }
}
