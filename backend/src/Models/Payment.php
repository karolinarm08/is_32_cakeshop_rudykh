<?php

namespace App\Models;

class Payment
{
    public int $id;
    public string $provider;
    public string $status;
    public float $amount;

    public int $orderId;

    public function __construct(int $orderId, float $amount, string $provider, string $status = 'pending')
    {
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->provider = $provider;
        $this->status = $status;
        $this->id = rand(1, 1000);
    }
}

