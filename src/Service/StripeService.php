<?php

namespace App\Service;

use Stripe\StripeClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Service de paiement : fait le lien entre le site et Stripe.
 */
class StripeService
{
    private StripeClient $stripe;

    public function __construct(
        // Symfony va lire la clé secrète dans .env.local et la donner ici
        #[Autowire('%env(STRIPE_SECRET_KEY)%')] string $secretKey,
    ) {
        $this->stripe = new StripeClient($secretKey);
    }

    /**
     * Crée une page de paiement Stripe pour les articles du panier.
     *
     * @param array $items Les lignes du panier (venant de CartService::getItems())
     * @return string L'adresse de la page de paiement Stripe
     */
    public function createCheckoutSession(array $items, string $successUrl, string $cancelUrl): string
    {
        $lineItems = [];

        // Transforme chaque ligne du panier au format attendu par Stripe
        foreach ($items as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    // Stripe compte en CENTIMES : 29.90 € → 2990
                    'unit_amount' => (int) round($item['product']->getPrice() * 100),
                    'product_data' => [
                        'name' => $item['product']->getName() . ' - taille ' . $item['size'],
                    ],
                ],
                'quantity' => $item['quantity'],
            ];
        }

        // Demande à Stripe de créer la page de paiement
        $session = $this->stripe->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => $lineItems,
            'success_url' => $successUrl,   // où revenir si le paiement réussit
            'cancel_url' => $cancelUrl,     // où revenir si le client annule
        ]);

        return $session->url;
    }

    /**
     * Vérifie auprès de Stripe qu'une commande a vraiment été payée.
     */
    public function isPaid(string $sessionId): bool
    {
        $session = $this->stripe->checkout->sessions->retrieve($sessionId);

        return $session->payment_status === 'paid';
    }
}
