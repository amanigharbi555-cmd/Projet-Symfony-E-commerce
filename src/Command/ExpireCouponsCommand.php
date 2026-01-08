<?php

namespace App\Command;

use App\Repository\CouponsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:coupons:expire',
    description: 'Désactive les coupons expirés',
)]
class ExpireCouponsCommand extends Command
{
    private $couponsRepository;
    private $em;

    public function __construct(CouponsRepository $couponsRepository, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->couponsRepository = $couponsRepository;
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Désactivation des coupons expirés');

        // Récupérer tous les coupons actifs
        $coupons = $this->couponsRepository->findBy(['is_valid' => true]);
        $now = new \DateTime();
        $expiredCount = 0;

        foreach ($coupons as $coupon) {
            // Vérifier si le coupon est expiré
            if ($coupon->getValidity() < $now) {
                $coupon->setIsValid(false);
                $this->em->persist($coupon);
                $expiredCount++;
                
                $io->text(sprintf(
                    '✓ Coupon "%s" désactivé (expiré le %s)',
                    $coupon->getCode(),
                    $coupon->getValidity()->format('d/m/Y H:i')
                ));
            }
        }

        if ($expiredCount > 0) {
            $this->em->flush();
            $io->success(sprintf('%d coupon(s) expiré(s) ont été désactivés.', $expiredCount));
        } else {
            $io->info('Aucun coupon expiré trouvé.');
        }

        return Command::SUCCESS;
    }
}
