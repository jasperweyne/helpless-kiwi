<?php

namespace App\Log;

use App\Entity\Security\LocalAccount;

class AbstractEvent
{
    /**
     * @var ?\DateTimeInterface
     */
    private $time;

    /**
     * @var ?LocalAccount
     */
    private $person;

    /**
     * @var ?object
     */
    private $entity;

    /**
     * @var ?callable
     */
    private $entityCb;

    /**
     * @var class-string|''
     */
    private $entityType = '';

    public function getTime(): \DateTimeInterface
    {
        if (null === $this->time) {
            throw new \RuntimeException('Can only be called after the event has been retrieved from the database');
        }

        return $this->time;
    }

    public function getPerson(): LocalAccount
    {
        if (null === $this->time) {
            throw new \RuntimeException('Can only be called after the event has been retrieved from the database');
        }

        return $this->person;
    }

    public function getEntity(): ?object
    {
        if (null !== $this->entityCb) {
            $this->entity = ($this->entityCb)();
            $this->entityCb = null;
        }

        return $this->entity;
    }

    public function setEntity(object $entity): self
    {
        $this->entityCb = null;
        $this->entity = $entity;
        $this->entityType = \get_class($entity);

        return $this;
    }

    /**
     * @return class-string|''
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @param class-string|'' $type
     */
    public function setEntityType(string $type): self
    {
        $this->entityType = $type;

        return $this;
    }

    public function getTitle(): string
    {
        return 'Unknown event type';
    }
}
