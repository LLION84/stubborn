<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\CartService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * Tests unitaires du panier (CartService).
 */
class CartServiceTest extends TestCase
{
    private CartService $cart;

    /**
     * Lancé avant CHAQUE test : prépare un panier vide tout neuf.
     */
    protected function setUp(): void
    {
        // Une fausse session, gardée en mémoire
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $requestStack = new RequestStack();
        $requestStack->push($request);

        // Deux faux sweats
        $blackbelt = (new Product())->setName('Blackbelt')->setPrice(29.90);
        $pokeball = (new Product())->setName('Pokeball')->setPrice(45.00);

        // Un faux repository : find(1) → Blackbelt, find(2) → Pokeball
        $repository = $this->createMock(ProductRepository::class);
        $repository->method('find')->willReturnCallback(
            fn($id) => ['1' => $blackbelt, '2' => $pokeball][(string) $id] ?? null
        );

        $this->cart = new CartService($requestStack, $repository);
    }

    /**
     * Ajouter un sweat crée une ligne dans le panier.
     */
    public function testAjouterUnProduitAuPanier(): void
    {
        $this->cart->add(1, 'M');

        $items = $this->cart->getItems();

        $this->assertCount(1, $items);
        $this->assertSame('Blackbelt', $items[0]['product']->getName());
        $this->assertSame('M', $items[0]['size']);
        $this->assertSame(1, $items[0]['quantity']);
    }

    /**
     * Le même sweat dans la même taille augmente la quantité au lieu de créer une ligne.
     */
    public function testAjouterDeuxFoisLeMemeProduitAugmenteLaQuantite(): void
    {
        $this->cart->add(1, 'M');
        $this->cart->add(1, 'M');

        $items = $this->cart->getItems();

        $this->assertCount(1, $items);
        $this->assertSame(2, $items[0]['quantity']);
    }

    /**
     * Le total additionne le prix de chaque ligne.
     */
    public function testLeTotalEstCalculeCorrectement(): void
    {
        $this->cart->add(1, 'M');   // 29,90 €
        $this->cart->add(2, 'L');   // 45,00 €

        $this->assertEqualsWithDelta(74.90, $this->cart->getTotal(), 0.001);
    }

    /**
     * Retirer une ligne la supprime du panier.
     */
    public function testRetirerUnProduitDuPanier(): void
    {
        $this->cart->add(1, 'M');
        $this->cart->remove('1-M');

        $this->assertCount(0, $this->cart->getItems());
        $this->assertEqualsWithDelta(0.0, $this->cart->getTotal(), 0.001);
    }

    /**
     * Une taille qui n'existe pas est refusée.
     */
    public function testUneTailleInconnueEstRefusee(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->cart->add(1, 'XXL');
    }
}
