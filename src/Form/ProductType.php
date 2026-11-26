<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

/**
 * Formulaire d'un sweat-shirt, utilisé par le back-office pour ajouter ET modifier.
 */
class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'empty_data' => '',
                'constraints' => [new NotBlank(message: 'Le nom est obligatoire.')],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix',
                'scale' => 2,                            // 2 chiffres après la virgule
                'html5' => true,                         // champ de type nombre
                'attr' => ['step' => '0.01', 'min' => '0'],
                'empty_data' => '0',
                'constraints' => [new Positive(message: 'Le prix doit être supérieur à 0.')],
            ])
            ->add('stockXs', IntegerType::class, [
                'label' => 'Stock XS',
                'attr' => ['min' => 0],
                'empty_data' => '0',
                'constraints' => [new PositiveOrZero(message: 'Le stock ne peut pas être négatif.')],
            ])
            ->add('stockS', IntegerType::class, [
                'label' => 'Stock S',
                'attr' => ['min' => 0],
                'empty_data' => '0',
                'constraints' => [new PositiveOrZero(message: 'Le stock ne peut pas être négatif.')],
            ])
            ->add('stockM', IntegerType::class, [
                'label' => 'Stock M',
                'attr' => ['min' => 0],
                'empty_data' => '0',
                'constraints' => [new PositiveOrZero(message: 'Le stock ne peut pas être négatif.')],
            ])
            ->add('stockL', IntegerType::class, [
                'label' => 'Stock L',
                'attr' => ['min' => 0],
                'empty_data' => '0',
                'constraints' => [new PositiveOrZero(message: 'Le stock ne peut pas être négatif.')],
            ])
            ->add('stockXl', IntegerType::class, [
                'label' => 'Stock XL',
                'attr' => ['min' => 0],
                'empty_data' => '0',
                'constraints' => [new PositiveOrZero(message: 'Le stock ne peut pas être négatif.')],
            ])
            ->add('featured', CheckboxType::class, [
                'label' => 'Mis en avant',
                'required' => false,                     // case décochée = pas mis en avant
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image',
                'mapped' => false,                       // ce n'est pas une propriété de Product
                'required' => false,                     // en modification, on peut garder l'ancienne
                'constraints' => [
                    new Image(
                        maxSize: '2M',
                        mimeTypesMessage: 'Le fichier doit être une image.',
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
