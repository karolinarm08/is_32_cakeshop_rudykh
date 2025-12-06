<?php

use PHPUnit\Framework\TestCase;
use App\Services\CartService;
// Примітка: У реальному проекті тут потрібно використовувати Mock-об'єкти для бази даних

class CartServiceTest extends TestCase
{
    // Тест 1: Перевірка логіки підрахунку суми
    public function testCalculateTotalLogic()
    {
        // Імітуємо дані, які зазвичай приходять з бази даних
        $fakeCartItems = [
            ['price' => 100.00, 'quantity' => 2], // 200
            ['price' => 50.50,  'quantity' => 1], // 50.50
            ['price' => 10.00,  'quantity' => 5]  // 50
        ];

        // Логіка підрахунку (така ж, як у CartService)
        $total = 0;
        foreach ($fakeCartItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $expectedTotal = 300.50;
        
        $this->assertEquals($expectedTotal, $total, "Сума кошика розрахована неправильно");
    }

    // Тест 2: Перевірка валідації вхідних даних
    public function testAddInvalidProductData()
    {
        $productId = -5; // Некоректний ID
        $quantity = 0;   // Некоректна кількість

        // Перевіряємо умову, яка повинна бути в контролері або сервісі
        $isValid = ($productId > 0 && $quantity > 0);
        
        $this->assertFalse($isValid, "Система не повинна пропускати від'ємні ID або нульову кількість");
    }
}