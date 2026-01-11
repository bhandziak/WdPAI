<?php

require_once 'Repository.php';
require_once __DIR__ . './../models/User.php';

class UserRepository extends Repository
{

    public function getAllUsersDetails(): ?array
    {
        $conn = $this->database->connect();

        $query = $conn->prepare('SELECT * FROM user_data ORDER BY total_score DESC');
        $query->execute();

        $users = $query->fetchAll(PDO::FETCH_ASSOC);

        $this->database->disconnect();
        return $users;
    }

    public function createUser(
        string $username,
        string $hashedPassword,
        ?string $email = null,
        ?string $favoriteGenre = null,
        string $role = 'user'
    ) {
        $conn = $this->database->connect();

        $query = $conn->prepare(
            'SELECT create_user(:username, :passwordHash, :role, :email, :favoriteGenre)'
        );

        $query->execute([
            ':username'       => $username,
            ':passwordHash'   => $hashedPassword,
            ':role'           => $role,
            ':email'          => $email,
            ':favoriteGenre'  => $favoriteGenre
        ]);

        $this->database->disconnect();
    }

    public function getUserDetailsByName(string $username): ?User
    {
        $conn = $this->database->connect();

        $query = $conn->prepare('SELECT * FROM user_data WHERE username = :username');
        $query->bindParam(':username', $username);
        $query->execute();

        $data = $query->fetch(PDO::FETCH_ASSOC);
        $this->database->disconnect();

        if (!$data) {
            return null;
        }

        return new User(
            (int)$data['user_id'],
            $data['username'],
            $data['role'],
            $data['email'] ?? null,
            $data['favoriteGenre'] ?? null,
            (int)$data['total_score']
        );
    }

    public function getUserByName(string $username)
    {
        $conn = $this->database->connect();

        $query = $conn->prepare('SELECT * FROM users WHERE username = :username');
        $query->bindParam(':username', $username);
        $query->execute();

        $user = $query->fetch(PDO::FETCH_ASSOC);
        $this->database->disconnect();

        return $user;
    }
}
