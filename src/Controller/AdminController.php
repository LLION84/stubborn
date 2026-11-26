<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]   // ← tout le contrôleur est réservé à l'administrateur
#[Route('/admin')]           // ← toutes les routes commencent par /admin
final class AdminController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FormFactoryInterface $formFactory,
    ) {}

    /**
     * La page du back-office : formulaire d'ajout + liste des sweats.
     */
    #[Route('', name: 'app_admin')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        // --- Le formulaire d'AJOUT ---
        $product = new Product();
        $addForm = $this->createForm(ProductType::class, $product);
        $addForm->handleRequest($request);

        if ($addForm->isSubmitted() && $addForm->isValid()) {
            $file = $addForm->get('imageFile')->getData();

            if ($file === null) {
                // Un nouveau sweat doit avoir une image
                $addForm->get('imageFile')->addError(new FormError('Une image est obligatoire pour un nouveau sweat.'));
            } else {
                $product->setImage($this->uploadImage($file));
                $this->entityManager->persist($product);
                $this->entityManager->flush();

                $this->addFlash('success', $product->getName() . ' a été ajouté.');
                return $this->redirectToRoute('app_admin');
            }
        }

        // --- Un formulaire de MODIFICATION par sweat ---
        $products = $productRepository->findAll();
        $editForms = [];
        foreach ($products as $p) {
            $editForms[$p->getId()] = $this->createEditForm($p)->createView();
        }

        return $this->render('admin/index.html.twig', [
            'addForm' => $addForm,
            'products' => $products,
            'editForms' => $editForms,
        ]);
    }

    /**
     * Enregistre la modification d'un sweat.
     */
    #[Route('/edit/{id}', name: 'app_admin_edit', methods: ['POST'])]
    public function edit(Product $product, Request $request): Response
    {
        $form = $this->createEditForm($product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Nouvelle image envoyée ? Sinon on garde l'ancienne
            $file = $form->get('imageFile')->getData();
            if ($file !== null) {
                $product->setImage($this->uploadImage($file));
            }

            $this->entityManager->flush();   // pas de persist : Doctrine connaît déjà ce sweat
            $this->addFlash('success', $product->getName() . ' a été modifié.');
        } else {
            // Formulaire invalide → on affiche chaque erreur
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('warning', $error->getMessage());
            }
        }

        return $this->redirectToRoute('app_admin');
    }

    /**
     * Supprime un sweat.
     */
    #[Route('/delete/{id}', name: 'app_admin_delete', methods: ['POST'])]
    public function delete(Product $product, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete-' . $product->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $name = $product->getName();
        $this->entityManager->remove($product);
        $this->entityManager->flush();

        $this->addFlash('success', $name . ' a été supprimé.');

        return $this->redirectToRoute('app_admin');
    }

    /**
     * Crée le formulaire de modification d'UN sweat, avec un nom unique.
     */
    private function createEditForm(Product $product): FormInterface
    {
        return $this->formFactory->createNamed(
            'product_' . $product->getId(),   // ex : product_11, product_12...
            ProductType::class,
            $product,
            ['action' => $this->generateUrl('app_admin_edit', ['id' => $product->getId()])]
        );
    }

    /**
     * Enregistre l'image envoyée dans public/images et renvoie son nouveau nom.
     */
    private function uploadImage(UploadedFile $file): string
    {
        $filename = uniqid() . '.' . $file->guessExtension();
        $file->move($this->getParameter('kernel.project_dir') . '/public/images', $filename);

        return $filename;
    }
}
