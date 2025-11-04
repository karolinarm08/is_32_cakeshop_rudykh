<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository
{
    public function __construct()
    {
        // Підключення до БД
    }

    public function save(Order $order): bool
    {
        echo "Репозиторій: Збереження Order (ID: $order->id) в БД...\n";

        return true;
    }

    public function findByUserId(int $userId): array
    {
        echo "Репозиторій: Пошук всіх Order для User ID: $userId\n";
        
        return [
            new Order($userId, 150.00, 'delivered'),
            new Order($userId, 320.50, 'shipped')
        ];
    }
}

