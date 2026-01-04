<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class SecurityController extends AppController
{

    private $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }


    public function login()
    {
        // nazewnictwo - early return
        if (!$this->isPost()) {
            return $this->render('login');
        }

        // TODO get data from login form
        // check if user exists in database
        // render dashboard view if success authentication

        $username = $_POST['username'] ?? "";
        $password = $_POST['password'] ?? "";

        if (empty($username) || empty($password)) {
            return $this->render('login', ['messages' => 'Fill all fields']);
        }

        $userRow = $this->userRepository->getUserByName($username);

        if (!$userRow) {
            return $this->render('login', ['messages' => 'User not found']);
        }

        if (!password_verify($password, $userRow['passwordhash'])) {
            return $this->render('login', ['messages' => 'Wrong password']);
        }

        // TODO
        // create user session
        // cookie - jwt

        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/dashboard");
    }

    // TODO rozwiniecie formularza register
    // 

    // walidacje w osobnym serwisie
    public function register()
    {
        if ($this->isGet()) {
            return $this->render("register");
        }

        $username = $_POST['username'] ?? "";
        $password = $_POST['password'] ?? "";
        $password2 = $_POST['password2'] ?? "";

        if (empty($username) || empty($password) || empty($password2)) {
            return $this->render('register', ['messages' => 'Fill all fields']);
        }

        if ($password !== $password2) {
            return $this->render('register', ['messages' => 'Passwords are not the same']);
        }

        // check if username is free
        $userRow = $this->userRepository->getUserByName($username);
        if ($userRow) {
            return $this->render('register', ['messages' => 'Username is already taken']);
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);


        $this->userRepository->createUser(
            $username,
            $hashedPassword
        );

        return $this->render('login', ['messages' => 'User registered successfully. Please login.']);
    }
}
