<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class SecurityController extends AppController{

    private $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }


    public function login(){
        // nazewnictwo - early return
        if (!$this->isPost()) {
            return $this->render('login');
        }

        // TODO get data from login form
        // check if user exists in database
        // render dashboard view if success authentication

        // NULL Coalescing Operators
        $email = $_POST['email'] ?? "";
        $password = $_POST['password'] ?? "";
        // var_dump($email, $password);

        // TERNARY OPERATOR
        // bool ? _ : _

        // ELVIS OPERATOR
        // bool ?: _

        if (empty($email) || empty($password)) {
            return $this->render('login', ['messages' => 'Fill all fields']);
        }

        $userRow = $this->userRepository->getUserByEmail($email);

        if (!$userRow) {
            return $this->render('login', ['messages' => 'User not found']);
        }

        if (!password_verify($password, $userRow['password'])) {
            return $this->render('login', ['messages' => 'Wrong password']);
        }

        // TODO
        // create user session
        // cookie - jwt

        // return $this->render("dashboard", ['cards' => []]);
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/dashboard");

        
    }

    // TODO rozwiniecie formularza register
    // 

    // walidacje w osobnym serwisie
    public function register(){
        if($this->isGet()){
            return $this->render("register");
        }

        $email = $_POST['email'] ?? "";
        $password = $_POST['password'] ?? "";
        $password2 = $_POST['password2'] ?? "";
        $name = $_POST['firstName'] ?? "";
        $lastName = $_POST['lastName'] ?? "";

        if (empty($email) || empty($password) || empty($password2)||
         empty($name) || empty($lastName)  ) {
            return $this->render('register', ['messages' => 'Fill all fields']);
        }

        if($password !== $password2){
            return $this->render('register', ['messages' => 'Passwords are not the same']);
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);


        $this->userRepository->createUser(
            $email,
            $hashedPassword,
            $name,
            $lastName
        );

        return $this->render('login', ['messages' => 'User registered successfully. Please login.']);
    }
}