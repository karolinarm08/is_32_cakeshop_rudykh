<?php

namespace App\Repositories;

use App\Models\Address;
use App\Config\Database;
use PDO;

class AddressRepository
{
    private PDO $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function findByUserId(int $userId): ?Address
    {
        $query = "SELECT * FROM addresses WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $address = new Address(
                $row['user_id'], 
                $row['city'], 
                $row['street'], 
                $row['house'], 
                $row['apartment'], 
                $row['floor']
            );
            $address->id = $row['id'];
            return $address;
        }
        return null;
    }

    public function save(Address $address): bool
    {
        $existing = $this->findByUserId($address->userId);

        if ($existing) {
            $query = "UPDATE addresses SET city = :city, street = :street, house = :house, apartment = :apart, floor = :floor WHERE user_id = :uid";
        } else {
            $query = "INSERT INTO addresses (user_id, city, street, house, apartment, floor) VALUES (:uid, :city, :street, :house, :apart, :floor)";
        }

        $stmt = $this->conn->prepare($query);

        $city = htmlspecialchars(strip_tags($address->city));
        $street = htmlspecialchars(strip_tags($address->street));
        $house = htmlspecialchars(strip_tags($address->house));
        $apart = htmlspecialchars(strip_tags($address->apartment ?? ''));
        $floor = htmlspecialchars(strip_tags($address->floor ?? ''));

        $stmt->bindParam(':uid', $address->userId);
        $stmt->bindParam(':city', $city);
        $stmt->bindParam(':street', $street);
        $stmt->bindParam(':house', $house);
        $stmt->bindParam(':apart', $apart);
        $stmt->bindParam(':floor', $floor);

        return $stmt->execute();
    }
}