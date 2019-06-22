<?php

namespace App\Log;

use App\Entity\Log\Event as EventEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EventService
{
    private $em;

    private $auth;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage) {
        $this->em = $em;
        $this->auth = $tokenStorage->getToken()->getUser();
    }

    public function log(AbstractEvent $event) {
        $entity = $this->hydrate($event);
        
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function hydrate(AbstractEvent $event) {
        if ($event->getTime() !== null)
            throw new \InvalidArgumentException("Events populated from database cannot be hydrated again");
        
        $discr = get_class($event);

        $entity = $event->getEntity();
        $event->setEntity(null);

        $meta = serialize($event);

        $event = new EventEntity();
        $event
            ->setTime(new \DateTime)
            ->setDiscr($discr)
            ->setAuth($this->auth)
            ->setMeta($meta)
        ;

        if ($entity !== null) {
            $event
                ->setObjectId($entity->getPrimairy())
                ->setObjectType(get_class($entity))
            ;
        }

        return $event;
    }

    public function populate(EventEntity $entity) {

        $event = unserialize($entity->getMeta());
        
        $objectType = $entity->getObjectType();
        $objectId   = $entity->getObjectId();
        $em = $this->em;

        $objectClosure = function () use ($em, $objectType, $objectId) {
            return $em->find($objectType, $objectId);
        };

        $event
            ->setTime($entity->getTime())
            ->setAuth($entity->getAuth())
            ->setEntityCb($objectClosure)
        ;

        return $event;
    }

    public function populateAll(array $entities) {
        return array_map($this->populate, $entities);
    }

    public function findBy(?LoggableEntityInterface $entity = null, ?string $type = null, array $options = array()) {

        if ($entity !== null) {
            $options['objectId'] = $entity->getPrimairy();
            $options['objectType'] = get_class($entity);
        }

        if ($type !== null) {
            $options['discr'] = $type;
        }

        $found = $this->em->getRepository(EventEntity::class)->findBy($options);

        return $this->populateAll($found);
    }

    public function findOneBy(?LoggableEntityInterface $entity = null, ?string $type = null, array $options = array()) {
        
        if ($entity !== null) {
            $options['objectId'] = $entity->getPrimairy();
            $options['objectType'] = get_class($entity);
        }

        if ($type !== null) {
            $options['discr'] = $type;
        }

        $found = $this->em->getRepository(EventEntity::class)->findOneBy($options);

        return $this->populate($found);
    }
}