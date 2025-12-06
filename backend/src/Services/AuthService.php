<?php

namespace App\Services;

use App\Models\User;
use App\Models\Address;
use App\Repositories\UserRepository;
use App\Repositories\AddressRepository;

class AuthService
{
    private UserRepository $userRepository;
    private AddressRepository $addressRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->addressRepository = new AddressRepository();
    }

    public function registerUser(string $email, string $password, string $name): array
    {
        if ($this->userRepository->findByEmail($email)) {
            return ['success' => false, 'message' => 'Користувач вже існує'];
        }
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $user = new User($email, $passwordHash, $name);

        if ($this->userRepository->save($user)) {
            return ['success' => true, 'message' => 'Реєстрація успішна'];
        }
        return ['success' => false, 'message' => 'Помилка БД'];
    }

    public function loginUser(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user || !password_verify($password, $user->passwordHash)) {
            return ['success' => false, 'message' => 'Невірний логін або пароль'];
        }
        return $this->formatUserData($user, 'Вхід успішний');
    }

    public function getUserProfile(string $email): array
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) return ['success' => false, 'message' => 'Користувача не знайдено'];
        
        return $this->formatUserData($user, 'Дані отримано');
    }

    public function updateUserData(string $email, string $fname, string $lname, string $phone): array
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) return ['success' => false, 'message' => 'Користувача не знайдено'];

        $user->firstName = $fname;
        $user->lastName = $lname;
        $user->phone = $phone;

        if ($this->userRepository->update($user)) {
            return ['success' => true, 'message' => 'Дані оновлено'];
        }
        return ['success' => false, 'message' => 'Помилка оновлення'];
    }

    public function updateUserAddress(string $email, array $addrData): array
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) return ['success' => false, 'message' => 'Користувача не знайдено'];

        $address = new Address(
            $user->id,
            $addrData['city'],
            $addrData['street'],
            $addrData['house'],
            $addrData['apartment'] ?? null,
            $addrData['floor'] ?? null
        );

        if ($this->addressRepository->save($address)) {
            return ['success' => true, 'message' => 'Адресу збережено'];
        }
        return ['success' => false, 'message' => 'Помилка збереження адреси'];
    }

    private function formatUserData(User $user, string $msg): array
    {
        $address = $this->addressRepository->findByUserId($user->id);
        
        return [
            'success' => true, 
            'message' => $msg, 
            'user' => [
                'id' => $user->id,
                'email' => $user->email, 
                'name' => $user->firstName,
                'lastName' => $user->lastName,
                'phone' => $user->phone,
                'role' => $user->role,
                'address' => $address ? [
                    'city' => $address->city,
                    'street' => $address->street,
                    'house' => $address->house,
                    'apartment' => $address->apartment,
                    'floor' => $address->floor
                ] : null
            ]
        ];
    }
}