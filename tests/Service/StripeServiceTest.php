<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Service\StripeService;
use PHPUnit\Framework\TestCase;

/**
 * Tests de l'achat avec Stripe (StripeService).
 */
class StripeServiceTest extends TestCase
{
    /**
     * Un panier de test : 2 Blackbelt en M + 1 Pokeball en L.
     */
    private function panierDeTest(): array
    {
        $blackbelt = (new Product())->setName('Blackbelt')->setPrice(29.90);
        $pokeball = (new Product())->setName('Pokeball')->setPrice(45.00);

        return [
            ['key' => '1-M', 'product' => $blackbelt, 'size' => 'M', 'quantity' => 2],
            ['key' => '2-L', 'product' => $pokeball, 'size' => 'L', 'quantity' => 1],
        ];
    }

    /**
     * La clé Stripe de test, lue dans .env.test.local.
     */
    private function cleStripe(): string
    {
        return $_SERVER['STRIPE_SECRET_KEY'] ?? $_ENV['STRIPE_SECRET_KEY'] ?? '';
    }

    /**
     * Les prix du panier sont convertis en centimes, au format attendu par Stripe.
     */
    public function testLesPrixSontEnvoyesEnCentimesAStripe(): void
    {
        $stripe = new StripeService('sk_test_fausse_cle');   // aucun appel réseau dans ce test
        $lignes = $stripe->buildLineItems($this->panierDeTest());

        $this->assertCount(2, $lignes);
        $this->assertSame(2990, $lignes[0]['price_data']['unit_amount']);
        $this->assertSame('Blackbelt - taille M', $lignes[0]['price_data']['product_data']['name']);
        $this->assertSame(2, $lignes[0]['quantity']);
        $this->assertSame(4500, $lignes[1]['price_data']['unit_amount']);
    }

    /**
     * Un achat test crée une vraie page de paiement chez Stripe (mode test).
     */
    public function testUnAchatTestCreeUnePageDePaiementStripe(): void
    {
        $cle = $this->cleStripe();

        // Sans vraie clé de test, on saute ce test au lieu de le faire échouer
        if (!str_starts_with($cle, 'sk_test_') || str_contains($cle, '*')) {
            $this->markTestSkipped('Clé Stripe absente : ajoutez STRIPE_SECRET_KEY dans .env.test.local');
        }

        $stripe = new StripeService($cle);
        $url = $stripe->createCheckoutSession(
            $this->panierDeTest(),
            'http://127.0.0.1:8000/checkout/success?session_id={CHECKOUT_SESSION_ID}',
            'http://127.0.0.1:8000/checkout/cancel'
        );

        $this->assertStringStartsWith('https://checkout.stripe.com/', $url);
    }
}
