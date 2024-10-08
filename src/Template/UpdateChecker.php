<?php

namespace App\Template;

use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UpdateChecker
{
    private HttpClientInterface $client;

    public function __construct(
        KernelInterface $kernel,
    ) {
        $this->client = new CachingHttpClient(
            HttpClient::create(),
            new Store("{$kernel->getCacheDir()}/releases")
        );
    }

    public function setHttpClient(HttpClientInterface $httpClient): self
    {
        $this->client = $httpClient;

        return $this;
    }

    public function newestVersion(): string
    {
        $response = $this->client->request(
            'GET',
            'https://api.github.com/repos/jasperweyne/helpless-kiwi/releases/latest'
        );
        if (403 == $response->getStatusCode()) {
            return $_ENV['INSTALLED_VERSION'] ?? '';
        }
        $responseArray = $response->toArray();

        return $responseArray['tag_name'];
    }
}
