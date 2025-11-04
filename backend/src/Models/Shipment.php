<?php

namespace App\Models;

class Shipment
{
    public int $id;
    public string $carrier;
    public string $trackingNumber;
    public string $status; 

    public int $orderId;

    public function __construct(int $orderId, string $carrier, string $status = 'pending', string $trackingNumber = '')
    {
        $this->orderId = $orderId;
        $this->carrier = $carrier;
        $this->status = $status;
        $this->trackingNumber = $trackingNumber;
        $this->id = rand(1, 1000);
    }
}

