<?php

namespace Modules\JellyfinApi\Traits\JellyfinAPI;

use Psr\Http\Message\StreamInterface;
use Throwable;

trait Users
{
    /**
     * @throws Throwable
     */
    public function getUsers(): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Users";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Throwable
     */
    public function getUser(string $userId): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Users/$userId";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Throwable
     */
    public function deleteUser(string $userId): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Users/$userId";

        $this->verb = 'delete';

        return $this->doJellyfinRequest(false);
    }

    /**
     * @throws Throwable
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
     * @throws Throwable
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
     * @throws Throwable
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
     * @throws Throwable
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
     * @throws Throwable
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
     * Updates a user's password.
     *
     * @param string $userId
     * @param array $data
     * @return array|StreamInterface|string
     * @throws Throwable
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
     * @throws Throwable
     */
    public function authenticateUserByName(array $data): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Users/AuthenticateByName";

        $this->options['json'] = $data;

        $this->verb = 'post';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Throwable
     */
    public function authenticateUserWithQuickConnect(string $secret): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Users/AuthenticateWithQuickConnect";

        $this->options['json'] = [
            'secret' => $secret
        ];

        $this->verb = 'post';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Throwable
     */
    public function redeemsForgotPasswordPin(string $pin): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Users/ForgotPassword/Pin";

        $this->options['json'] = [
            'pin' => $pin
        ];

        $this->verb = 'post';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Throwable
     */
    public function getUserBasedOnAuthToken(): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Users/Me";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Throwable
     */
    public function createUser(string $name, string $password): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Users/New";

        $this->options['json'] = [
            'name' => $name,
            'password' => $password
        ];

        $this->verb = 'post';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Throwable
     */
    public function getListOfPubliclyVisibleUsers(): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Users/Public";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }
}
