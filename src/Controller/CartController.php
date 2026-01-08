<?php
namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use App\Service\CouponService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(SessionInterface $session, ProductsRepository $productsRepository, CouponService $couponService)
    {
        $panier = $session->get('panier', []);

        // On initialise des variables
        $data = [];
        $total = 0;

        foreach($panier as $id => $quantity){
            $product = $productsRepository->find($id);

            $data[] = [
                'product' => $product,
                'quantity' => $quantity
            ];
            $total += $product->getPrice() * $quantity;
        }

        // Gestion des coupons
        $coupon = null;
        $discount = 0;
        $finalTotal = $total;

        $couponCode = $session->get('coupon_code');
        if ($couponCode) {
            $couponData = $couponService->validateCoupon($couponCode);
            if ($couponData['valid']) {
                $coupon = $couponData['coupon'];
                $result = $couponService->applyCoupon($coupon, $total / 100);
                $discount = $result['discount'] * 100; // Convertir en centimes
                $finalTotal = $result['new_total'] * 100;
            } else {
                // Si le coupon n'est plus valide, on le retire de la session
                $session->remove('coupon_code');
            }
        }
        
        return $this->render('cart/index.html.twig', [
            'data' => $data,
            'total' => $total,
            'coupon' => $coupon,
            'discount' => $discount,
            'finalTotal' => $finalTotal
        ]);
    }


    #[Route('/add/{id}', name: 'add')]
    public function add(Products $product, SessionInterface $session)
    {
        //On récupère l'id du produit
        $id = $product->getId();

        // On récupère le panier existant
        $panier = $session->get('panier', []);

        // On ajoute le produit dans le panier s'il n'y est pas encore
        // Sinon on incrémente sa quantité
        if(empty($panier[$id])){
            $panier[$id] = 1;
        }else{
            $panier[$id]++;
        }

        $session->set('panier', $panier);
        
        //On redirige vers la page du panier
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/remove/{id}', name: 'remove')]
    public function remove(Products $product, SessionInterface $session)
    {
        //On récupère l'id du produit
        $id = $product->getId();

        // On récupère le panier existant
        $panier = $session->get('panier', []);

        // On retire le produit du panier s'il n'y a qu'1 exemplaire
        // Sinon on décrémente sa quantité
        if(!empty($panier[$id])){
            if($panier[$id] > 1){
                $panier[$id]--;
            }else{
                unset($panier[$id]);
            }
        }

        $session->set('panier', $panier);
        
        //On redirige vers la page du panier
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Products $product, SessionInterface $session)
    {
        //On récupère l'id du produit
        $id = $product->getId();

        // On récupère le panier existant
        $panier = $session->get('panier', []);

        if(!empty($panier[$id])){
            unset($panier[$id]);
        }

        $session->set('panier', $panier);
        
        //On redirige vers la page du panier
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/empty', name: 'empty')]
    public function empty(SessionInterface $session)
    {
        $session->remove('panier');
        $session->remove('coupon_code');

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/apply-coupon', name: 'apply_coupon', methods: ['POST'])]
    public function applyCoupon(Request $request, SessionInterface $session, CouponService $couponService)
    {
        $couponCode = $request->request->get('coupon_code');

        if (!$couponCode) {
            $this->addFlash('danger', 'Veuillez saisir un code promo');
            return $this->redirectToRoute('cart_index');
        }

        $result = $couponService->validateCoupon($couponCode);

        if ($result['valid']) {
            $session->set('coupon_code', strtoupper($couponCode));
            $this->addFlash('success', $result['message']);
        } else {
            $this->addFlash('danger', $result['message']);
        }

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/remove-coupon', name: 'remove_coupon')]
    public function removeCoupon(SessionInterface $session)
    {
        $session->remove('coupon_code');
        $this->addFlash('info', 'Le code promo a été retiré');

        return $this->redirectToRoute('cart_index');
    }
}