<?php

require_once 'Repository.php';

class UserRepository extends Repository {

    public function getAllUsers() : ?array{
        $query = $this->database->connect()->prepare(
            'SELECT * FROM users'
        );
        $query->execute();

        $users = $query->fetchAll(PDO::FETCH_ASSOC);

        $this->database->disconnect();
        return $users;
    }

    public function createUser(
        string $email, string $hashedPassword,
        string $firstName, string $lastName,
        string $bio = ""
    ){
        $query = $this->database->connect()->prepare(
            'INSERT INTO users (firstname, lastname, email, password, bio)
             VALUES (?, ?, ?, ?, ?)'
        );

        $query->execute([
            $firstName,
            $lastName,
            $email,
            $hashedPassword,
            $bio
        ]);

        // $query = $this->database->connect()->prepare(
        //     'INSERT INTO users (firstname, lastname, email, password, bio, enabled)
        //      VALUES (:firstName, :lastName, :email, :password, :bio, TRUE)'
        // );

        // $query->bindParam(':firstName', $firstName);
        // $query->bindParam(':lastName', $lastName);
        // $query->bindParam(':email', $email);
        // $query->bindParam(':password', $hashedPassword);
        // $query->bindParam(':bio', $bio);

        // $query->execute();

        $this->database->disconnect();
    }

    public function getUserByEmail(string $email){
        $query = $this->database->connect()->prepare(
            'SELECT * FROM users WHERE email = :email'
        );

        $query->bindParam(':email', $email);;
        $query->execute();
        
        $user = $query->fetch(PDO::FETCH_ASSOC);
        $this->database->disconnect();
        
        return $user;
    }
}