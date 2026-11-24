<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Gère le panier de l'utilisateur, stocké en session.
 */
class CartService
{
    /** Les tailles autorisées (section 3.1 du PDF) */
    public const SIZES = ['XS', 'S', 'M', 'L', 'XL'];

    public function __construct(
        private RequestStack $requestStack,
        private ProductRepository $productRepository,
    ) {}

    /**
     * Ajoute un sweat d'une taille donnée au panier.
     */
    public function add(int $productId, string $size): void
    {
        // Style défensif : on refuse une taille qui n'existe pas
        if (!in_array($size, self::SIZES, true)) {
            throw new \InvalidArgumentException('Taille inconnue : ' . $size);
        }

        $cart = $this->getCart();
        $key = $productId . '-' . $size;        // ex : "11-M" = sweat n°11 en taille M
        $cart[$key] = ($cart[$key] ?? 0) + 1;   // +1 exemplaire
        $this->saveCart($cart);
    }

    /**
     * Retire une ligne du panier.
     */
    public function remove(string $key): void
    {
        $cart = $this->getCart();
        unset($cart[$key]);
        $this->saveCart($cart);
    }

    /**
     * Les lignes du panier, avec les vrais objets Product.
     *
     * @return array<int, array{key: string, product: Product, size: string, quantity: int}>
     */
    public function getItems(): array
    {
        $items = [];

        foreach ($this->getCart() as $key => $quantity) {
            [$productId, $size] = explode('-', $key);   // "11-M" → 11 et "M"
            $product = $this->productRepository->find($productId);

            // Le sweat a été supprimé entre-temps → on l'ignore
            if ($product === null) {
                continue;
            }

            $items[] = [
                'key' => $key,
                'product' => $product,
                'size' => $size,
                'quantity' => $quantity,
            ];
        }

        return $items;
    }

    /**
     * Le montant total du panier.
     */
    public function getTotal(): float
    {
        $total = 0;

        foreach ($this->getItems() as $item) {
            $total += $item['product']->getPrice() * $item['quantity'];
        }

        return $total;
    }

    /**
     * Vide le panier (après un paiement réussi).
     */
    public function clear(): void
    {
        $this->saveCart([]);
    }

    private function getCart(): array
    {
        return $this->requestStack->getSession()->get('cart', []);
    }

    private function saveCart(array $cart): void
    {
        $this->requestStack->getSession()->set('cart', $cart);
    }
}
