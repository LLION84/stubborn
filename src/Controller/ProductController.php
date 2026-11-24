<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]   // ← toute la boutique est réservée aux utilisateurs connectés
final class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        // Les 3 fourchettes de prix du PDF : nom => [min, max]
        $ranges = [
            '10-29' => [10, 29],
            '29-35' => [29, 35],
            '35-50' => [35, 50],
        ];

        // La fourchette choisie dans l'URL, ex : /products?range=29-35
        $selected = $request->query->get('range');

        if (isset($ranges[$selected])) {
            // Une fourchette valide → on filtre
            [$min, $max] = $ranges[$selected];
            $products = $productRepository->findByPriceRange($min, $max);
        } else {
            // Aucune fourchette (ou une fausse) → tous les sweats
            $products = $productRepository->findAll();
        }

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'ranges' => array_keys($ranges),
            'selected' => $selected,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product')]
    public function show(Product $product): Response
    {
        // Symfony va chercher tout seul le Product dont l'id est dans l'URL
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}
