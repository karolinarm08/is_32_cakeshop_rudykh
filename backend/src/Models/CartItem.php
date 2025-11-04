<?php

namespace App\Models;

class CartItem
{
    public int $id;
    public int $qty;
    public float $unitPrice;

    public int $cartId;
    public int $productId;

    public function __construct(int $cartId, int $productId, int $qty, float $unitPrice)
    {
        $this->cartId = $cartId;
        $this->productId = $productId;
        $this->qty = $qty;
        $this->unitPrice = $unitPrice;
        $this->id = rand(1, 10000);
    }
}

