<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Repository\OrdersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profil', name: 'profile_')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/commandes', name: 'orders')]
    public function orders(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('profile/orders.html.twig', [
            'user' => $user,
            'orders' => $user->getOrders(),
        ]);
    }

    #[Route('/commandes/{id}', name: 'order_details')]
    public function orderDetails(Orders $order, OrdersRepository $ordersRepository): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Vérifier que la commande appartient à l'utilisateur connecté
        if ($order->getUsers()->getId() !== $user->getId()) {
            $this->addFlash('danger', 'Vous n\'avez pas accès à cette commande.');
            return $this->redirectToRoute('profile_orders');
        }

        return $this->render('profile/order_details.html.twig', [
            'order' => $order,
            'user' => $user,
        ]);
    }
}
