# Stubborn — Boutique en ligne de sweat-shirts

Site e-commerce réalisé avec **Symfony 6.4** pour la marque Stubborn
(*Don't compromise on your look*).

La documentation complète de l'application est disponible en PDF :
[`docs/documentation-stubborn.pdf`](docs/documentation-stubborn.pdf)

## Fonctionnalités

- Inscription avec **confirmation par e-mail**, connexion, déconnexion
- Rôles **client** et **administrateur**
- Page d'accueil avec les sweat-shirts mis en avant
- Boutique avec **filtre par fourchette de prix**
- Page produit avec **choix de la taille** (XS à XL)
- **Panier** en session : ajout, retrait, calcul du total
- **Paiement Stripe** en mode test (bac à sable)
- **Back-office** administrateur : ajouter, modifier, supprimer un sweat-shirt
- **Tests unitaires** du panier et de l'achat, lancés au démarrage

## Technologies

PHP 8.1 · Symfony 6.4 · Twig · Doctrine ORM · MySQL / MariaDB · Stripe · PHPUnit

## Prérequis

- PHP **8.1** ou plus
- [Composer](https://getcomposer.org/)
- [Symfony CLI](https://symfony.com/download)
- MySQL ou MariaDB (par exemple avec XAMPP)
- Un compte [Stripe](https://stripe.com) en **mode test** (clé `sk_test_...`)
- Un compte [Mailtrap](https://mailtrap.io) (Email Sandbox) pour recevoir les e-mails de test

## Installation

1. Cloner le dépôt et installer les dépendances :

   ```bash
   git clone https://github.com/LLION84/stubborn.git
   cd stubborn
   composer install
   ```

2. Créer un fichier `.env.local` à la racine, avec vos propres valeurs :

   ```
   DATABASE_URL="mysql://root:@127.0.0.1:3306/stubborn?serverVersion=mariadb-10.4.32&charset=utf8mb4"
   MAILER_DSN="smtp://UTILISATEUR:MOT_DE_PASSE@sandbox.smtp.mailtrap.io:2525"
   STRIPE_SECRET_KEY=sk_test_VOTRE_CLE
   ```

3. Créer un fichier `.env.test.local` à la racine (pour le test d'achat Stripe) :

   ```
   STRIPE_SECRET_KEY=sk_test_VOTRE_CLE
   ```

4. Créer la base de données, les tables, puis charger les 10 sweat-shirts et le compte administrateur :

   ```bash
   symfony console doctrine:database:create
   symfony console doctrine:migrations:migrate
   symfony console doctrine:fixtures:load
   ```

## Lancement

```bash
composer start
```

Cette commande **lance d'abord les tests unitaires** (résultats affichés dans la console),
**puis démarre le serveur** sur http://127.0.0.1:8000

## Comptes de test

| Rôle           | E-mail              | Mot de passe |
|----------------|---------------------|--------------|
| Administrateur | `admin@stubborn.fr` | `admin123`   |

Pour un compte **client** : s'inscrire sur `/register`, puis cliquer sur le lien
de confirmation reçu dans la boîte Mailtrap.

## Faire un achat test

1. Se connecter, ajouter un sweat-shirt au panier, cliquer sur **Finaliser ma commande**
2. Sur la page Stripe, utiliser la carte de test :
   - Numéro : `4242 4242 4242 4242`
   - Date : n'importe quelle date future (ex. `12/30`)
   - CVC : n'importe quels 3 chiffres (ex. `123`)
3. Après le paiement, retour sur le site avec la page de confirmation

## Tests

```bash
php bin/phpunit --testdox
```

- `tests/Service/CartServiceTest.php` : ajout, quantité, total, retrait, taille invalide
- `tests/Service/StripeServiceTest.php` : conversion des prix, création d'une session de paiement Stripe

## Structure du projet

```
src/
├── Controller/    les pages (accueil, boutique, panier, paiement, back-office...)
├── DataFixtures/  les 10 sweat-shirts et le compte administrateur
├── Entity/        User, Product
├── Form/          formulaires d'inscription et de sweat-shirt
├── Repository/    requêtes sur la base (dont le filtre par prix)
├── Security/      authentification et vérification d'e-mail
└── Service/       CartService (panier), StripeService (paiement)
templates/         les vues Twig
tests/             les tests unitaires
docs/              la documentation PDF
```
