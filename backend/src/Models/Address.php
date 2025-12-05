<?php

namespace App\Models;

class Address
{
    public int $id;
    public int $userId;
    public string $city;
    public string $street;
    public string $house;
    public ?string $apartment;
    public ?string $floor;

    public function __construct(int $userId, string $city, string $street, string $house, ?string $apartment = null, ?string $floor = null)
    {
        $this->userId = $userId;
        $this->city = $city;
        $this->street = $street;
        $this->house = $house;
        $this->apartment = $apartment;
        $this->floor = $floor;
    }
}