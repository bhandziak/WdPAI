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
        $username = $_POST['username'] ?? "";
        $password = $_POST['password'] ?? "";

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
}
