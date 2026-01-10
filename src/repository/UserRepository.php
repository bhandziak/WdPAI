<?php

require_once 'Repository.php';

class UserRepository extends Repository
{

    public function getAllUsers(): ?array
    {
        $query = $this->database->connect()->prepare(
            'SELECT * FROM users'
        );
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

    public function getUserByName(string $username)
    {
        $query = $this->database->connect()->prepare(
            'SELECT * FROM users WHERE username = :username'
        );

        $query->bindParam(':username', $username);;
        $query->execute();

        $user = $query->fetch(PDO::FETCH_ASSOC);
        $this->database->disconnect();

        return $user;
    }
}
