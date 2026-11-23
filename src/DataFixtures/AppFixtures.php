<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $sweats = [
            ['name' => 'Blackbelt', 'price' => 29.90, 'image' => '1.jpeg', 'featured' => true],
            ['name' => 'BlueBelt', 'price' => 29.90, 'image' => '2.jpeg', 'featured' => false],
            ['name' => 'Street', 'price' => 34.50, 'image' => '3.jpeg', 'featured' => false],
            ['name' => 'Pokeball', 'price' => 45.00, 'image' => '4.jpeg', 'featured' => true],
            ['name' => 'PinkLady', 'price' => 29.90, 'image' => '5.jpeg', 'featured' => false],
            ['name' => 'Snow', 'price' => 32.00, 'image' => '6.jpeg', 'featured' => false],
            ['name' => 'Greyback', 'price' => 28.50, 'image' => '7.jpeg', 'featured' => false],
            ['name' => 'BlueCloud', 'price' => 45.00, 'image' => '8.jpeg', 'featured' => false],
            ['name' => 'BornInUsa', 'price' => 59.90, 'image' => '9.jpeg', 'featured' => true],
            ['name' => 'GreenSchool', 'price' => 42.20, 'image' => '10.jpeg', 'featured' => false],
        ];

        foreach ($sweats as $sweat) {
            $product = new Product();
            $product->setName($sweat['name']);
            $product->setPrice($sweat['price']);
            $product->setImage($sweat['image']);
            $product->setFeatured($sweat['featured']);
            $product->setStockXs(5);
            $product->setStockS(5);
            $product->setStockM(5);
            $product->setStockL(5);
            $product->setStockXl(5);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
