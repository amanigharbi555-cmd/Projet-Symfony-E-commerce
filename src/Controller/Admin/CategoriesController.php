<?php

namespace App\Controller\Admin;

use App\Entity\Categories;
use App\Form\CategoriesFormType;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/categories', name: 'admin_categories_')]
class CategoriesController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CategoriesRepository $categoriesRepository): Response
    {
        $categories = $categoriesRepository->findBy([], ['categoryOrder' => 'asc']);

        return $this->render('admin/categories/index.html.twig', compact('categories'));
    }

    #[Route('/ajout', name: 'add')]
    public function add(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $category = new Categories();
        $categoryForm = $this->createForm(CategoriesFormType::class, $category);

        $categoryForm->handleRequest($request);

        if($categoryForm->isSubmitted() && $categoryForm->isValid()){
            // Générer le slug à partir du nom
            $slug = $slugger->slug($category->getName())->lower();
            $category->setSlug($slug);

            // Si categoryOrder n'est pas défini, le mettre à 0
            if($category->getCategoryOrder() === null) {
                $category->setCategoryOrder(0);
            }

            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'Catégorie ajoutée avec succès');

            return $this->redirectToRoute('admin_categories_index');
        }

        return $this->renderForm('admin/categories/add.html.twig', compact('categoryForm'));
    }

    #[Route('/edition/{id}', name: 'edit')]
    public function edit(Categories $category, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $categoryForm = $this->createForm(CategoriesFormType::class, $category);

        $categoryForm->handleRequest($request);

        if($categoryForm->isSubmitted() && $categoryForm->isValid()){
            // Régénérer le slug si le nom a changé
            $slug = $slugger->slug($category->getName())->lower();
            $category->setSlug($slug);

            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'Catégorie modifiée avec succès');

            return $this->redirectToRoute('admin_categories_index');
        }

        return $this->render('admin/categories/edit.html.twig', [
            'categoryForm' => $categoryForm->createView(),
            'category' => $category
        ]);
    }

    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(Categories $category, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Vérifier si la catégorie a des produits
        if($category->getProducts()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer cette catégorie car elle contient des produits.');
            return $this->redirectToRoute('admin_categories_index');
        }

        // Vérifier si la catégorie a des sous-catégories
        if($category->getCategories()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer cette catégorie car elle contient des sous-catégories.');
            return $this->redirectToRoute('admin_categories_index');
        }

        $em->remove($category);
        $em->flush();

        $this->addFlash('success', 'Catégorie supprimée avec succès');

        return $this->redirectToRoute('admin_categories_index');
    }
}