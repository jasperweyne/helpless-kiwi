<?php

namespace Tests\Helper\Database\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\Helper\TestData;

class RegistrationFixture extends Fixture implements DependentFixtureInterface
{
    public const REGISTRATION_REFERENCE = 'registration';

    public function load(ObjectManager $manager)
    {
        $activities = $manager->getRepository(Activity::class)->findAll();
        $priceoption = $manager->getRepository(PriceOption::class)->findAll()[0];

        $registrations = self::generate($activities, $priceoption)->return();
        foreach ($registrations as $object) {
            $manager->persist($object);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActivityFixture::class,
            PriceOptionFixture::class,
        ];
    }

    public static function generate(array $activities, priceOption $priceoption): TestData
    {
        return TestData::from(new Registration())
            ->with('newdate', new \DateTime('second day January 2038 18:10'))
            ->with('person_id', 0)
            ->doWith('activity', function (Registration $registration, Activity $activity) {
            $registration->setActivity($activity);
        }, ...$activities)
            ->doWith('option', function (Registration $registration, PriceOption $priceoption) {
            $registration->setOption($priceoption);
        }, $priceoption);
    }
}
