<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    // 👇 ВАЖЛИВО: Впишіть сюди дані з вашого хостингу InfinityFree
    private string $host = 'sql100.infinityfree.com'; // Знайдіть "MySQL Hostname" у панелі
    private string $db_name = 'if0_40472805_cakeshop'; // Ваша назва БД (зі скріншоту)
    private string $username = 'if0_40472805';        // Ваш "MySQL Username"
    private string $password = 'dcmRXnx3yUO78'; // Ваш пароль від хостингу (vPanel password)
    
    public ?PDO $conn = null;

    public function getConnection(): ?PDO
    {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            // Ми НЕ робимо echo тут, щоб не ламати JSON
            // Ми викидаємо помилку далі, щоб auth.php її зловив
            throw new PDOException("Помилка підключення до БД: " . $exception->getMessage());
        }

        return $this->conn;
    }
}