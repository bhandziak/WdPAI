<?php

require_once 'AppController.php';

class SecurityController extends AppController{

     // ======= LOKALNA "BAZA" UŻYTKOWNIKÓW =======
    private static array $users = [
        [
            'email' => 'anna@example.com',
            'password' => '$2y$10$wz2g9JrHYcF8bLGBbDkEXuJQAnl4uO9RV6cWJKcf.6uAEkhFZpU0i', // test123
            'first_name' => 'Anna'
        ],
        [
            'email' => 'bartek@example.com',
            'password' => '$2y$10$fK9rLobZK2C6rJq6B/9I6u6Udaez9CaRu7eC/0zT3pGq5piVDsElW', // haslo456
            'first_name' => 'Bartek'
        ],
        [
            'email' => 'celina@example.com',
            'password' => '$2y$10$Cq1J6YMGzRKR6XzTb3fDF.6sC6CShm8kFgEv7jJdtyWkhC1GuazJa', // qwerty
            'first_name' => 'Celina'
        ],
    ];


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

        //TODO replace with search from database
        $userRow = null;
        foreach (self::$users as $u) {
            if (strcasecmp($u['email'], $email) === 0) {
                $userRow = $u;
                break;
            }
        }

        if (!$userRow) {
            return $this->render('login', ['messages' => 'User not found']);
        }

        if (!password_verify($password, $userRow['password'])) {
            return $this->render('login', ['messages' => 'Wrong password']);
        }



        // return $this->render("dashboard", ['cards' => []]);
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/dashboard");

        
    }

    // TODO rozwiniecie formularza register
    // 
    public function register(){
        if($this->isGet()){
            return $this->render("register");
        }


        $email = $_POST['email'] ?? "";
        $password = $_POST['password'] ?? "";
        $password2 = $_POST['password2'] ?? "";
        $name = $_POST['firstName'] ?? "";
        $city = $_POST['city'] ?? "";

        if (empty($email) || empty($password) || empty($password2)||
         empty($name) || empty($city)  ) {
            return $this->render('register', ['messages' => 'Fill all fields']);
        }

        if($password !== $password2){
            return $this->render('register', ['messages' => 'Passwords are not the same']);
        }

        return $this->render('login');
    }
}