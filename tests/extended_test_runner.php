<?php

header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$root = dirname(__DIR__); 

function requireModel($path) {
    if (file_exists($path)) { require_once $path; return true; }
    echo "<div style='color:red'>Файл не знайдено: $path</div>"; return false;
}

requireModel($root . '/backend/src/Models/User.php');
requireModel($root . '/backend/src/Models/Cart.php');
requireModel($root . '/backend/src/Models/CartItem.php');
requireModel($root . '/backend/src/Models/Product.php');
requireModel($root . '/backend/src/Models/Order.php');
requireModel($root . '/backend/src/Models/Address.php');

use App\Models\User;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Order;
use App\Models\Address;

echo "<html><head><title>Full Unit Tests</title>";
echo "<style>
    body { font-family: 'Segoe UI', sans-serif; padding: 20px; background: #f8f9fa; color: #333; }
    h1 { color: #2c3e50; }
    .section { background: white; padding: 20px; border-radius: 8px; margin-bottom: 25px; border-left: 5px solid #7F4B93; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    h2 { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 0; color: #7F4B93; }
    .test-case { margin-bottom: 8px; border-bottom: 1px dashed #eee; padding-bottom: 8px; }
    .pass { color: #27ae60; font-weight: bold; float: right; }
    .fail { color: #c0392b; font-weight: bold; float: right; }
    .debug { font-size: 0.85em; color: #7f8c8d; margin-left: 20px; display: block; margin-top: 4px; }
</style></head><body>";

echo "<h1>Розширене тестування системи Cake Shop</h1>";

echo "<div class='section'><h2>1. User & Security (Безпека)</h2>";

runTest("User: статичний метод hashPassword створює хеш", function() {
    $rawPassword = "SecretPassword123";
    $hash = User::hashPassword($rawPassword);
    
    if ($hash === $rawPassword) throw new Exception("Пароль не захешовано!");
    if (empty($hash)) throw new Exception("Хеш пустий!");
    
    return "Хеш згенеровано: " . substr($hash, 0, 15) . "...";
});

runTest("User: verifyPassword приймає правильний пароль", function() {
    $pass = "MySecret";
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $user = new User("test@mail.com", $hash, "TestUser");
    
    if (!$user->verifyPassword($pass)) {
        throw new Exception("Метод відхилив правильний пароль.");
    }
});

runTest("User: verifyPassword відхиляє неправильний пароль", function() {
    $user = new User("test@mail.com", password_hash("RealPass", PASSWORD_DEFAULT), "TestUser");
    
    if ($user->verifyPassword("WrongPass")) {
        throw new Exception("Метод прийняв неправильний пароль! (Критична помилка)");
    }
});

runTest("User: перевірка ролей (Admin vs User)", function() {
    $admin = new User("a@a.com", "hash", "Admin", "admin");
    $user = new User("u@u.com", "hash", "User", "user");
    
    if (!$admin->isAdmin()) throw new Exception("Адмін не розпізнаний.");
    if ($user->isAdmin()) throw new Exception("Звичайний юзер отримав права адміна.");
});
echo "</div>";

echo "<div class='section'><h2>2. Модель Order (Замовлення)</h2>";

runTest("Order: перевірка дефолтного статусу 'new'", function() {
    $order = new Order(1, 550.00);
    
    assertEquals('new', $order->status);
    assertEqualsFloat(550.00, $order->total);
});

runTest("Order: створення дати замовлення", function() {
    $order = new Order(1, 100.00);
    
    if (!($order->createdAt instanceof DateTime)) {
        throw new Exception("createdAt не є об'єктом DateTime");
    }
    $now = new DateTime();
    if ($order->createdAt->format('Y-m-d') !== $now->format('Y-m-d')) {
        throw new Exception("Дата замовлення не співпадає з поточною");
    }
});
echo "</div>";

echo "<div class='section'><h2>3. Cart & Calculations (Фінанси)</h2>";

runTest("Cart: порожній кошик = 0 грн", function() {
    $cart = new Cart(99);
    $total = $cart->getTotalPrice();
    assertEqualsFloat(0.0, $total);
});

runTest("Cart: складний розрахунок (кілька товарів з копійками)", function() {
    $cart = new Cart(99);
    
    $item1 = new CartItem($cart->id, 1, 3, 33.33);
    $item2 = new CartItem($cart->id, 2, 1, 0.01);
    
    $cart->items[] = $item1;
    $cart->items[] = $item2;
    
    $expected = 100.00;
    $actual = $cart->getTotalPrice();
    
    assertEqualsFloat($expected, $actual);
});
echo "</div>";

echo "<div class='section'><h2>4. Модель Address</h2>";

runTest("Address: створення об'єкта та nullable поля", function() {
    $addr = new Address(5, "Kyiv", "Khreshchatyk", "1");
    
    if ($addr->city !== "Kyiv") throw new Exception("Місто не збереглося");
    if ($addr->apartment !== null) throw new Exception("Квартира має бути null за замовчуванням (якщо не передана)");
    
    $addrFull = new Address(5, "Dnipro", "Polya", "2", "15B", "3");
    if ($addrFull->floor !== "3") throw new Exception("Поверх не зберігся");
    
    return true;
});
echo "</div>";

echo "</body></html>";

function runTest($name, $callback) {
    echo "<div class='test-case'><strong>$name</strong> ... ";
    try {
        $result = $callback();
        echo "<span class='pass'>Тест пройдено</span>";
        if (is_string($result)) echo "<span class='debug'>Info: $result</span>";
    } catch (Exception $e) {
        echo "<span class='fail'>Тест не пройдено</span>";
        echo "<span class='debug' style='color:red'>" . $e->getMessage() . "</span>";
    }
    echo "</div>";
}

function assertEquals($expected, $actual) {
    if ($expected !== $actual) {
        throw new Exception("Очікувалось: '$expected', Отримано: '$actual'");
    }
}

function assertEqualsFloat($expected, $actual, $delta = 0.001) {
    if (abs($expected - $actual) > $delta) {
        throw new Exception("Очікувалось: $expected, Отримано: $actual (Різниця > $delta)");
    }
}
?>