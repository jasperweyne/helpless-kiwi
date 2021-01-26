<?php

namespace App\DataFixtures\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use App\Tests\Helper\TestData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PriceOptionFixture extends Fixture
{
    public const PRICE_OPTION_REFERENCE = 'price';

    public function load(ObjectManager $manager)
    {
        $activities = $manager->getRepository(Activity::class)->findAll();

        foreach (self::generate($activities) as $object) {
            $manager->persist($object);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            ActivityFixture::class,
        ];
    }

    public static function generate(array $activities)
    {
        return TestData::from(new PriceOption())
            ->with('name', 'free')
            ->with('price', 0.00, 1.00)
            ->with('details', [])
            ->with('confirmationMsg', '')
            ->doWith('activity', function (PriceOption $priceOption, Activity $activity) {
                $priceOption->setActivity($activity);
            }, ...$activities)
            ->return();
    }
}
