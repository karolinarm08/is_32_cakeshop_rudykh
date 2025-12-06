<?php

namespace App\Controllers;

use App\Services\OrderService;
use App\Services\CartService;
use App\Repositories\UserRepository;
use App\Repositories\CartRepository;
use App\Repositories\AddressRepository;
use App\Models\Address;

class OrderController
{
    private OrderService $orderService;
    private CartService $cartService;

    public function __construct()
    {
        $this->orderService = new OrderService();
        $this->cartService = new CartService();
    }

    public function create(array $data)
    {
        $email = $data['email'] ?? '';
        if (empty($email)) { echo json_encode(['success' => false, 'message' => 'Необхідна авторизація']); return; }

        $cartResult = $this->cartService->getUserCart($email);
        if (!$cartResult['success'] || empty($cartResult['items'])) {
            echo json_encode(['success' => false, 'message' => 'Кошик порожній']); return;
        }

        $userRepository = new UserRepository();
        $user = $userRepository->findByEmail($email);
        if (!$user) { echo json_encode(['success' => false, 'message' => 'Користувача не знайдено']); return; }

        $addressId = null;
        if (isset($data['deliveryAddress'])) {
            $addrData = $data['deliveryAddress'];
            $addressRepo = new AddressRepository();
            $address = new Address($user->id, $addrData['city'] ?? '', $addrData['street'] ?? '', $addrData['house'] ?? '', $addrData['apartment'] ?? null, $addrData['floor'] ?? null);
            if ($addressRepo->save($address)) {
                $savedAddr = $addressRepo->findByUserId($user->id);
                if ($savedAddr) $addressId = $savedAddr->id;
            }
        }

        $cartItems = [];
        foreach ($cartResult['items'] as $item) {
            $cartItems[] = ['product_id' => $item['product_id'], 'qty' => $item['quantity']];
        }

        $orderResult = $this->orderService->createNewOrder($user->id, $cartItems, $addressId);

        if ($orderResult['success']) {
            $cartRepository = new CartRepository();
            $cart = $cartRepository->findCartByUserId($user->id);
            if ($cart) $cartRepository->clearCart($cart['id']);
        }

        echo json_encode($orderResult);
    }

    public function getHistory(array $data)
    {
        $email = $data['email'] ?? '';
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Необхідна авторизація (email missing)']);
            return;
        }

        $userRepository = new UserRepository();
        $user = $userRepository->findByEmail($email);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Користувача не знайдено в базі']);
            return;
        }

        echo json_encode($this->orderService->getUserOrders($user->id));
    }

    public function getAll(array $data)
    {
        if (!$this->isAdmin($data['admin_email'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Доступ заборонено.']); return;
        }
        echo json_encode($this->orderService->getAllOrders());
    }

    public function updateStatus(array $data)
    {
        if (!$this->isAdmin($data['admin_email'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Доступ заборонено.']); return;
        }
        $orderId = $data['order_id'] ?? 0;
        $status = $data['status'] ?? '';
        echo json_encode($this->orderService->changeStatus((int)$orderId, $status));
    }

    private function isAdmin(string $email): bool
    {
        if (empty($email)) return false;
        $userRepo = new UserRepository();
        $user = $userRepo->findByEmail($email);
        return ($user && $user->role === 'admin');
    }
}