<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]   // ← le panier est réservé aux utilisateurs connectés
final class CartController extends AbstractController
{
    /**
     * Affiche le panier et son total.
     */
    #[Route('/cart', name: 'app_cart')]
    public function index(CartService $cartService): Response
    {
        return $this->render('cart/index.html.twig', [
            'items' => $cartService->getItems(),
            'total' => $cartService->getTotal(),
        ]);
    }

    /**
     * Ajoute un sweat (dans la taille choisie) au panier.
     */
    #[Route('/cart/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Product $product, Request $request, CartService $cartService): Response
    {
        // Vérifie le jeton anti-CSRF envoyé par le formulaire
        if (!$this->isCsrfTokenValid('add-to-cart', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        // La taille choisie dans la liste déroulante
        $size = (string) $request->request->get('size');

        try {
            $cartService->add($product->getId(), $size);
        } catch (\InvalidArgumentException $e) {
            // Taille inventée (formulaire trafiqué) → page d'erreur 404
            throw $this->createNotFoundException($e->getMessage());
        }

        $this->addFlash('success', $product->getName() . ' (taille ' . $size . ') a été ajouté au panier.');

        return $this->redirectToRoute('app_cart');
    }

    /**
     * Retire une ligne du panier.
     */
    #[Route('/cart/remove/{key}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(string $key, Request $request, CartService $cartService): Response
    {
        if (!$this->isCsrfTokenValid('remove-' . $key, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $cartService->remove($key);

        return $this->redirectToRoute('app_cart');
    }
}
