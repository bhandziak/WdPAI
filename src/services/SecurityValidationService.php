<?php

require_once __DIR__ . '/../repository/UserRepository.php';

class SecurityValidationService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function validateLogin(array $input): array
    {
        $username = trim($input['username'] ?? '');
        $password = trim($input['password'] ?? '');

        if (empty($username) || empty($password)) {
            throw new InvalidArgumentException('Fill all fields');
        }

        $userRow = $this->userRepository->getUserByName($username);

        if (!$userRow) {
            throw new InvalidArgumentException('Wrong password or username');
        }

        if (!password_verify($password, $userRow['password_hash'])) {
            throw new InvalidArgumentException('Wrong password or username');
        }

        return $userRow;
    }

    public function validateRegistration(array $input): array
    {
        $username = trim($input['username'] ?? '');
        $password = trim($input['password'] ?? '');
        $password2 = trim($input['password2'] ?? '');
        $email = trim($input['email'] ?? '');
        $favoriteGenre = $input['favorite_genre'] ?? null;

        if (empty($username) || empty($password) || empty($password2)) {
            throw new InvalidArgumentException('Fill all required fields');
        }

        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long');
        }

        if ($password !== $password2) {
            throw new InvalidArgumentException('Passwords are not the same');
        }

        $existingUser = $this->userRepository->getUserByName($username);
        if ($existingUser) {
            throw new InvalidArgumentException('Username already taken');
        }

        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address');
        }

        return [
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'favorite_genre' => $favoriteGenre
        ];
    }
}
