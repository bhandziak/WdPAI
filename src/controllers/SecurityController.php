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

        // fetch user details
        $userDetailsRow = $this->userRepository->getUserDetailsByName($username);

        // create user session

        session_start();
        session_regenerate_id(true);

        // TODO map user without password
        $_SESSION['user'] = $userDetailsRow;

        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/home");
        exit;
    }

    public function logout()
    {
        session_start();

        session_unset();
        session_destroy();

        setcookie(session_name(), '', time() - 3600, '/');

        header("Location: /login");
        exit;
    }

    // walidacje w osobnym serwisie
    public function register()
    {
        if ($this->isGet()) {
            return $this->render("register");
        }

        $username = $_POST['username'] ?? "";
        $password = $_POST['password'] ?? "";
        $password2 = $_POST['password2'] ?? "";
        $email = $_POST['email'] ?? null;
        $favoriteGenre = $_POST['favoriteGenre'] ?? null;

        if (empty($username) || empty($password) || empty($password2)) {
            return $this->render('register', ['messages' => 'Fill all required fields']);
        }

        if ($password !== $password2) {
            return $this->render('register', ['messages' => 'Passwords are not the same']);
        }

        // check if username is free
        $userRow = $this->userRepository->getUserByName($username);
        if ($userRow) {
            return $this->render('register', ['messages' => 'Username is already taken']);
        }

        // validate email
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->render('register', ['messages' => 'Invalid email address']);
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);


        try {
            $this->userRepository->createUser(
                $username,
                $hashedPassword,
                $email,
                $favoriteGenre
            );
        } catch (\Exception $e) {
            return $this->render('register', ['messages' => 'Something went wrong. Please try again.']);
        }

        return $this->render('login', ['messages' => 'User registered successfully. Please login.']);
    }

    public function refreshUserSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user']['username'])) {
            return;
        }

        $username = $_SESSION['user']['username'];

        $userDetailsRow = $this->userRepository->getUserDetailsByName($username);

        if ($userDetailsRow) {
            $_SESSION['user'] = $userDetailsRow;
        }
    }
}
