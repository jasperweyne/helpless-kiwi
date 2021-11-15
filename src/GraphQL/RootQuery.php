<?php

namespace App\GraphQL;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Type
 */
class RootQuery
{
    /**
     * @GQL\Field(name="ping", type="String!")
     */
    public function ping()
    {
        return 'Hello world!';
    }
}
