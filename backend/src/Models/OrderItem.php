<?php

namespace App\Models;

class OrderItem
{
    public int $id;
    public int $qty;
    public float $unitPrice;

    public int $orderId;
    public int $productId;

    public function __construct(int $orderId, int $productId, int $qty, float $unitPrice)
    {
        $this->orderId = $orderId;
        $this->productId = $productId;
        $this->qty = $qty;
        $this->unitPrice = $unitPrice;
        $this->id = rand(1, 10000);
    }
}

