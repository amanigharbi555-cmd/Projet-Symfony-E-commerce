<?php

namespace App\DataFixtures;

use App\Entity\CouponsTypes;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CouponsTypesFixtures extends Fixture
{
    public const PERCENTAGE_TYPE = 'percentage-type';
    public const FIXED_TYPE = 'fixed-type';

    public function load(ObjectManager $manager): void
    {
        // Type 1: Pourcentage
        $percentageType = new CouponsTypes();
        $percentageType->setName('Pourcentage');
        $manager->persist($percentageType);
        $this->addReference(self::PERCENTAGE_TYPE, $percentageType);

        // Type 2: Montant fixe
        $fixedType = new CouponsTypes();
        $fixedType->setName('Montant fixe');
        $manager->persist($fixedType);
        $this->addReference(self::FIXED_TYPE, $fixedType);

        $manager->flush();
    }
}
