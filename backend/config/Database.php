<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private string $host = 'sql100.infinityfree.com';
    private string $db_name = 'if0_40472805_cakeshop'; 
    private string $username = 'if0_40472805'; 
    private string $password = 'dcmRXnx3yUO78'; 
    
    public ?PDO $conn = null;

    public function getConnection(): ?PDO
    {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
            $this->conn->exec("SET time_zone = '+02:00'");
        } catch (PDOException $exception) {
            throw new PDOException("Помилка підключення до БД: " . $exception->getMessage());
        }

        return $this->conn;
    }
}