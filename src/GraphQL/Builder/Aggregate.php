<?php

namespace App\GraphQL\Builder;

use Overblog\GraphQLBundle\Definition\Builder\MappingInterface;

/**
 * FieldsBuilder for aggregate results of n-to-many relations.
 */
class Aggregate implements MappingInterface
{
    public function toMappingDefinition(array $config): array
    {
        $name = $config['name'];
        $property = $config['property'] ?? $name;

        $fields = [
            $name.'_aggr' => [
                'description' => "Return the result of an aggregate operation on $name",
                'type' => 'Int!',
                'args' => [
                    'fn' => [
                        'type' => 'AggregateFunc',
                    ],
                ],
                'resolve' => "@=call(args['fn'], [value.$property])",
            ],
        ];

        $types = [
            'AggregateFunc' => [
                'type' => 'enum',
                'config' => [
                    'description' => 'An aggregate function',
                    'values' => [
                        'COUNT' => [
                            'value' => 'count',
                            'description' => 'Compute the amount of items.',
                        ],
                        'MIN' => [
                            'value' => 'min',
                            'description' => 'Get the minimum value.',
                        ],
                        'MAX' => [
                            'value' => 'max',
                            'description' => 'Get the maximum value.',
                        ],
                    ],
                ],
            ],
        ];

        return ['fields' => $fields, 'types' => $types];
    }
}
