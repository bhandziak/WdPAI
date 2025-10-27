<?php

require_once 'AppController.php';

class SecurityController extends AppController{

    public function login(){

        // TODO get data from login form
        // check if user exists in database
        // render dashboard view if success authentication
        return $this->render('login');
    }

    public function register(){
        
        return $this->render('register');
    }
}