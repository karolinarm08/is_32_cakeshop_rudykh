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

    public function createNewOrder(int $userId, array $cartItems, int $addressId = null): array
    {
        $total = 0;
        $orderItems = [];

        // Розрахунок суми та підготовка товарів
        foreach ($cartItems as $item) {
            $product = $this->productRepository->findById($item['product_id']);
            if ($product) {
                $total += $product->price * $item['qty'];
                $orderItems[] = [
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'price' => $product->price
                ];
            }
        }

        if (empty($orderItems)) {
            return ['success' => false, 'message' => 'Кошик порожній'];
        }

        $order = new Order($userId, $total, 'new');
        
        // Збереження замовлення в БД
        $orderId = $this->orderRepository->save($order);
        
        if ($orderId) {
            // Збереження товарів замовлення
            $this->orderRepository->addItems($orderId, $orderItems);
            
            // Імітація надсилання Email
            $this->sendOrderConfirmationEmail($userId, $orderId);

            return ['success' => true, 'order_id' => $orderId, 'total' => $total];
        }

        return ['success' => false, 'message' => 'Помилка створення замовлення'];
    }
    
    private function sendOrderConfirmationEmail($userId, $orderId) {
        // Тут має бути логіка PHPMailer або mail()
        // mail($userEmail, "Замовлення #$orderId підтверджено", "Дякуємо за покупку!");
    }
}