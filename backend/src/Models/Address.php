<?php

namespace App\Models;

class Address
{
    public int $id;
    public string $line1;
    public string $city;
    public int $zip;
    public bool $isDefault;

    public int $userId; 

    public function __construct(int $userId, string $line1, string $city, int $zip, bool $isDefault = false)
    {
        $this->userId = $userId;
        $this->line1 = $line1;
        $this->city = $city;
        $this->zip = $zip;
        $this->isDefault = $isDefault;
        $this->id = rand(1, 10000);
    }
}

