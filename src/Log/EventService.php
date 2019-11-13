<?php

namespace App\Log;

use App\Entity\Log\Event as EventEntity;
use App\Reflection\ReflectionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EventService
{
    private $em;

    private $auth;

    private $refl;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage, ReflectionService $refl)
    {
        $token = $tokenStorage->getToken();

        if (null === $token || !\is_object($token->getUser())) {
            $this->auth = null;
        } else {
            $this->auth = $token->getUser();
        }

        $this->em = $em;
        $this->refl = $refl;
    }

    public function log(AbstractEvent $event)
    {
        $entity = $this->hydrate($event);

        $this->em->persist($entity);
        $this->em->flush();
    }

    public function hydrate(?AbstractEvent $event)
    {
        if (null === $event) {
            return null;
        }

        $meta = [];
        $rootProperties = $this->refl->getAllProperties(AbstractEvent::class);
        foreach ($this->refl->getAllProperties(get_class($event)) as $name => $property) {
            if (in_array($property, $rootProperties)) {
                continue;
            }

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
        if (null !== $object) {
            if (!is_string($this->getIdentifier($object))) {
                @trigger_error('Entities with identifiers that are not of type string, are not supported yet.', E_USER_WARNING);

                return null;
            }

            $entity
                ->setObjectId($this->getIdentifier($object)) // todo: assumes id is string without assertion, fix this
                ->setObjectType($this->getClassName($object))
            ;
        }

        return $entity;
    }

    public function populate(?EventEntity $entity)
    {
        if (null === $entity) {
            return null;
        }

        $objectType = $entity->getObjectType();
        $objectId = $entity->getObjectId();
        $em = $this->em;

        $objectClosure = function () use ($em, $objectType, $objectId) {
            return $em->find($objectType, $objectId);
        };

        $fields = unserialize($entity->getMeta());
        $fields['time'] = $entity->getTime();
        $fields['auth'] = $entity->getAuth();
        $fields['entityCb'] = $objectClosure;
        $fields['entityType'] = $objectType;

        $class = class_exists($entity->getDiscr()) ? $entity->getDiscr() : AbstractEvent::class;

        return $this->refl->instantiate($class, $fields);
    }

    public function populateAll(array $entities)
    {
        return array_map([$this, 'populate'], $entities);
    }

    public function findBy($entity = null, string $type = '', array $options = [])
    {
        if (null !== $entity) {
            $options['objectId'] = $this->getIdentifier($entity);
            $options['objectType'] = get_class($entity);
        }

        if ('' !== $type) {
            $options['discr'] = $type;
        }

        $found = $this->em->getRepository(EventEntity::class)->findBy($options);

        return $this->populateAll($found);
    }

    public function findOneBy($entity = null, string $type = '', array $options = [])
    {
        if (null !== $entity) {
            $options['objectId'] = $this->getIdentifier($entity);
            $options['objectType'] = get_class($entity);
        }

        if ('' !== $type) {
            $options['discr'] = $type;
        }

        $found = $this->em->getRepository(EventEntity::class)->findOneBy($options);

        return $this->populate($found);
    }

    public function getIdentifier($entity)
    {
        $className = $this->getClassName($entity);
        $identifier = $this->em->getClassMetadata($className)->getSingleIdentifierFieldName();
        $refl = $this->refl->getAccessibleProperty($className, $identifier);

        return $refl->getValue($entity);
    }

    public function getClassName($entity)
    {
        return $this->em->getClassMetadata(get_class($entity))->name;
    }
}
