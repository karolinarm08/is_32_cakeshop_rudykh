<?php

namespace App\Models;

class CartItem
{
    public int $id;
    public int $qty;
    public float $unitPrice;

    public int $cartId;
    public int $productId;

    // Оновлений конструктор для коректної роботи з БД
    public function __construct(int $cartId, int $productId, int $qty, float $unitPrice, ?int $id = null)
    {
        $this->cartId = $cartId;
        $this->productId = $productId;
        $this->qty = $qty;
        $this->unitPrice = $unitPrice;
        // Встановлюємо ID, якщо він переданий (при завантаженні з БД)
        if ($id !== null) {
            $this->id = $id;
        }
    }
}