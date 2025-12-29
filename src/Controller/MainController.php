<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main')]
    public function index(CategoriesRepository $categoriesRepository, ProductsRepository $productsRepository): Response
    {
        // Récupérer les catégories principales et leurs sous-catégories
        $categories = $categoriesRepository->findBy([], ['categoryOrder' => 'asc']);
        
        // Récupérer les derniers produits (produits en vedette)
        $featuredProducts = $productsRepository->findBy([], ['id' => 'DESC'], 8);
        
        return $this->render('main/index.html.twig', [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts
        ]);
    }
}
