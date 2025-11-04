<?php

namespace App\Models;

class Category
{
    public int $id;
    public string $name;
    public string $slug;

    public array $products = [];

    public function __construct(string $name, string $slug)
    {
        $this->name = $name;
        $this->slug = $slug;
        $this->id = rand(1, 100);
    }
}

