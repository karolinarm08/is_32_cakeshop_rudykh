<?php
// src/Models/Product.php

namespace App\Models;

class Product
{
    public ?int $id;
    public string $name;
    public float $price;
    public string $description;
    public float $weight;
    public int $categoryId;
    public bool $isActive;

    public function __construct(
        string $name,
        float $price,
        string $description,
        float $weight,
        int $categoryId,
        bool $isActive = true,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->description = $description;
        $this->weight = $weight;
        $this->categoryId = $categoryId;
        $this->isActive = $isActive;
    }
}