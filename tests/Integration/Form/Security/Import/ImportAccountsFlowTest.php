<?php

namespace App\Tests\Form\Security\Import;

use App\Form\Security\Import\ConfirmationType;
use App\Form\Security\Import\ImportAccountsFlow;
use App\Form\Security\Import\UploadCsvType;
use PHPUnit\Framework\TestCase;

class ImportAccountsFlowTest extends TestCase
{
    public function testLoadStepsConfig(): void
    {
        $flow = new ImportAccountsFlow();
        $reflection = new \ReflectionClass(ImportAccountsFlow::class);
        $method = $reflection->getMethod('loadStepsConfig');
        $method->setAccessible(true);
        $stepsConfig = $method->invoke($flow);

        self::assertIsArray($stepsConfig);
        self::assertCount(2, $stepsConfig);

        // Assert the first step
        self::assertArrayHasKey('label', $stepsConfig[0]);
        self::assertEquals('upload', $stepsConfig[0]['label']);
        self::assertArrayHasKey('form_type', $stepsConfig[0]);
        self::assertEquals(UploadCsvType::class, $stepsConfig[0]['form_type']);

        // Assert the second step
        self::assertArrayHasKey('label', $stepsConfig[1]);
        self::assertEquals('confirmation', $stepsConfig[1]['label']);
        self::assertArrayHasKey('form_type', $stepsConfig[1]);
        self::assertEquals(ConfirmationType::class, $stepsConfig[1]['form_type']);
    }
}
