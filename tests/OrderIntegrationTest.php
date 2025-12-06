<?php

use PHPUnit\Framework\TestCase;

// Цей тест імітує реальні HTTP-запити до вашого API
// Для його роботи потрібен запущений локальний сервер
class OrderIntegrationTest extends TestCase
{
    private $baseUrl = 'http://localhost/is_32_cakeshop_rudykh/backend/api';

    // Тест 1: Перевірка доступності списку товарів
    public function testGetProductList()
    {
        $ch = curl_init($this->baseUrl . '/../product_api.php?action=list');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // API повинно відповідати кодом 200
        $this->assertEquals(200, $httpCode, "API повинно повертати 200 OK");

        // Відповідь має бути валідним JSON
        $data = json_decode($response, true);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }

    // Тест 2: Спроба створити замовлення без email (має бути помилка)
    public function testCreateOrderWithoutAuth()
    {
        $payload = json_encode([
            'userData' => ['name' => 'Test'],
            'cartItems' => []
            // email пропущено навмисно
        ]);

        $ch = curl_init($this->baseUrl . '/order.php?action=create');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        
        $response = curl_exec($ch);
        $data = json_decode($response, true);

        // Очікуємо success: false
        $this->assertFalse($data['success'] ?? true, "API має відхилити запит без email"); 
    }
}