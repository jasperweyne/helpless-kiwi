<?php

namespace App\Log;

use App\Log\LoggableEntityInterface;

class EntityNewEvent extends AbstractEvent
{
    public function __construct(LoggableEntityInterface $entity)
    {
        $this->setEntity($entity);
    }
}
