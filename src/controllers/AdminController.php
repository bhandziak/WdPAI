<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class AdminController extends AppController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    #[AllowedMethods(['GET'])]
    #[AllowedRules(['admin'])]
    public function index()
    {
        $search = $_GET['search'] ?? null;

        $users = $this->userRepository->getUsersByText($search);

        return $this->render('admin', [
            'users' => $users
        ]);
    }
}
