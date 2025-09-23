<?php

namespace Modules\JellyfinApi\Traits\JellyfinAPI;

use Psr\Http\Message\StreamInterface;
use Exception;

trait System
{
    /**
     * @throws Exception
     */
    public function getSystemServerInformations(): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "System/Info";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getSystemRequestEndpointInformations(): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "System/Endpoint";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getSystemPublicInformations(): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "System/Info/Public";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getSystemLogFiles(): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "System/Logs";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getSystemLogFile(string $logName): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "System/Logs/Log";

        $this->setRequestQuery('name', $logName);

        $this->verb = 'get';

        return $this->doJellyfinRequest(false);
    }

    /**
     * @throws Exception
     */
    public function pingSystem(): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "System/Ping";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function postPingSystem(): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "System/Ping";

        $this->verb = 'post';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function restartApplication(): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "System/Restart";

        $this->verb = 'post';

        return $this->doJellyfinRequest(false);
    }

    /**
     * @throws Exception
     */
    public function shutdownApplication(): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "System/Shutdown";

        $this->verb = 'post';

        return $this->doJellyfinRequest(false);
    }
}
