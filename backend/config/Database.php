<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private $host = 'sql100.infinityfree.com';
    private $db_name = 'if0_40472805_cakeshop';
    private $username = 'if0_40472805'; // Ваше ім'я користувача БД
    private $password = 'dcmRXnx3yUO78';     // Ваш пароль БД
    private $charset = 'utf8mb4'; // Рекомендоване кодування
    public $conn;

    public function getConnection(): ?PDO
    {
        $this->conn = null;

        $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $exception) {
            echo "Помилка підключення: " . $exception->getMessage();
        }

        return $this->conn;
    }
}