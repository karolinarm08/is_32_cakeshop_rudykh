<?php

namespace App\Models;

class Order
{
    public int $id;
    public string $status;
    public float $total;
    public \DateTime $createdAt;

    public int $userId;
    public array $items = [];
    public ?Payment $payment = null;
    public ?Shipment $shipment = null;

    public function __construct(int $userId, float $total, string $status = 'new')
    {
        $this->userId = $userId;
        $this->total = $total;
        $this->status = $status;
        $this->createdAt = new \DateTime();
        $this->id = rand(1, 1000);
    }
}

