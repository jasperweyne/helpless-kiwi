<?php

namespace App\Log;

class AbstractEvent
{
    private $time;

    private $auth;

    private $entity;

    private $entityCb;

    private $entityType = '';

    public function getTime()
    {
        if (null === $this->time) {
            throw new \RuntimeException('Can only be called after the event has been retrieved from the database');
        }

        return $this->time;
    }

    public function getAuth()
    {
        if (null === $this->time) {
            throw new \RuntimeException('Can only be called after the event has been retrieved from the database');
        }

        return $this->auth;
    }

    public function getEntity()
    {
        if (null !== $this->entityCb) {
            $this->entity = ($this->entityCb)();
            $this->entityCb = null;
        }

        return $this->entity;
    }

    public function setEntity($entity)
    {
        $this->entityCb = null;
        $this->entity = $entity;
        $this->entityType = \get_class($entity);

        return $this;
    }

    public function getEntityType()
    {
        return $this->entityType;
    }

    public function setEntityType($type)
    {
        $this->entityType = $type;

        return $this;
    }

    public function getTitle()
    {
        return 'Unknown event type';
    }
}
