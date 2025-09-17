<?php

namespace Modules\JellyfinApi\Services;

use Modules\JellyfinApi\Traits\JellyfinRequest;

class JellyfinService
{
    use JellyfinRequest;

    public function __construct(array $config = [])
    {
        // Setting Jellyfin API Credentials
        $this->setConfig($config);

        $this->httpBodyParam = 'form_params';

        $this->setRequestHeader('Accept', 'application/json');
        $this->setRequestHeader('Content-Type', 'application/json');
    }

    protected function setOptions(array $credentials): void
    {
        // Setting API Endpoint
        $this->config['server_api_url'] = $credentials['server_url'];
    }
}
