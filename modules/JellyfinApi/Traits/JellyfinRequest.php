<?php

namespace Modules\JellyfinApi\Traits;

use Exception;
use RuntimeException;

trait JellyfinRequest
{
    use JellyfinHttpClient;
    use JellyfinAPI;

    public string $token;

    private array $config;

    protected array $options = [];

    /**
     * @throws Exception
     */
    public function setApiCredentials(array $credentials): void
    {
        if (empty($credentials)) {
            $this->throwConfigurationException();
        }

        // Set API configuration for the Jellyfin provider
        $this->setApiProviderConfiguration($credentials);

        // Set Http Client configuration.
        $this->setHttpClientConfiguration();
    }

    /**
     * @throws Exception
     */
    private function setApiProviderConfiguration(array $credentials): void
    {
        // Setting Jellyfin API Credentials
        collect($credentials)->map(function ($value, $key) {
            $this->config[$key] = $value;
        });

        $this->validateSSL = $this->config['validate_ssl'];

        $this->setRequestHeader('X-Emby-Token', $this->config['token']);
        $this->setRequestHeader('X-Application', $this->config['application'] ?: 'Laravel Jellyfin / v1.0');
        $this->setRequestHeader(
            'X-Emby-Authorization',
            'MediaBrowser Client="'
            . $this->config['application']
            . ' CLI", Device="'
            . $this->config['application']
            . '-CLI", DeviceId="None", Version="'
            . $this->config['version']
            . '"'
        );

        $this->setOptions($this->config);
    }

    public function setRequestHeader(string $key, string $value): self
    {
        $this->options['headers'][$key] = $value;

        return $this;
    }

    public function setArrayRequestHeader(array $value): self
    {
        $this->options['headers'] = array_merge_recursive($this->options['headers'], $value);

        return $this;
    }

    public function removeRequestHeader(string $key): self
    {
        unset($this->options['headers'][$key]);

        return $this;
    }

    /**
     * @throws RuntimeException
     */
    public function getRequestHeader(string $key): string
    {
        if (isset($this->options['headers'][$key])) {
            return $this->options['headers'][$key];
        }

        throw new RuntimeException('Options header is not set.');
    }

    public function setRequestsQuery(array $options): self
    {
        foreach ($options as $key => $value) {
            $this->options['query'][$key] = $value;
        }

        return $this;
    }

    public function setRequestQuery(string $key, string $value): self
    {
        $this->options['query'][$key] = $value;

        return $this;
    }

    /**
     * @throws RuntimeException
     */
    public function getRequestQuery(string $key): string
    {
        if (isset($this->options['query'][$key])) {
            return $this->options['query'][$key];
        }

        throw new RuntimeException('Options query is not set.');
    }

    /**
     * @throws Exception
     */
    private function setConfig(array $config): void
    {
        $apiConfig = function_exists('config') && ! empty(config('jellyfin')) ? config('jellyfin') : $config;

        // Set Api Credentials
        $this->setApiCredentials($apiConfig);
    }

    /**
     * @throws RuntimeException
     */
    private function throwConfigurationException(): void
    {
        throw new RuntimeException(
            'Invalid configuration provided. Please provide valid configuration for Jellyfin API.'
        );
    }
}
