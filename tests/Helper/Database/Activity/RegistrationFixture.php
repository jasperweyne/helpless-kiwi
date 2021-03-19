<?php

namespace Tests\Helper\Database\Activity;

use App\Entity\Activity\Registration;
use App\Entity\Order;
use App\Repository\RegistrationRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\Helper\TestData;

class RegistrationFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $priceOption = $this->getReference(PriceOptionFixture::PRICE_OPTION_REFERENCE.'0');
        $activities = $this->getReference(ActivityFixture::ACTIVITY_REFERENCE.'0');

        $registrations = self::generate($priceOption, $activities)->return();
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

    public static function generate($priceOption, $activities): TestData
    {
        $counter = Order::create(RegistrationRepository::MINORDER());

        return TestData::from(new Registration())
            ->with('id', '')
            ->with('option', $priceOption)
            ->with('activity', $activities)
            ->with('person_id', '1', '2', '3')
            ->do('reserve_position', function ($registration) use (&$counter) {
                $counter = Order::calc($counter, Order::create('b'), fn ($a, $b) => $a + $b);
                $registration->setReservePosition($counter);
            })
            ->with('newdate', new \DateTime('first day January 2038'))
        ;
    }
}
