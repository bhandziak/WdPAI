<?php

require_once 'Repository.php';
require_once __DIR__ . './../models/User.php';
require_once __DIR__ . './../mappers/UserMapper.php';

class UserRepository extends Repository
{
    public function getUsersByText(?string $search): ?array
    {
        $conn = $this->database->connect();

        $query = $conn->prepare(
            'SELECT *
             FROM user_data
             WHERE username ILIKE :search
                OR email ILIKE :search
             ORDER BY total_score DESC'
        );

        $query->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        $query->execute();

        $rows = $query->fetchAll(PDO::FETCH_ASSOC);

        $this->database->disconnect();
        return UserMapper::fromRows($rows);
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
            'SELECT create_user(:username, :password_hash, :role, :email, :favorite_genre)'
        );

        $query->execute([
            ':username'       => $username,
            ':password_hash'   => $hashedPassword,
            ':role'           => $role,
            ':email'          => $email,
            ':favorite_genre'  => $favoriteGenre
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
            $data['favorite_genre'] ?? null,
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

    public function changeUserRole(int $userId, string $role): void
    {
        $conn = $this->database->connect();

        $query = $conn->prepare('UPDATE users SET role = :role WHERE id = :id');
        $query->bindParam(':role', $role, PDO::PARAM_STR);
        $query->bindParam(':id', $userId, PDO::PARAM_INT);

        $query->execute();

        $this->database->disconnect();
    }
}
