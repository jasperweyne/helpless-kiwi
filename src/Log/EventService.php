<?php

namespace App\Log;

use App\Entity\Log\Event as EventEntity;
use App\Log\ReflectionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EventService
{
    private $em;

    private $auth;

    private $refl;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage, ReflectionService $refl) {
        $this->em = $em;
        $this->auth = $tokenStorage->getToken()->getUser();
        $this->refl = $refl;
    }

    public function log(AbstractEvent $event) {
        $entity = $this->hydrate($event);
        
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function hydrate(AbstractEvent $event) {
        $meta = array();
        $rootProperties = $this->refl->getAllProperties(AbstractEvent::class);
        foreach ($this->refl->getAllProperties(get_class($event)) as $name => $property) {
            if (in_array($property, $rootProperties))
                continue;
            
            $meta[$name] = $property->getValue($event);
        }

        $entity = new EventEntity();
        $entity
            ->setTime(new \DateTime())
            ->setDiscr(get_class($event))
            ->setAuth($this->auth)
            ->setMeta(serialize($meta))
        ;

        $object = $event->getEntity();
        if ($object !== null) {
            $entity
                ->setObjectId($this->getIdentifier($object))
                ->setObjectType($this->getClassName($object))
            ;
        }

        return $entity;
    }

    public function populate(EventEntity $entity) {

        $reflFields = $this->refl->getAllProperties($entity->getDiscr());
        
        $objectType = $entity->getObjectType();
        $objectId   = $entity->getObjectId();
        $em         = $this->em;

        $objectClosure = function () use ($em, $objectType, $objectId) {
            return $em->find($objectType, $objectId);
        };

        $fields = unserialize($entity->getMeta());
        $fields['time']     = $entity->getTime();
        $fields['auth']     = $entity->getAuth();
        $fields['entityCb'] = $objectClosure;

        return $this->refl->instantiate($entity->getDiscr(), $fields);
    }

    public function populateAll(array $entities) {
        return array_map($this->populate, $entities);
    }

    public function findBy($entity = null, string $type = '', array $options = array()) {

        if ($entity !== null) {
            $options['objectId'] = $this->getIdentifier($entity);
            $options['objectType'] = get_class($entity);
        }

        if ($type !== '') {
            $options['discr'] = $type;
        }

        $found = $this->em->getRepository(EventEntity::class)->findBy($options);

        return $this->populateAll($found);
    }

    public function findOneBy($entity = null, string $type = '', array $options = array()) {
        
        if ($entity !== null) {
            $options['objectId'] = $this->getIdentifier($entity);
            $options['objectType'] = get_class($entity);
        }

        if ($type !== '') {
            $options['discr'] = $type;
        }

        $found = $this->em->getRepository(EventEntity::class)->findOneBy($options);

        return $this->populate($found);
    }

    public function getIdentifier($entity) {
        $className = $this->getClassName($entity);
        $identifier = $this->em->getClassMetadata($className)->getSingleIdentifierFieldName();
        $refl = $this->refl->getAccessibleProperty($className, $identifier);

        return $refl->getValue($entity);
    }

    public function getClassName($entity) {
        return $this->em->getClassMetadata(get_class($entity))->name;
    }
}