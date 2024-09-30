<?php

namespace App\Tests\Form\Security\Import;

use App\Form\Activity\ActivityCreationFlow;
use App\Form\Activity\ActivityLocationType;
use App\Form\Activity\ActivityNewType;
use PHPUnit\Framework\TestCase;

class ActivityCreationFlowTest extends TestCase
{
    public function testLoadStepsConfig(): void
    {
        $flow = new ActivityCreationFlow();
        $reflection = new \ReflectionClass(ActivityCreationFlow::class);
        $method = $reflection->getMethod('loadStepsConfig');
        $method->setAccessible(true);
        $stepsConfig = $method->invoke($flow);

        self::assertIsArray($stepsConfig);
        self::assertCount(2, $stepsConfig);

        // Assert the first step
        self::assertArrayHasKey('label', $stepsConfig[0]);
        self::assertEquals('create activity', $stepsConfig[0]['label']);
        self::assertArrayHasKey('form_type', $stepsConfig[0]);
        self::assertEquals(ActivityNewType::class, $stepsConfig[0]['form_type']);

        // Assert the second step
        self::assertArrayHasKey('label', $stepsConfig[1]);
        self::assertEquals('create location', $stepsConfig[1]['label']);
        self::assertArrayHasKey('form_type', $stepsConfig[1]);
        self::assertEquals(ActivityLocationType::class, $stepsConfig[1]['form_type']);
    }
}
