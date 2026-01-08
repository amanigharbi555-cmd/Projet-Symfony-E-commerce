<?php

namespace App\Controller\Admin;

use App\Entity\Coupons;
use App\Entity\CouponsTypes;
use App\Form\CouponsType;
use App\Form\CouponsTypesType;
use App\Repository\CouponsRepository;
use App\Repository\CouponsTypesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/admin/coupons', name: 'admin_coupons_')]
#[IsGranted('ROLE_ADMIN')]
class CouponsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CouponsRepository $couponsRepository): Response
    {
        $coupons = $couponsRepository->findAll();

        return $this->render('admin/coupons/index.html.twig', [
            'coupons' => $coupons,
        ]);
    }

    #[Route('/add', name: 'add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $coupon = new Coupons();
        $form = $this->createForm(CouponsType::class, $coupon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($coupon);
            $em->flush();

            $this->addFlash('success', 'Le coupon a été créé avec succès');
            return $this->redirectToRoute('admin_coupons_index');
        }

        return $this->render('admin/coupons/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Coupons $coupon, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CouponsType::class, $coupon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Le coupon a été modifié avec succès');
            return $this->redirectToRoute('admin_coupons_index');
        }

        return $this->render('admin/coupons/edit.html.twig', [
            'form' => $form->createView(),
            'coupon' => $coupon,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Coupons $coupon, EntityManagerInterface $em): Response
    {
        if ($coupon->getOrders()->count() > 0) {
            $this->addFlash('danger', 'Impossible de supprimer ce coupon car il est utilisé dans des commandes');
        } else {
            $em->remove($coupon);
            $em->flush();
            $this->addFlash('success', 'Le coupon a été supprimé avec succès');
        }

        return $this->redirectToRoute('admin_coupons_index');
    }

    // Gestion des types de coupons
    #[Route('/types', name: 'types_index')]
    public function typesIndex(CouponsTypesRepository $couponsTypesRepository): Response
    {
        $couponsTypes = $couponsTypesRepository->findAll();

        return $this->render('admin/coupons/types_index.html.twig', [
            'coupons_types' => $couponsTypes,
        ]);
    }

    #[Route('/types/add', name: 'types_add')]
    public function typesAdd(Request $request, EntityManagerInterface $em): Response
    {
        $couponType = new CouponsTypes();
        $form = $this->createForm(CouponsTypesType::class, $couponType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($couponType);
            $em->flush();

            $this->addFlash('success', 'Le type de coupon a été créé avec succès');
            return $this->redirectToRoute('admin_coupons_types_index');
        }

        return $this->render('admin/coupons/types_add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/types/edit/{id}', name: 'types_edit')]
    public function typesEdit(CouponsTypes $couponType, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CouponsTypesType::class, $couponType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Le type de coupon a été modifié avec succès');
            return $this->redirectToRoute('admin_coupons_types_index');
        }

        return $this->render('admin/coupons/types_edit.html.twig', [
            'form' => $form->createView(),
            'coupon_type' => $couponType,
        ]);
    }

    #[Route('/types/delete/{id}', name: 'types_delete', methods: ['POST'])]
    public function typesDelete(CouponsTypes $couponType, EntityManagerInterface $em): Response
    {
        if ($couponType->getCoupons()->count() > 0) {
            $this->addFlash('danger', 'Impossible de supprimer ce type car il est utilisé par des coupons');
        } else {
            $em->remove($couponType);
            $em->flush();
            $this->addFlash('success', 'Le type de coupon a été supprimé avec succès');
        }

        return $this->redirectToRoute('admin_coupons_types_index');
    }
}
