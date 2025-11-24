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

    // TODO implement add user method
    public function addUser(
        $username, $password, $email, $firstname, $lastname, $bio
        ) : bool {
        $query = $this->database->connect()->prepare(
            'INSERT INTO users
            (username, password, email, firstname, lastname, bio)
            VALUES 
            (:username, :password, :email, :firstname, :lastname, :bio)'
        );
        $query->bindParam(':username', $username);
        $query->bindParam(':password', password_hash($password, PASSWORD_BCRYPT));
        $query->bindParam(':email', $email);
        $query->bindParam(':firstname', $firstname);
        $query->bindParam(':lastname', $lastname);
        $query->bindParam(':bio', $bio);

        $this->database->disconnect();
        return $query->execute();
    }
}