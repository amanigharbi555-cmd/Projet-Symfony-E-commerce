<?php

namespace App\DataFixtures;

use App\Entity\Coupons;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CouponsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Coupon 1: Réduction de 10%
        $coupon1 = new Coupons();
        $coupon1->setCode('PROMO10');
        $coupon1->setDescription('Réduction de 10% sur votre commande');
        $coupon1->setDiscount(10);
        $coupon1->setMaxUsage(100);
        $coupon1->setValidity(new \DateTime('+1 month'));
        $coupon1->setIsValid(true);
        $coupon1->setCouponsTypes($this->getReference(CouponsTypesFixtures::PERCENTAGE_TYPE));
        $manager->persist($coupon1);

        // Coupon 2: Réduction de 20%
        $coupon2 = new Coupons();
        $coupon2->setCode('PROMO20');
        $coupon2->setDescription('Réduction de 20% sur votre commande');
        $coupon2->setDiscount(20);
        $coupon2->setMaxUsage(50);
        $coupon2->setValidity(new \DateTime('+2 months'));
        $coupon2->setIsValid(true);
        $coupon2->setCouponsTypes($this->getReference(CouponsTypesFixtures::PERCENTAGE_TYPE));
        $manager->persist($coupon2);

        // Coupon 3: Réduction de 5 TND
        $coupon3 = new Coupons();
        $coupon3->setCode('PROMO5TND');
        $coupon3->setDescription('Réduction de 5 TND sur votre commande');
        $coupon3->setDiscount(5);
        $coupon3->setMaxUsage(200);
        $coupon3->setValidity(new \DateTime('+3 months'));
        $coupon3->setIsValid(true);
        $coupon3->setCouponsTypes($this->getReference(CouponsTypesFixtures::FIXED_TYPE));
        $manager->persist($coupon3);

        // Coupon 4: Coupon VIP
        $coupon4 = new Coupons();
        $coupon4->setCode('VIP30');
        $coupon4->setDescription('Réduction VIP de 30% - Offre exclusive');
        $coupon4->setDiscount(30);
        $coupon4->setMaxUsage(20);
        $coupon4->setValidity(new \DateTime('+1 week'));
        $coupon4->setIsValid(true);
        $coupon4->setCouponsTypes($this->getReference(CouponsTypesFixtures::PERCENTAGE_TYPE));
        $manager->persist($coupon4);

        // Coupon 5: Coupon expiré (pour tester)
        $coupon5 = new Coupons();
        $coupon5->setCode('EXPIRED');
        $coupon5->setDescription('Coupon expiré - pour tests');
        $coupon5->setDiscount(15);
        $coupon5->setMaxUsage(10);
        $coupon5->setValidity(new \DateTime('-1 day'));
        $coupon5->setIsValid(false);
        $coupon5->setCouponsTypes($this->getReference(CouponsTypesFixtures::PERCENTAGE_TYPE));
        $manager->persist($coupon5);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CouponsTypesFixtures::class,
        ];
    }
}
