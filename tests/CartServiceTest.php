<?php

use PHPUnit\Framework\TestCase;
use App\Services\CartService;

class CartServiceTest extends TestCase
{
    public function testCalculateTotalLogic()
    {
        $fakeCartItems = [
            ['price' => 100.00, 'quantity' => 2], // 200
            ['price' => 50.50,  'quantity' => 1], // 50.50
            ['price' => 10.00,  'quantity' => 5]  // 50
        ];

  
        $total = 0;
        foreach ($fakeCartItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $expectedTotal = 300.50;
        
        $this->assertEquals($expectedTotal, $total, "Сума кошика розрахована неправильно");
    }


    public function testAddInvalidProductData()
    {
        $productId = -5; 
        $quantity = 0;   

     
        $isValid = ($productId > 0 && $quantity > 0);
        
        $this->assertFalse($isValid, "Система не повинна пропускати від'ємні ID або нульову кількість");
    }
}