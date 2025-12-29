<?php

namespace App\Controller\Admin;

use App\Repository\CategoriesRepository;
use App\Repository\ProductsRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class MainController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        CategoriesRepository $categoriesRepository,
        ProductsRepository $productsRepository,
        UsersRepository $usersRepository
    ): Response
    {
        return $this->render('admin/index.html.twig', [
            'categories_count' => count($categoriesRepository->findAll()),
            'products_count' => count($productsRepository->findAll()),
            'users_count' => count($usersRepository->findAll()),
        ]);
    }
}