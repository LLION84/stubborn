<?php

namespace App\Controller;

use App\Repository\ProductRepository;   // ← nouveau : l'outil pour lire la table product
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository): Response   // ← Symfony fournit le repository
    {
        // Récupère uniquement les sweats mis en avant (featured = true) → les 3 avec ★
        $products = $productRepository->findBy(['featured' => true]);

        // Envoie ces 3 produits au template sous le nom "products"
        return $this->render('home/index.html.twig', [
            'products' => $products,
        ]);
    }
}
