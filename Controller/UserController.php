<?php
class UserController {
    public function index(): void {
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            $_SESSION['error'] = 'Access denied. Admins only.';
            header('Location: index.php?page=home');
            exit;
        }

        $userModel = new User();
        $users = $userModel->all();

        require BASE_PATH . '/views/users/index.php';
    }

    public function store(): void {
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            $_SESSION['error'] = 'Access denied. Admins only.';
            header('Location: index.php?page=home');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role = trim($_POST['role'] ?? 'student');

        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'Name, email, and password are required.';
            header('Location: index.php?page=users');
            exit;
        }

        $userModel = new User();
        
        // Check if email already exists
        if ($userModel->findByEmail($email)) {
            $_SESSION['error'] = 'Email address is already taken.';
            header('Location: index.php?page=users');
            exit;
        }

        if ($userModel->create(['name' => $name, 'email' => $email, 'password' => $password, 'role' => $role])) {
            $_SESSION['success'] = 'Account created successfully.';
        } else {
            $_SESSION['error'] = 'Failed to create account.';
        }
        
        header('Location: index.php?page=users');
        exit;
    }

    public function delete(int $id): void {
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            $_SESSION['error'] = 'Access denied. Admins only.';
            header('Location: index.php?page=home');
            exit;
        }

        if ($id === (int)($_SESSION['user_id'] ?? 0)) {
            $_SESSION['error'] = 'You cannot delete your own account.';
            header('Location: index.php?page=users');
            exit;
        }

        $userModel = new User();
        if ($userModel->delete($id)) {
            $_SESSION['success'] = 'Account deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete account.';
        }
        
        header('Location: index.php?page=users');
        exit;
    }
}
