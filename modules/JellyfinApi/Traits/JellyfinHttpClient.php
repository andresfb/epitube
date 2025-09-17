<?php

namespace Modules\JellyfinApi\Traits;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Utils;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Throwable;

trait JellyfinHttpClient
{
    private HttpClient $client;

    private array $httpClientConfig;

    protected string $apiBaseUrl;

    private string $apiUrl;

    private string $apiEndPoint;

    private string $httpBodyParam;

    private bool $validateSSL;

    protected string $verb = 'get';

    protected function setCurlConstants(): void
    {
        $constants = [
            'CURLOPT_SSLVERSION'        => 32,
            'CURL_SSLVERSION_TLSv1_2'   => 6,
            'CURLOPT_SSL_VERIFYPEER'    => 64,
            'CURLOPT_SSLCERT'           => 10025,
        ];

        foreach ($constants as $key => $item) {
            $this->defineCurlConstant($key, $item);
        }
    }

    protected function defineCurlConstant(string $key, string $value): bool|string
    {
        return defined($key) ? true : define($key, $value);
    }

    public function setClient(HttpClient $client = null): void
    {
        if ($client instanceof HttpClient) {
            $this->client = $client;

            return;
        }

        $this->client = new HttpClient([
            'curl' => $this->httpClientConfig,
        ]);
    }

    protected function setHttpClientConfiguration(): void
    {
        $this->setCurlConstants();

        $this->httpClientConfig = [
            CURLOPT_SSLVERSION     => CURL_SSLVERSION_TLSv1_2,
            CURLOPT_SSL_VERIFYPEER => $this->validateSSL,
        ];

        // Set default values.
        $this->setDefaultValues();

        // Initialize Http Client
        $this->setClient();
    }

    /**
     * Set default values for configuration.
     *
     * @return void
     */
    private function setDefaultValues(): void
    {
        $validateSSL = empty($this->validateSSL) ? true : $this->validateSSL;
        $this->validateSSL = $validateSSL;

        $this->apiBaseUrl = $this->config['server_api_url'];
    }

    /**
     * @throws Throwable
     */
    private function makeHttpRequest(): StreamInterface
    {
        return $this->client->{$this->verb}(
            $this->apiUrl,
            $this->options
        )->getBody();
    }

    /**
     * @throws Throwable
     */
    private function doJellyfinRequest(bool $decode = true): StreamInterface|array|string
    {
        try {
            $this->apiUrl = collect([$this->apiBaseUrl, $this->apiEndPoint])->implode('/');

            // Perform Jellyfin HTTP API request.
            $response = $this->makeHttpRequest();

            $data = $response->getContents();

            return ($decode === false) ? $data : Utils::jsonDecode($data, true);
        } catch (RuntimeException $t) {
            return $t->getMessage();
        }
    }
}
