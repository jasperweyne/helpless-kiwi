<?php

namespace App\Tests\Database\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use App\Entity\Order;
use App\Entity\Security\LocalAccount;
use App\Repository\RegistrationRepository;
use App\Tests\Database\Security\LocalAccountFixture;
use App\Tests\TestData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RegistrationFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var LocalAccount */
        $person = $this->getReference(LocalAccountFixture::LOCAL_ACCOUNT_REFERENCE);
        /** @var PriceOption */
        $priceOption1 = $this->getReference(PriceOptionFixture::PRICE_OPTION_REFERENCE.'0');
        /** @var PriceOption */
        $priceOption2 = $this->getReference(PriceOptionFixture::PRICE_OPTION_REFERENCE.'1');
        /** @var Activity */
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

    /**
     * @param PriceOption[] $priceOption
     *
     * @return TestData<Registration>
     */
    public static function generate(array $priceOption, Activity $activity, LocalAccount $person): TestData
    {
        $counter = Order::create(RegistrationRepository::MINORDER());

        return TestData::from(new Registration())
            ->with('id', '')
            ->with('option', ...$priceOption)
            ->with('activity', $activity, null)
            ->with('person', $person)
            ->do('reserve_position', fn ($reg) => $reg, function ($registration) use (&$counter) {
                $counter = Order::calc($counter, Order::create('b'), fn ($a, $b) => $a + $b);
                $registration->setReservePosition($counter);
            })
            ->with('newdate', new \DateTime('first day January 2038'))
        ;
    }
}
