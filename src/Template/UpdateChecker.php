<?php

namespace App\Template;

use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UpdateChecker
{
    private HttpClientInterface $client;

    public function __construct(
        KernelInterface $kernel,
        HttpClientInterface $httpClient,
    ) {
        $this->client = new CachingHttpClient(
            $httpClient,
            new Store("{$kernel->getCacheDir()}/releases")
        );
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
