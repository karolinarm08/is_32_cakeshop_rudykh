<?php

namespace App\Models;

class User
{
    public int $id;
    public string $email;
    public string $passwordHash;
    public string $role;

    public function __construct(string $email, string $passwordHash, int $id = 0, string $role = 'user')
    {
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->id = $id;
        $this->role = $role;
    }
}