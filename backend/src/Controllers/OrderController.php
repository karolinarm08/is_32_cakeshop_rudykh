<?php

namespace App\Controllers;

use App\Services\OrderService;


class OrderController
{
    private OrderService $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

    public function createOrder(array $data)
    {
        echo "Контролер: Створення нового замовлення...\n";

        $order = $this->orderService->createNewOrder($data['userId'], $data['items'], $data['addressId']);

        print_r($order);
    }
}

