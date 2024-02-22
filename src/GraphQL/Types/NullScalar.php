<?php

namespace App\GraphQL\Types;

use Overblog\GraphQLBundle\Annotation as GQL;

#[GQL\Scalar(name: 'Null')]
class NullScalar
{
    /**
     * @return string
     */
    public static function serialize($value)
    {
        return '';
    }

    public static function parseValue($value)
    {
        return null;
    }

    /**
     * @param \GraphQL\Language\AST\Node $valueNode
     *
     * @return string
     */
    public static function parseLiteral($valueNode)
    {
        return '';
    }
}
