<?php

namespace App\Models;

class Image
{
    public int $id;
    public string $url;
    public string $alt;

    public int $productId;

    public function __construct(int $productId, string $url, string $alt)
    {
        $this->productId = $productId;
        $this->url = $url;
        $this->alt = $alt;
        $this->id = rand(1, 10000);
    }
}

