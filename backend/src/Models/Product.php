<?php

namespace App\Models;

class Product
{
    public int $id;
    public string $name;
    public float $price;
    public string $description;
    public bool $isActive;

   
    public int $categoryId;
    public array $images = [];  

    public function __construct(string $name, float $price, string $description, int $categoryId, bool $isActive = true)
    {
        $this->name = $name;
        $this->price = $price;
        $this->description = $description;
        $this->categoryId = $categoryId;
        $this->isActive = $isActive;
        $this->id = rand(1, 1000);
    }
}

