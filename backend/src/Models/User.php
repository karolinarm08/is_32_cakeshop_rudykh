<?php

namespace App\Models;

class User
{
    public int $id;
    public string $email;
    public string $passwordHash;
    public ?string $firstName;
    public ?string $lastName;
    public ?string $phone;
    public string $role;

    public function __construct(string $email, string $passwordHash, ?string $firstName = null, string $role = 'user')
    {
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->firstName = $firstName;
        $this->role = $role;
    }
}