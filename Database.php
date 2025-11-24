<?php

require_once "config.php";
require_once __DIR__ . '/src/controllers/AppController.php';

// to nie jest kontroler bo nie nic nie wraca uzytkownikowi
class Database {
    private $username;
    private $password;
    private $host;
    private $database;

    private static $instance = null;
    private $connection = null;

    private ErrorController $errorController;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // $this->username = USERNAME;
        // $this->password = PASSWORD;
        // $this->host = HOST;
        // $this->database = DATABASE;

        $config = require __DIR__ . "/config.php";

        $this->host     = $config['db_host'];
        $this->database = $config['db_name'];
        $this->username = $config['db_user'];
        $this->password = $config['db_pass'];

        $this->errorController = AppController::getInstance("ErrorController");
    }

    public function connect()
    {
        if($this->connection !== null) {
            return $this->connection;
        }

        try {
            $conn = new PDO(
                "pgsql:host=$this->host;port=5432;dbname=$this->database",
                $this->username,
                $this->password,
                ["sslmode"  => "prefer"]
            );

            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        }
        catch(PDOException $e) {
            // TODO instead of die, redirect to an error page
            // die("Connection failed: " . $e->getMessage());

            $this->errorController->error($e->getMessage());
            exit();
        }
    }

    public function disconnect()
    {
        $this->connection = null;
    }
}