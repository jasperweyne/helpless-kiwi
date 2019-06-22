<?php

namespace App\Log;

use App\Entity\Log\Event;

abstract class AbstractEvent
{
    private $time;

    private $auth;

    private $entity;
    
    private $entityCb;

    public function getTime() {
        if ($this->time === null)
            throw new \RuntimeException("Can only be called after the event has been retrieved from the database");

        return $this->time;
    }

    public function getAuth() {
        if ($this->auth === null)
            throw new \RuntimeException("Can only be called after the event has been retrieved from the database");

        return $this->auth;
    }

    public function getEntity() {
        if ($this->entityCb !== null) {
            $this->entity = ($this->entityCb)();
            $this->entityCb = null;
        }

        return $this->entity;
    }

    public function setEntity($entity) {
        $this->entityCb = null;
        $this->entity = $entity;

        return $this;
    }

    public function migrateMetadata(array $meta) {
        return $meta;
    }
}