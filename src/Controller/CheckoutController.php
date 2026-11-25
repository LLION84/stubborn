<?php

namespace App\Controller;

use App\Service\CartService;
use App\Service\StripeService;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class CheckoutController extends AbstractController
{
    /**
     * 1. Le client clique sur « Finaliser ma commande » → on l'envoie chez Stripe.
     */
    #[Route('/checkout', name: 'app_checkout', methods: ['POST'])]
    public function checkout(Request $request, CartService $cartService, StripeService $stripeService): Response
    {
        if (!$this->isCsrfTokenValid('checkout', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $items = $cartService->getItems();

        // Style défensif : on ne paie pas un panier vide
        if (empty($items)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart');
        }

        // Les adresses COMPLÈTES de retour (Stripe a besoin de http://...)
        // {CHECKOUT_SESSION_ID} sera remplacé par Stripe par le numéro du paiement
        $successUrl = $this->generateUrl('app_checkout_success', [], UrlGeneratorInterface::ABSOLUTE_URL)
            . '?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = $this->generateUrl('app_checkout_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $stripeUrl = $stripeService->createCheckoutSession($items, $successUrl, $cancelUrl);

        // On quitte notre site pour la page de paiement Stripe
        return $this->redirect($stripeUrl);
    }

    /**
     * 2. Retour de Stripe après un paiement.
     */
    #[Route('/checkout/success', name: 'app_checkout_success')]
    public function success(Request $request, CartService $cartService, StripeService $stripeService): Response
    {
        $sessionId = (string) $request->query->get('session_id');

        // On redemande à Stripe si c'est VRAIMENT payé
        try {
            $paid = $sessionId !== '' && $stripeService->isPaid($sessionId);
        } catch (ApiErrorException) {
            $paid = false;   // numéro inventé ou inconnu de Stripe
        }

        if (!$paid) {
            $this->addFlash('warning', "Le paiement n'a pas été confirmé.");
            return $this->redirectToRoute('app_cart');
        }

        // Payé → on vide le panier
        $cartService->clear();

        return $this->render('checkout/success.html.twig');
    }

    /**
     * 3. Le client a cliqué sur « Retour » chez Stripe.
     */
    #[Route('/checkout/cancel', name: 'app_checkout_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('warning', 'Paiement annulé. Votre panier a été conservé.');

        return $this->redirectToRoute('app_cart');
    }
}
