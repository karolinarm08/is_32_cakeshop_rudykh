<?php

use PHPUnit\Framework\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    // Тест 1: Перевірка створення об'єкта користувача
    public function testUserCreation()
    {
        $email = "test@example.com";
        $hash = password_hash("password123", PASSWORD_DEFAULT);
        $name = "TestUser";

        $user = new User($email, $hash, $name);

        $this->assertEquals($email, $user->email);
        $this->assertEquals($name, $user->firstName);
        $this->assertEquals('user', $user->role); // Роль за замовчуванням
    }

    // Тест 2: Перевірка верифікації пароля
    public function testPasswordVerification()
    {
        $password = "secret_pass";
        $hash = User::hashPassword($password);
        
        $user = new User("test@mail.com", $hash, "Name");

        // Правильний пароль має повертати true
        $this->assertTrue($user->verifyPassword($password));
        
        // Неправильний пароль має повертати false
        $this->assertFalse($user->verifyPassword("wrong_pass"));
    }

    // Тест 3: Перевірка методу повного імені
    public function testGetFullName()
    {
        $user = new User("a@b.c", "hash", "Ivan");
        $user->lastName = "Ivanov";

        $this->assertEquals("Ivan Ivanov", $user->getFullName());
    }

    // Тест 4: Перевірка адмінських прав
    public function testIsAdmin()
    {
        $admin = new User("admin@ruby.com", "hash", "Admin", "admin");
        $user = new User("user@ruby.com", "hash", "User", "user");

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
    }
}