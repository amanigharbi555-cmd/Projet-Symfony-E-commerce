<?php

namespace App\Service;

use App\Entity\Coupons;
use App\Repository\CouponsRepository;
use Doctrine\ORM\EntityManagerInterface;

class CouponService
{
    private $couponsRepository;
    private $em;

    public function __construct(CouponsRepository $couponsRepository, EntityManagerInterface $em)
    {
        $this->couponsRepository = $couponsRepository;
        $this->em = $em;
    }

    /**
     * Vérifie si un code de coupon est valide
     * 
     * @param string $code Le code du coupon à vérifier
     * @return array Retourne un tableau avec 'valid' (bool) et 'message' (string)
     */
    public function validateCoupon(string $code): array
    {
        $coupon = $this->couponsRepository->findOneBy(['code' => strtoupper($code)]);

        if (!$coupon) {
            return [
                'valid' => false,
                'message' => 'Ce code promo n\'existe pas',
                'coupon' => null
            ];
        }

        if (!$coupon->getIsValid()) {
            return [
                'valid' => false,
                'message' => 'Ce code promo n\'est plus actif',
                'coupon' => null
            ];
        }

        if ($coupon->getValidity() < new \DateTime()) {
            return [
                'valid' => false,
                'message' => 'Ce code promo a expiré',
                'coupon' => null
            ];
        }

        if ($coupon->getOrders()->count() >= $coupon->getMaxUsage()) {
            return [
                'valid' => false,
                'message' => 'Ce code promo a atteint son nombre maximum d\'utilisations',
                'coupon' => null
            ];
        }

        return [
            'valid' => true,
            'message' => 'Code promo appliqué avec succès',
            'coupon' => $coupon
        ];
    }

    /**
     * Calcule la réduction d'un coupon sur un montant donné
     * 
     * @param Coupons $coupon Le coupon à appliquer
     * @param float $amount Le montant sur lequel appliquer la réduction
     * @return float Le montant de la réduction
     */
    public function calculateDiscount(Coupons $coupon, float $amount): float
    {
        $type = $coupon->getCouponsTypes()->getName();

        if ($type === 'Pourcentage') {
            // Réduction en pourcentage
            $discount = ($amount * $coupon->getDiscount()) / 100;
        } else {
            // Réduction en montant fixe
            $discount = min($coupon->getDiscount(), $amount); // Ne pas dépasser le montant total
        }

        return round($discount, 2);
    }

    /**
     * Applique un coupon et retourne le nouveau total
     * 
     * @param Coupons $coupon Le coupon à appliquer
     * @param float $amount Le montant initial
     * @return array Retourne le nouveau total et le montant de la réduction
     */
    public function applyCoupon(Coupons $coupon, float $amount): array
    {
        $discount = $this->calculateDiscount($coupon, $amount);
        $newTotal = max(0, $amount - $discount); // Le total ne peut pas être négatif

        return [
            'original_amount' => $amount,
            'discount' => $discount,
            'new_total' => $newTotal,
            'coupon' => $coupon
        ];
    }

    /**
     * Récupère un coupon par son code
     * 
     * @param string $code Le code du coupon
     * @return Coupons|null
     */
    public function getCouponByCode(string $code): ?Coupons
    {
        return $this->couponsRepository->findOneBy(['code' => strtoupper($code)]);
    }

    /**
     * Vérifie si un coupon est toujours disponible pour utilisation
     * 
     * @param Coupons $coupon
     * @return bool
     */
    public function isCouponAvailable(Coupons $coupon): bool
    {
        return $coupon->getIsValid() 
            && $coupon->getValidity() >= new \DateTime()
            && $coupon->getOrders()->count() < $coupon->getMaxUsage();
    }

    /**
     * Récupère tous les coupons actifs
     * 
     * @return array
     */
    public function getActiveCoupons(): array
    {
        $allCoupons = $this->couponsRepository->findBy(['is_valid' => true]);
        $activeCoupons = [];

        foreach ($allCoupons as $coupon) {
            if ($this->isCouponAvailable($coupon)) {
                $activeCoupons[] = $coupon;
            }
        }

        return $activeCoupons;
    }
}
