<?php

declare(strict_types=1);

namespace Modules\JellyfinApi\Traits\JellyfinAPI;

use Exception;
use Psr\Http\Message\StreamInterface;

trait Users
{
    /**
     * @throws Exception
     */
    public function getUsers(): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = 'Users';

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getUser(string $userId): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Users/$userId";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function deleteUser(string $userId): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Users/$userId";

        $this->verb = 'delete';

        return $this->doJellyfinRequest(false);
    }

    /**
     * @throws Exception
     */
    public function updateUser(string $userId, array $data): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Users/$userId";

        $this->options['json'] = $data;

        $this->verb = 'post';

        return $this->doJellyfinRequest(false);
    }

    /**
     * @throws Exception
     */
    public function updateUserConfiguration(string $userId, array $data): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Users/$userId/Configuration";

        $this->options['json'] = $data;

        $this->verb = 'post';

        return $this->doJellyfinRequest(false);
    }

    /**
     * @throws Exception
     */
    public function updateUserPolicy(string $userId, array $data): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Users/$userId/Policy";

        $this->options['json'] = $data;

        $this->verb = 'post';

        return $this->doJellyfinRequest(false);
    }

    /**
     * @throws Exception
     */
    public function authenticateUser(string $userId, string $password): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Users/$userId/Authenticate";

        $this->setRequestQuery('pw', $password);

        $this->verb = 'post';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function updateUserEasyPassword(string $userId, array $data): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Users/$userId/EasyPassword";

        $this->options['json'] = $data;

        $this->verb = 'post';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function updateUserPassword(string $userId, array $data): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Users/$userId/Password";

        $this->options['json'] = $data;

        $this->verb = 'post';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function authenticateUserByName(array $data): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = 'Users/AuthenticateByName';

        $this->options['json'] = $data;

        $this->verb = 'post';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function authenticateUserWithQuickConnect(string $secret): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = 'Users/AuthenticateWithQuickConnect';

        $this->options['json'] = [
            'secret' => $secret,
        ];

        $this->verb = 'post';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function redeemsForgotPasswordPin(string $pin): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = 'Users/ForgotPassword/Pin';

        $this->options['json'] = [
            'pin' => $pin,
        ];

        $this->verb = 'post';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getUserBasedOnAuthToken(): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = 'Users/Me';

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function createUser(string $name, string $password): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = 'Users/New';

        $this->options['json'] = [
            'name' => $name,
            'password' => $password,
        ];

        $this->verb = 'post';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getListOfPubliclyVisibleUsers(): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = 'Users/Public';

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }
}
