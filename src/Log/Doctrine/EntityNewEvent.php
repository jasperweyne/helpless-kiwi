<?php

namespace App\Log\Doctrine;

use App\Log\AbstractEvent;
use App\Log\LoggableEntityInterface;

class EntityNewEvent extends AbstractEvent
{
    public function __construct(LoggableEntityInterface $entity)
    {
        $this->setEntity($entity);
    }
}
