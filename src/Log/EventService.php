<?php

namespace App\Log;

use App\Entity\Log\Event as EventEntity;
use Doctrine\Instantiator\Instantiator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EventService
{
    private $em;

    private $auth;

    private $instantiator;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage) {
        $this->em = $em;
        $this->auth = $tokenStorage->getToken()->getUser();
        $this->instantiator = new Instantiator();
    }

    public function log(AbstractEvent $event) {
        $entity = $this->hydrate($event);
        
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function hydrate(AbstractEvent $event) {
        $meta = array();
        $rootProperties = self::getAllProperties(AbstractEvent::class);
        foreach (self::getAllProperties(get_class($event)) as $name => $property) {
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
                ->setObjectType(get_class($object))
            ;
        }

        return $entity;
    }

    public function populate(EventEntity $entity) {

        $reflFields = self::getAllProperties($entity->getDiscr());
        
        $objectType = $entity->getObjectType();
        $objectId   = $entity->getObjectId();
        $em         = $this->em;

        $objectClosure = function () use ($em, $objectType, $objectId) {
            return $em->find($objectType, $objectId);
        };
        
        $event = $this->instantiator->instantiate($entity->getDiscr());

        $reflFields['time']->setValue($event, $entity->getTime());
        $reflFields['auth']->setValue($event, $entity->getAuth());
        $reflFields['entityCb']->setValue($event, $objectClosure);

        $meta = unserialize($entity->getMeta());
        foreach ($event->migrateMetadata($meta) as $field => $value) {
            $reflFields[$field]->setValue($event, $value);
        }

        return $event;
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

    private function getIdentifier($entity) {
        $className = get_class($entity);
        $identifier = $this->em->getClassMetadata($className)->getSingleIdentifierFieldName();
        $reflFields = self::getAllProperties($className);
        return $reflFields[$identifier]->getValue($entity);
    }

    public static function getAllProperties(string $class) {
        $reflFields = array();
        try {
            $reflClass = new \ReflectionClass($class);
            do {
                foreach ($reflClass->getProperties() as $property) {
                    $property->setAccessible(true);
                    $reflFields[$property->getName()] = $property;
                }
            } while ($reflClass = $reflClass->getParentClass());
        } catch (\ReflectionException $e) { }

        return $reflFields;
    }
}