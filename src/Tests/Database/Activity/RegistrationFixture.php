<?php

namespace App\Tests\Database\Activity;

use App\Entity\Activity\Registration;
use App\Entity\Order;
use App\Repository\RegistrationRepository;
use App\Tests\Database\Security\LocalAccountFixture;
use App\Tests\TestData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RegistrationFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $person = $this->getReference(LocalAccountFixture::LOCAL_ACCOUNT_REFERENCE);
        $priceOption1 = $this->getReference(PriceOptionFixture::PRICE_OPTION_REFERENCE.'0');
        $priceOption2 = $this->getReference(PriceOptionFixture::PRICE_OPTION_REFERENCE.'1');
        $activity = $this->getReference(ActivityFixture::ACTIVITY_REFERENCE.'0');

        $registrations = self::generate([$priceOption1, $priceOption2], $activity, $person)->return();
        foreach ($registrations as $object) {
            $manager->persist($object);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PriceOptionFixture::class,
            ActivityFixture::class,
        ];
    }

    public static function generate($priceOption, $activity, $person): TestData
    {
        $counter = Order::create(RegistrationRepository::MINORDER());

        return TestData::from(new Registration())
            ->with('id', '')
            ->with('option', ...$priceOption)
            ->with('activity', $activity)
            ->with('person', $person)
            ->do('reserve_position', function ($registration) use (&$counter) {
                $counter = Order::calc($counter, Order::create('b'), fn ($a, $b) => $a + $b);
                $registration->setReservePosition($counter);
            })
            ->with('newdate', new \DateTime('first day January 2038'))
        ;
    }
}
