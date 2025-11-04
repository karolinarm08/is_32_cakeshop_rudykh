<?php

namespace App\Controllers;

use App\Repositories\ProductRepository;


class ProductController
{
    private ProductRepository $productRepository;

    public function __construct()
    {
        $this->productRepository = new ProductRepository();
    }

    public function getAllProducts()
    {
        echo "Контролер: Отримання всіх товарів...\n";
        $products = $this->productRepository->findAll();
        
        print_r($products);
    }


    public function getProductById(int $id)
    {
        echo "Контролер: Отримання товару з ID: $id\n";
        $product = $this->productRepository->findById($id);

        print_r($product);
    }
}

