<?php

namespace App\Models;

class AdminUser
{
    public int $id;
    public string $email;
    public string $passwordHash;
    public string $role;

    public function __construct(string $email, string $passwordHash, string $role = 'admin')
    {
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
        $this->id = rand(1, 100);
    }
}

