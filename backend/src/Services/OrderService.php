<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;

class OrderService
{
    private OrderRepository $orderRepository;
    private ProductRepository $productRepository;
    private PaymentService $paymentService;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->productRepository = new ProductRepository();
        $this->paymentService = new PaymentService();
    }

    public function createNewOrder(int $userId, array $items, int $addressId): ?Order
    {
        echo "Сервіс: Створення замовлення для User ID: $userId\n";

        $total = 199.99; 

        $order = new Order($userId, $total);

        
        $paymentSuccess = $this->paymentService->processPayment($order, 'LiqPay');

        if ($paymentSuccess) {
            echo "Сервіс: Оплата пройшла успішно.\n";
            $order->status = 'processing';
            
            return $order;
        } else {
            echo "Сервіс: Оплата не вдалася.\n";
            return null;
        }
    }
}

