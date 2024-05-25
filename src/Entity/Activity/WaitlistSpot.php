<?php

namespace App\Entity\Activity;

use App\Entity\Security\LocalAccount;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table('waitlist')]
class WaitlistSpot
{
    #[ORM\Id()]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid')]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    public string $id;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public readonly \DateTimeImmutable $timestamp;

    public function __construct(
        #[ORM\ManyToOne(inversedBy: 'waitlist')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        public readonly LocalAccount $person,
        #[ORM\ManyToOne(inversedBy: 'waitlist')]
        #[ORM\JoinColumn(nullable: false)]
        public readonly PriceOption $option,
    ) {
        $this->timestamp = new \DateTimeImmutable('now');
    }
}
