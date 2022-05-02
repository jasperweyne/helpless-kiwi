<?php

namespace Tests\Unit\GraphQL\Types;

use App\GraphQL\Types\DatetimeScalar;
use GraphQL\Language\AST\StringValueNode;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class DatetimeScalarTest.
 *
 * @covers \App\GraphQL\Types\DatetimeScalar
 */
class DatetimeScalarTest extends KernelTestCase
{
    public function testSerialize()
    {
        $expected = '2019-10-12T07:20:50+00:00';
        $value = new \DateTime($expected);
        $result = DatetimeScalar::serialize($value);
        self::assertEquals($expected, $result);
    }

    public function testParseValue()
    {
        $value = '2019-10-12T07:20:50.52+00:00';
        $expected = new \DateTime($value);
        $result = DatetimeScalar::parseValue($value);
        self::assertEquals($expected, $result);
    }

    public function testParseLiteral()
    {
        $value = '2019-10-12T07:20:50.52+00:00';
        $node = new StringValueNode([]);
        $node->value = $value;
        $expected = new \DateTime($value);
        $result = DatetimeScalar::parseLiteral($node);
        self::assertEquals($expected, $result);
    }
}
