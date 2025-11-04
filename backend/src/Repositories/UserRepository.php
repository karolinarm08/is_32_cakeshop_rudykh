<?php

namespace App\Repositories;

use App\Models\User;


class UserRepository
{
    public function __construct()
    {
    }

    public function findById(int $id): ?User
    {
        echo "Репозиторій: Пошук User з ID: $id\n";
        

        if (!$id) {
            return null;
        }
        return new User("user@example.com", "fake_hash");
    }


    public function findByEmail(string $email): ?User
    {
        echo "Репозиторій: Пошук User з Email: $email\n";

        return null; 
    }


    public function save(User $user): bool
    {
        echo "Репозиторій: Збереження User (Email: $user->email) в БД...\n";
        
 
        
        return true;
    }
}

