<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class AdminController extends AppController
{
    private UserRepository $userRepository;
    private static $roles = ['user', 'admin'];

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
            'users' => $users,
            'roles' => self::$roles
        ]);
    }

    #[AllowedMethods(['PATCH', 'POST'])]
    #[AllowedRules(['admin'])]
    public function changeRole()
    {
        $userId = $_POST['user_id'] ?? null;
        $newRole = $_POST['role'] ?? null;

        if ($userId == $_SESSION['user']->getId()) {
            throw new Exception('Can not change your role', 400);
        }

        try {
            if ($userId && $newRole && in_array($newRole, self::$roles)) {
                $this->userRepository->changeUserRole((int)$userId, $newRole);
            }
        } catch (Exception $e) {
            throw new Exception('Error while changing role: ' . $e->getMessage(), 500);
        }


        header("Location: /admin");
        exit;
    }

    #[AllowedMethods(['DELETE', 'POST'])]
    #[AllowedRules(['admin'])]
    public function deleteUser()
    {
        $userId = $_POST['user_id'] ?? null;

        if ($userId == $_SESSION['user']->getId()) {
            throw new Exception('Cannot delete yourself', 400);
        }

        try {
            if ($userId) {
                $this->userRepository->deleteUser((int)$userId);
            }
        } catch (Exception $e) {
            throw new Exception('Error while deleting user: ' . $e->getMessage(), 500);
        }

        header("Location: /admin");
        exit;
    }
}
