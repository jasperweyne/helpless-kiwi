<?php

namespace App\Log;

use App\Entity\Log\Event as EventEntity;
use App\Entity\Security\LocalAccount;
use App\Reflection\ReflectionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class EventService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string|\Stringable|UserInterface|null
     */
    private $person;

    /**
     * @var ReflectionService
     */
    private $refl;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage, ReflectionService $refl)
    {
        $token = $tokenStorage->getToken();

        if (null === $token || !\is_object($token->getUser())) {
            $this->person = null;
        } else {
            $this->person = $token->getUser();
        }

        $this->em = $em;
        $this->refl = $refl;
    }

    public function log(AbstractEvent $event): void
    {
        $entity = $this->hydrate($event);
        assert(null !== $entity); // hydrate can return null, that's unwanted.
        // this only happens if you pass it null as an argument.
        // should be rewritten in a nicer fashion.

        $this->em->persist($entity);
        $this->em->flush();
    }

    public function hydrate(?AbstractEvent $event): ?EventEntity
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
        assert($this->person instanceof LocalAccount || null === $this->person);
        $entity
            ->setTime(new \DateTime())
            ->setDiscr(get_class($event))
            ->setPerson($this->person)
            ->setMeta(serialize($meta));

        $object = $event->getEntity();
        if (null !== $object) {
            if (!is_string($this->getIdentifier($object))) {
                @trigger_error(
                    'Entities with identifiers that are not of type string, are not supported yet.',
                    E_USER_WARNING
                );

                return null;
            }

            $entity
                ->setObjectId($this->getIdentifier($object)) // todo: assumes id is string without assertion, fix this
                ->setObjectType($this->getClassName($object));
        }

        return $entity;
    }

    public function populate(?EventEntity $entity): ?AbstractEvent
    {
        if (null === $entity) {
            return null;
        }

        $objectType = $entity->getObjectType();
        $objectId = $entity->getObjectId();
        $em = $this->em;

        assert(null !== $objectType);
        $objectClosure = function () use ($em, $objectType, $objectId) {
            return $em->find($objectType, $objectId);
        };

        assert(is_string($entity->getMeta()));
        /** @var array<string, mixed> */
        $fields = unserialize($entity->getMeta());
        $fields['time'] = $entity->getTime();
        $fields['person'] = $entity->getPerson();
        $fields['entityCb'] = $objectClosure;
        $fields['entityType'] = $objectType;

        assert(null !== $entity->getDiscr());
        $class = class_exists($entity->getDiscr()) ? $entity->getDiscr() : AbstractEvent::class;

        /** @var AbstractEvent */
        return $this->refl->instantiate($class, $fields);
    }

    /**
     * @param array<EventEntity> $entities
     *
     * @return (?AbstractEvent)[]
     */
    public function populateAll(array $entities)
    {
        return array_map([$this, 'populate'], $entities);
    }

    /**
     * @template T of object
     *
     * @param ?T                    $entity
     * @param ?class-string<T>      $type
     * @param array<string, string> $options
     *
     * @return (?AbstractEvent)[]
     */
    public function findBy(object $entity = null, string $type = null, array $options = [])
    {
        if (null !== $entity) {
            $options['objectId'] = $this->getIdentifier($entity);
            $options['objectType'] = get_class($entity);
        }

        if (null !== $type) {
            $options['discr'] = $type;
        }

        /** @var EventEntity[] */
        $found = $this->em->getRepository(EventEntity::class)->findBy($options);

        return $this->populateAll($found);
    }

    /**
     * @template T of object
     *
     * @param ?T                    $entity
     * @param ?class-string<T>      $type
     * @param array<string, string> $options
     *
     * @return ?AbstractEvent
     */
    public function findOneBy(object $entity = null, ?string $type = null, array $options = [])
    {
        if (null !== $entity) {
            $options['objectId'] = $this->getIdentifier($entity);
            $options['objectType'] = get_class($entity);
        }

        if (null !== $type) {
            $options['discr'] = $type;
        }

        $found = $this->em->getRepository(EventEntity::class)->findOneBy($options);

        return $this->populate($found);
    }

    public function getIdentifier(object $entity): mixed
    {
        $className = $this->getClassName($entity);
        $identifier = $this->em->getClassMetadata($className)->getSingleIdentifierFieldName();
        $refl = $this->refl->getAccessibleProperty($className, $identifier);

        assert($refl instanceof \ReflectionProperty);

        return $refl->getValue($entity);
    }

    /**
     * @template T of object
     *
     * @phpstan-param T $entity
     *
     * @phpstan-return class-string<T>
     */
    public function getClassName(object $entity): string
    {
        return $this->em->getClassMetadata(get_class($entity))->name;
    }
}
