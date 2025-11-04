<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    public function __construct()
    {
        // Підключення до БД
    }

    public function findById(int $id): ?Product
    {
        echo "Репозиторій: Пошук Product з ID: $id\n";
        
        if ($id > 0) {
            return new Product("Фейковий Торт", 150.00, "Опис фейкового торта", 1);
        }
        return null;
    }

    public function findAll(): array
    {
        echo "Репозиторій: Пошук всіх Product...\n";

        return [
            new Product("Торт 'Наполеон'", 250.00, "Класичний торт", 1),
            new Product("Тістечко 'Шу'", 50.00, "З ванільним кремом", 2),
        ];
    }
}

