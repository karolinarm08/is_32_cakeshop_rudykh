<?php

namespace App\Models;

class Cart
{
    public int $id;
    public \DateTime $createdAt;

    public int $userId;
    public array $items = [];

    public function __construct(int $userId)
    {
        $this->userId = $userId;
        $this->createdAt = new \DateTime();
        $this->id = rand(1, 1000);
    }

    public function getTotalPrice(): float
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->unitPrice * $item->qty;
        }
        return $total;
    }
}

