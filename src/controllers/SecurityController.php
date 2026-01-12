<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../middleware/AllowedMethods.php';
require_once __DIR__ . '/../middleware/AllowedRules.php';
require_once __DIR__ . '/../services/SecurityValidationService.php';

class SecurityController extends AppController
{

    private $userRepository;
    private $securityValidationService;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->securityValidationService = new SecurityValidationService($this->userRepository);
    }


    #[AllowedMethods(['POST', 'GET'])]
    public function login()
    {
        // render login
        if ($this->isGet()) {
            return $this->render('login');
        }

        // validate login
        $username = $_POST['username'] ?? "";
        try {
            $userRow = $this->securityValidationService->validateLogin($_POST);
        } catch (InvalidArgumentException $e) {
            return $this->render('login', ['messages' => $e->getMessage()]);
        }

        // fetch user details
        $userDetailsRow = $this->userRepository->getUserDetailsByName($username);

        // create user session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            session_regenerate_id(true);
        }

        // TODO map user without password
        $_SESSION['user'] = $userDetailsRow;

        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/home");
        exit;
    }

    #[AllowedMethods(['GET'])]
    #[AllowedRules(['user', 'admin'])]
    public function logout()
    {
        session_unset();
        session_destroy();

        setcookie(session_name(), '', time() - 3600, '/');

        header("Location: /login");
        exit;
    }

    #[AllowedMethods(['POST', 'GET'])]
    public function register()
    {
        // render register
        if ($this->isGet()) {
            return $this->render("register");
        }

        // validate registration
        try {
            $validatedData = $this->securityValidationService->validateRegistration($_POST);
        } catch (InvalidArgumentException $e) {
            return $this->render('register', ['messages' => $e->getMessage()]);
        }

        $username = $validatedData['username'];
        $password = $validatedData['password'];
        $email = $validatedData['email'];
        $favoriteGenre = $validatedData['favorite_genre'];

        // hash password
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

    #[AllowedMethods(['GET'])]
    #[AllowedRules(['user', 'admin'])]
    public function refreshUserSession(): void
    {
        /** @var User $user */
        $user = $_SESSION['user'];

        $username = $user->getUsername();

        $userDetailsRow = $this->userRepository->getUserDetailsByName($username);

        if ($userDetailsRow) {
            $_SESSION['user'] = $userDetailsRow;
        }
    }
}
