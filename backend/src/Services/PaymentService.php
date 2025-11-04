<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;

class PaymentService
{
    public function __construct()
    {

    }

    public function processPayment(Order $order, string $provider): bool
    {
        echo "Сервіс: Обробка платежу на суму $order->total через $provider...\n";

        $payment = new Payment($order->id, $order->total, $provider, 'completed');
        $order->payment = $payment;

        return true;
    }
}

