<?php

namespace App\Tests\Database\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use App\Tests\TestData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PriceOptionFixture extends Fixture implements DependentFixtureInterface
{
    public const PRICE_OPTION_REFERENCE = 'price-';

    public function load(ObjectManager $manager): void
    {
        $activities = $manager->getRepository(Activity::class)->findAll();
        $priceCount = 0;

        $options = self::generate($activities)->return();
        foreach ($options as $object) {
            $this->setReference($this::PRICE_OPTION_REFERENCE.$priceCount, $object);
            $manager->persist($object);
            ++$priceCount;
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActivityFixture::class,
        ];
    }

    /**
     * @param Activity[] $activities
     *
     * @return TestData<PriceOption>
     */
    public static function generate(array $activities): TestData
    {
        return TestData::from(new PriceOption())
            ->with('name', 'free')
            ->with('price', 0.00, 1.00)
            ->with('details', [])
            ->with('confirmationMsg', '')
            ->doWith('activity', function (PriceOption $priceOption, Activity $activity) {
                $priceOption->setActivity($activity);
            }, ...$activities)
        ;
    }
}
