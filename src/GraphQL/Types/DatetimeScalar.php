<?php

namespace App\GraphQL\Types;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Scalar(name="DateTimeScalar")
 */
class DatetimeScalar
{
    /**
     * @return string
     */
    public static function serialize(\DateTime $value)
    {
        return $value->format(\DateTimeInterface::RFC3339);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public static function parseValue($value)
    {
        return new \DateTime($value);
    }

    /**
     * @param Node $valueNode
     *
     * @return string
     */
    public static function parseLiteral($valueNode)
    {
        return new \DateTime($valueNode->value);
    }
}
