<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;

class OrderService
{
    private OrderRepository $orderRepository;
    private ProductRepository $productRepository;
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->productRepository = new ProductRepository();
        $this->userRepository = new UserRepository();
    }

    public function createNewOrder(int $userId, array $cartItems, int $addressId = null): array
    {
        $total = 0;
        $orderItems = [];

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

        $orderId = $this->orderRepository->createOrder($userId, $total, $addressId);
        
        if ($orderId) {
            $this->orderRepository->addItems($orderId, $orderItems);
            
            // Відправка email про створення
            $this->sendOrderEmail($userId, $orderId, $total, 'new');

            return [
                'success' => true, 
                'order_id' => $orderId, 
                'total' => $total,
                'message' => 'Замовлення успішно створено!'
            ];
        }

        return ['success' => false, 'message' => 'Помилка створення замовлення'];
    }

    // --- МЕТОДИ ДЛЯ АДМІНА ---

    public function getAllOrders(): array
    {
        $orders = $this->orderRepository->findAllOrders();
        // Додаємо список товарів до кожного замовлення
        foreach ($orders as &$order) {
            $order['items'] = $this->orderRepository->getOrderItems($order['id']);
        }
        return ['success' => true, 'orders' => $orders];
    }

    public function changeStatus(int $orderId, string $newStatus): array
    {
        $allowedStatuses = ['new', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($newStatus, $allowedStatuses)) {
            return ['success' => false, 'message' => 'Невірний статус'];
        }

        if ($this->orderRepository->updateStatus($orderId, $newStatus)) {
            // Знаходимо замовлення, щоб отримати ID користувача
            $order = $this->orderRepository->findById($orderId);
            if ($order) {
                // Відправляємо лист про зміну статусу
                $this->sendOrderEmail($order->user_id, $orderId, $order->total_price, $newStatus);
            }
            
            return ['success' => true, 'message' => "Статус замовлення #$orderId змінено на '$newStatus'"];
        }
        
        return ['success' => false, 'message' => 'Помилка оновлення статусу'];
    }

    // --- Відправка E-mail ---
    private function sendOrderEmail($userId, $orderId, $total, $status) {
        $user = $this->userRepository->findById($userId);
        if ($user) {
            $to = $user->email;
            $subject = "Замовлення #$orderId - Оновлення статусу";
            
            $statusText = match($status) {
                'new' => 'Нове (очікує обробки)',
                'processing' => 'В обробці (готуємо ваше замовлення)',
                'shipped' => 'Відправлено (прямує до вас)',
                'delivered' => 'Доставлено (смачного!)',
                'cancelled' => 'Скасовано',
                default => $status
            };

            $message = "Вітаємо, {$user->firstName}!\n\n";
            $message .= "Статус вашого замовлення #$orderId оновлено.\n";
            $message .= "Новий статус: $statusText.\n";
            if ($status === 'new') {
                $message .= "Сума до сплати: $total грн.\n";
            }
            $message .= "\nДякуємо, що обрали RUBY Cake Shop!";

            $headers = "From: no-reply@cakeshop.great-site.net\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            // Функція mail() може не працювати на локальному сервері без налаштування SMTP,
            // але на реальному хостингу повинна працювати.
            @mail($to, $subject, $message, $headers);
        }
    }
}