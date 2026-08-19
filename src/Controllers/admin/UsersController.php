<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

class UsersController extends Controller {

    public function index() {
        // Check if admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login");
            exit;
        }

        $userModel = $this->model('User');
        $users = $userModel->getAllUsers();

        $data = [
            'users' => $users,
            'active' => 'users'
        ];

        $this->view('admin/users/index', $data);
    }

    public function detail($id) {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login");
            exit;
        }

        $userModel = $this->model('User');
        $user = $userModel->findById($id);

        if (!$user) {
            $_SESSION['flash_error'] = "Người dùng không tồn tại.";
            header("Location: /admin/users");
            exit;
        }

        $data = [
            'user' => $user,
            'active' => 'users'
        ];

        $this->view('admin/users/view', $data);
    }

    public function edit($id) {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login");
            exit;
        }

        $userModel = $this->model('User');
        $user = $userModel->findById($id);

        if (!$user) {
            $_SESSION['flash_error'] = "Người dùng không tồn tại.";
            header("Location: /admin/users");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = $_POST['full_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';
            $role = $_POST['role'] ?? 'customer';

            if (empty($fullName)) {
                $_SESSION['flash_error'] = "Vui lòng điền đầy đủ thông tin.";
            } else {
                // Handle avatar upload if provided
                if (!empty($_FILES['avatar']['name'])) {
                    $avatar = $_FILES['avatar'];
                    if ($avatar['error'] === 0) {
                        $uploadsDir = ROOT_PATH . '/public/uploads/users/';
                        if (!is_dir($uploadsDir)) {
                            mkdir($uploadsDir, 0755, true);
                        }

                        $filename = uniqid() . '_' . basename($avatar['name']);
                        $filepath = $uploadsDir . $filename;

                        if (move_uploaded_file($avatar['tmp_name'], $filepath)) {
                            $userModel->updateAvatar($id, '/uploads/users/' . $filename);
                        }
                    }
                }

                // Update user information using model method
                $updateData = [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                    'role' => $role
                ];

                if ($userModel->updateUserAdmin($id, $updateData)) {
                    $_SESSION['flash_message'] = "Cập nhật thông tin người dùng thành công.";
                    header("Location: /admin/users");
                    exit;
                } else {
                    $_SESSION['flash_error'] = "Lỗi cập nhật thông tin.";
                }
            }
        }

        $data = [
            'user' => $user,
            'active' => 'users'
        ];

        $this->view('admin/users/edit', $data);
    }

    public function delete($id) {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login");
            exit;
        }

        // Prevent deleting yourself
        if ($_SESSION['user']['id'] == $id) {
            $_SESSION['flash_error'] = "Không thể xóa chính bạn.";
            header("Location: /admin/users");
            exit;
        }

        $userModel = $this->model('User');
        $user = $userModel->findById($id);

        if (!$user) {
            $_SESSION['flash_error'] = "Người dùng không tồn tại.";
            header("Location: /admin/users");
            exit;
        }

        // Delete user using model method
        if ($userModel->deleteUser($id)) {
            $_SESSION['flash_message'] = "Xóa người dùng thành công.";
        } else {
            $_SESSION['flash_error'] = "Lỗi xóa người dùng.";
        }

        header("Location: /admin/users");
        exit;
    }

    public function ban($id) {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login");
            exit;
        }

        // Prevent banning yourself
        if ($_SESSION['user']['id'] == $id) {
            $_SESSION['flash_error'] = "Không thể khóa chính bạn.";
            header("Location: /admin/users");
            exit;
        }

        $userModel = $this->model('User');
        $user = $userModel->findById($id);

        if (!$user) {
            $_SESSION['flash_error'] = "Người dùng không tồn tại.";
            header("Location: /admin/users");
            exit;
        }

        if ($userModel->banUser($id, 1)) {
            $_SESSION['flash_message'] = "Khóa người dùng thành công.";
        } else {
            $_SESSION['flash_error'] = "Lỗi khóa người dùng.";
        }

        header("Location: /admin/users");
        exit;
    }

    public function unban($id) {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login");
            exit;
        }

        $userModel = $this->model('User');
        $user = $userModel->findById($id);

        if (!$user) {
            $_SESSION['flash_error'] = "Người dùng không tồn tại.";
            header("Location: /admin/users");
            exit;
        }

        if ($userModel->banUser($id, 0)) {
            $_SESSION['flash_message'] = "Mở khóa người dùng thành công.";
        } else {
            $_SESSION['flash_error'] = "Lỗi mở khóa người dùng.";
        }

        header("Location: /admin/users");
        exit;
    }
}