<?php

namespace App\Log;

abstract class AbstractEvent
{
    private $time;

    private $auth;

    private $entity;

    private $entityCb;

    public function getTime()
    {
        if (null === $this->time) {
            throw new \RuntimeException('Can only be called after the event has been retrieved from the database');
        }

        return $this->time;
    }

    public function getAuth()
    {
        if (null === $this->auth) {
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

        return $this;
    }
}
