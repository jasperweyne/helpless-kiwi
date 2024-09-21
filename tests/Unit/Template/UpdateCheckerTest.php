<?php

namespace Tests\Unit\Template;

use App\Template\UpdateChecker;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Class UpdateChecker.
 *
 * @author A-Daneel
 *
 * @covers \App\Template\UpdateChecker
 */
class UpdateCheckerTest extends KernelTestCase
{
    public function testNewestVersion(): void
    {
        $kernel = self::bootKernel();

        $expectedVersion = '1993-06-23';
        $expectedResponseData = ['tag_name' => $expectedVersion];
        $mockResponseJson = json_encode($expectedResponseData, JSON_THROW_ON_ERROR);

        $mockResponse = new MockResponse($mockResponseJson, [
            'http_code' => 200,
            'response_headers' => ['Content-Type: application/json'],
        ]);

        $httpClient = new MockHttpClient($mockResponse);

        $updateChecker = new UpdateChecker($kernel);
        $newestVersion = $updateChecker->setHttpClient($httpClient)->newestVersion();
        self::assertEquals($expectedVersion, $newestVersion);
    }

    public function testNewestVersionRateLimited(): void
    {
        $kernel = self::bootKernel();

        $expectedVersion = '';
        $mockResponseJson = json_encode([], JSON_THROW_ON_ERROR);

        $mockResponse = new MockResponse($mockResponseJson, [
            'http_code' => 403,
            'response_headers' => ['Content-Type: application/json'],
        ]);

        $httpClient = new MockHttpClient($mockResponse);

        $updateChecker = new UpdateChecker($kernel);
        $newestVersion = $updateChecker->setHttpClient($httpClient)->newestVersion();
        self::assertEquals($expectedVersion, $newestVersion);
    }
}
