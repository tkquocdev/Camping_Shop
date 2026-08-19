<?php
namespace App\Controllers\Client;

use App\Core\Controller;
use App\Core\Security;
use App\Core\Csrf;
use App\Utils\MailHelper;

use Google_Client;
use Google_Service_Oauth2;
use App\Config\Database;

class AuthController extends Controller {

    // --- XỬ LÝ ĐĂNG NHẬP ---
    public function login() {
        // Nếu đã đăng nhập rồi thì về trang chủ
        if (isset($_SESSION['user'])) {
            header("Location: /Camping_Shop/public/");
            exit;
        }

        $error = '';

        // Xử lý khi người dùng bấm nút Submit
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!Security::verifyCSRFToken($_POST[Csrf::TOKEN_FIELD] ?? null)) {
                $error = "Lỗi bảo mật CSRF. Vui lòng thử lại.";
                $this->view('client/auth/login', ['error' => $error]);
                return;
            }
            
            $username = Security::sanitizeInput($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            $userModel = $this->model('User');
            $user = $userModel->findByUsername($username);

            // 1. Kiểm tra Username và Mật khẩu
            if ($user && password_verify($password, $user['password'])) {
                
                // 2. Kiểm tra xem tài khoản có bị BAN không trước khi cho vào
                if ($user['status'] === 'banned') { 
                    $error = "Tài khoản của bạn đã bị khóa do vi phạm chính sách. Vui lòng liên hệ Admin.";
                    $this->view('client/auth/login', ['error' => $error]);
                    return; 
                }

                // 3. Nếu tài khoản OK -> Lưu Session đăng nhập
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'full_name' => $user['full_name'],
                    'name' => $user['full_name'],
                    'avatar' => $user['avatar'] ?? null,
                    'role' => $user['role'], // 'admin' hoặc 'customer'
                    'email' => $user['email']
                ];

                // 4. LOGIC TÍCH ĐIỂM ĐĂNG NHẬP HÀNG NGÀY
                $loyaltyModel = $this->model('LoyaltyModel'); 
                if (method_exists($loyaltyModel, 'checkDailyLogin') && !$loyaltyModel->checkDailyLogin($user['id'])) {
                    $loyaltyModel->addPoints($user['id'], 10, 'daily_login', 'Điểm danh hàng ngày');
                    $_SESSION['flash_message'] = "Chào mừng trở lại! Bạn được cộng +10 điểm thưởng.";
                }

                // 4.5. ĐỒNG BỘ GIỎ HÀNG TỪ SESSION VÀO DATABASE
                if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                    // Gọi Model Cart vừa tạo ở Bước 2
                    $cartModel = $this->model('Cart'); 
                    
                    // Thực hiện đồng bộ
                    $cartModel->syncSessionToDb($user['id'], $_SESSION['cart']);
                    
                    // Sau khi lưu vào DB xong thì xóa Session Cart đi để tránh trùng lặp
                    unset($_SESSION['cart']);
                }
                // =================================================================

                // 5. Chuyển hướng dựa trên quyền
                if ($user['role'] == 'admin') {
                    header("Location: /admin/dashboard");
                } else {
                    header("Location: /");
                }
                exit;

            } else {
                $error = "Tài khoản hoặc mật khẩu không chính xác!";
            }
        }

        $this->view('client/auth/login', ['error' => $error]);
    }

    // --- XỬ LÝ ĐĂNG KÝ ---
    public function register() {
        
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!Security::verifyCSRFToken($_POST[Csrf::TOKEN_FIELD] ?? null)) {
                $error = "Lỗi bảo mật CSRF. Vui lòng thử lại.";
                $this->view('client/auth/register', ['error' => $error]);
                return;
            }
            
            $username = trim(Security::sanitizeInput($_POST['username'] ?? ''));
            $fullname = trim(Security::sanitizeInput($_POST['full_name'] ?? ''));
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            $userModel = $this->model('User');

            // Validate cơ bản
            if (empty($username)) {
                $error = "Tài khoản không được để trống!";
                $_SESSION['old'] = $_POST;
            } elseif (empty($fullname)) {
                $error = "Họ tên không được để trống!";
                $_SESSION['old'] = $_POST;
            } elseif ($password != $confirm_password) {
                $error = "Mật khẩu nhập lại không khớp!";
                $_SESSION['old'] = $_POST;
            } elseif ($userModel->findByUsername($username)) {
                $error = "Tài khoản này đã tồn tại!";
                $_SESSION['old'] = $_POST;
            } else {
                // Tạo user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                try {
                    // Direct insert
                    $db = (new \App\Config\Database())->getConnection();
                    $stmt = $db->prepare("INSERT INTO users (username, full_name, password, role) VALUES (?, ?, ?, ?)");
                    if ($stmt->execute([$username, $fullname, $hashedPassword, 'customer'])) {
                        $_SESSION['flash_message'] = "Đăng ký thành công! Vui lòng đăng nhập.";
                        header("Location: http://campingshop.localhost/auth/login");
                        exit;
                    } else {
                        $error = "Insert failed without exception";
                        $_SESSION['old'] = $_POST;
                    }
                } catch (\Exception $e) {
                    $error = "Database error: " . $e->getMessage();
                    $_SESSION['old'] = $_POST;
                }
            }
        }

        $this->view('client/auth/register', ['error' => $error, 'success' => $success]);
    }

    // --- XỬ LÝ ĐĂNG XUẤT ---
    public function logout() {
        session_destroy();
        header("Location: /auth/login");
        exit;
    }

    // Trang Quên Mật Khẩu
    public function forgot() {
        
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!Security::verifyCSRFToken($_POST[Csrf::TOKEN_FIELD] ?? null)) {
                $error = "Lỗi bảo mật CSRF. Vui lòng thử lại.";
                $this->view('client/auth/forgot', ['error' => $error]);
                return;
            }
            
            $identifier = Security::sanitizeInput($_POST['identifier'] ?? '');
            if (!$identifier) {
                $error = "Vui lòng nhập tên tài khoản hoặc email!";
            } else {
                $userModel = $this->model('User');
                $user = null;
                $accountType = null; // 'email' hoặc 'username'
                
                // Kiểm tra xem có phải email không
                if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                    // Thử tìm theo email trước
                    $user = $userModel->findByEmail($identifier);
                    $accountType = 'email';
                    
                    // Nếu không tìm được bằng email, thử tìm theo username (email có thể là username)
                    if (!$user) {
                        $user = $userModel->findByUsername($identifier);
                        $accountType = 'username';
                    }
                } else {
                    // Tìm theo username
                    $user = $userModel->findByUsername($identifier);
                    $accountType = 'username';
                }
                
                if ($user) {
                    // Nếu là username và không có email, không gửi OTP
                    if ($accountType === 'username' && empty($user['email'])) {
                        $_SESSION['reset_username'] = $identifier;
                        $_SESSION['reset_account_type'] = 'username';
                        $_SESSION['reset_user_id'] = $user['id'];
                        $_SESSION['otp_verified'] = true; // Đánh dấu đã vượt qua bước xác minh
                        $_SESSION['flash_message'] = "Tài khoản $identifier được tìm thấy. Vui lòng nhập mật khẩu mới.";
                        header("Location: /auth/reset-password");
                        exit;
                    } else {
                        // Nếu là email hoặc username có email, gửi OTP
                        $sendTo = $accountType === 'email' ? $identifier : $user['email'];
                        
                        if (empty($sendTo)) {
                            $error = "Tài khoản này không có email để gửi OTP!";
                        } else {
                            $otp = rand(100000, 999999);
                            
                            // Lưu vào DB
                            $resetModel = $this->model('PasswordReset');
                            $resetModel->createToken($sendTo, $otp);

                            // --- GỬI MAIL ---
                            $mailHelper = new MailHelper();
                            if ($mailHelper->sendOTP($sendTo, $otp)) {
                                $_SESSION['flash_message'] = "Mã OTP đã được gửi đến email <strong>$sendTo</strong>. Vui lòng kiểm tra hộp thư.";
                                $_SESSION['reset_email'] = $sendTo;
                                $_SESSION['reset_account_type'] = $accountType;
                                $_SESSION['reset_user_id'] = $user['id'];
                                header("Location: /auth/verify-otp");
                                exit;
                            } else {
                                $error = "Không thể gửi email. Vui lòng kiểm tra lại kết nối hoặc thử lại sau.";
                            }
                        }
                    }
                } else {
                    $error = "Tài khoản hoặc email <strong>" . htmlspecialchars($identifier) . "</strong> chưa được đăng ký!";
                }
            }
        }
        $this->view('client/auth/forgot', ['error' => $error]);
    }

    // Trang Xác Nhận OTP
    public function verify_otp() {
        // Nếu là username không email, đẩy thẳng sang reset password
        if (isset($_SESSION['reset_account_type']) && $_SESSION['reset_account_type'] === 'username' && isset($_SESSION['reset_username'])) {
            $_SESSION['otp_verified'] = true;
            header("Location: /auth/reset-password");
            exit;
        }

        if (!isset($_SESSION['reset_email'])) {
            header("Location: /auth/forgot-password");
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!Security::verifyCSRFToken($_POST[Csrf::TOKEN_FIELD] ?? null)) {
                $error = "Lỗi bảo mật CSRF. Vui lòng thử lại.";
                $this->view('client/auth/verify_otp', ['error' => $error]);
                return;
            }
            
            $otp = Security::sanitizeInput($_POST['otp'] ?? '');
            $email = $_SESSION['reset_email'];

            $resetModel = $this->model('PasswordReset');
            
            if ($resetModel->verifyToken($email, $otp)) {
                // OTP đúng -> Chuyển sang trang đặt lại mật khẩu
                $_SESSION['otp_verified'] = true; // Đánh dấu đã verify
                header("Location: /auth/reset-password");
                exit;
            } else {
                $error = "Mã OTP không chính xác hoặc đã hết hạn!";
            }
        }
        $this->view('client/auth/verify_otp', ['error' => $error]);
    }

    // Trang Đặt mật khẩu mới
    public function reset_password() {
        // Phải verify OTP rồi mới được vào đây (hoặc là username không email)
        if (!isset($_SESSION['otp_verified'])) {
            header("Location: /auth/forgot-password");
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!Security::verifyCSRFToken($_POST[Csrf::TOKEN_FIELD] ?? null)) {
                $error = "Lỗi bảo mật CSRF. Vui lòng thử lại.";
                $this->view('client/auth/reset_password', ['error' => $error]);
                return;
            }
            
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if ($password !== $confirm) {
                $error = "Mật khẩu xác nhận không khớp!";
            } elseif (strlen($password) < 3) {
                $error = "Mật khẩu phải trên 3 ký tự!";
            } else {
                // Cập nhật mật khẩu mới
                $userModel = $this->model('User');
                $userId = $_SESSION['reset_user_id'] ?? null;
                
                if (!$userId) {
                    $error = "Lỗi hệ thống. Vui lòng thử lại.";
                } else {
                    $newHash = password_hash($password, PASSWORD_BCRYPT);
                    $userModel->updatePasswordById($userId, $newHash);

                    // Dọn dẹp Session
                    unset($_SESSION['reset_email']);
                    unset($_SESSION['reset_username']);
                    unset($_SESSION['reset_account_type']);
                    unset($_SESSION['reset_user_id']);
                    unset($_SESSION['otp_verified']);

                    // Xóa token OTP nếu có
                    if (isset($_SESSION['reset_email'])) {
                        $this->model('PasswordReset')->deleteToken($_SESSION['reset_email']);
                    }

                    $_SESSION['flash_message'] = "Đổi mật khẩu thành công! Hãy đăng nhập lại.";
                    header("Location: /auth/login");
                    exit;
                }
            }
        }
        $this->view('client/auth/reset_password', ['error' => $error]);
    }

    // Thêm tính năng đăng nhập bằng google
    // LOGIN GOOGLE: HÀM 1 - CHUYỂN HƯỚNG
    public function google() {
        $redirectUri = $this->requiredEnv('GOOGLE_REDIRECT_URI');
        
        // Khởi tạo client
        $client = new Google_Client();
        $client->setClientId($this->requiredEnv('GOOGLE_CLIENT_ID'));
        $client->setClientSecret($this->requiredEnv('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri($redirectUri);
        
        // Thêm quyền truy cập
        $client->addScope("email");
        $client->addScope("profile");
        
        // Chuyển hướng người dùng sang Google
        header('Location: ' . $client->createAuthUrl());
        exit;
    }

    public function google_callback() {
        try {
            $redirectUri = $this->requiredEnv('GOOGLE_REDIRECT_URI');
            
            $client = new \Google_Client();
            $client->setClientId($this->requiredEnv('GOOGLE_CLIENT_ID'));
            $client->setClientSecret($this->requiredEnv('GOOGLE_CLIENT_SECRET'));
            $client->setRedirectUri($redirectUri);
            
            // Fix lỗi SSL trên Localhost
            $client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));

            if (!isset($_GET['code'])) {
                header('Location: /auth/login');
                exit;
            }

            // 1. Lấy Token
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            if (isset($token['error'])) {
                throw new \Exception("Lỗi xác thực Google");
            }

            // 2. Lấy thông tin User
            $client->setAccessToken($token);
            $google_oauth = new \Google_Service_Oauth2($client);
            $google_info = $google_oauth->userinfo->get();

            $email  = $google_info->email;
            $name   = $google_info->name;
            $g_id   = $google_info->id;
            $avatar = $google_info->picture ?? null;
            
            // Avatar từ Google là URL (https://...), lưu trực tiếp vào DB
            // Nếu muốn download & save local, xử lý ở đây
            // Hiện tại chỉ lưu URL từ Google (đơn giản & tiết kiệm storage)
            
            // Optional: Download avatar và lưu local
            if ($avatar) {
                $avatar = $this->downloadGoogleAvatar($avatar, $g_id);
            }

            // 3. Xử lý Database
            $conn = Database::getConnection(); 
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // --- ĐÃ CÓ TÀI KHOẢN ---
                if ($user['status'] === 'banned') {
                    $this->view('client/auth/login', ['error' => 'Tài khoản Google này đã bị khóa.']);
                    return;
                }
                // Cập nhật Avatar/GoogleID
                $stmtUpdate = $conn->prepare("UPDATE users SET google_id = ?, avatar = ? WHERE id = ?");
                $stmtUpdate->execute([$g_id, $avatar, $user['id']]);
                
                $userId   = $user['id'];
                $role     = $user['role'];
                $fullName = $user['full_name'];

            } else {
                // --- TÀI KHOẢN MỚI ---
                $sql = "INSERT INTO users (full_name, email, google_id, avatar, password, role, status) VALUES (?, ?, ?, ?, NULL, 'customer', 'active')";
                $stmtInsert = $conn->prepare($sql);
                $stmtInsert->execute([$name, $email, $g_id, $avatar]);
                $userId = $conn->lastInsertId();
                $role     = 'customer';
                $fullName = $name;
            }

            // 4. Lưu Session
            if (session_status() === PHP_SESSION_NONE) session_start();
            session_regenerate_id(true);

            $_SESSION['user'] = [
                'id'        => $userId,
                'full_name' => $fullName,
                'name'      => $fullName,
                'avatar'    => $avatar ?? null,
                'email'     => $email,
                'role'      => $role
            ];

            // 5. Tích điểm & Giỏ hàng
            $loyaltyModel = $this->model('LoyaltyModel'); 
            if (method_exists($loyaltyModel, 'checkDailyLogin') && !$loyaltyModel->checkDailyLogin($userId)) {
                $loyaltyModel->addPoints($userId, 10, 'daily_login', 'Điểm danh hàng ngày');
                $_SESSION['flash_message'] = "Đăng nhập Google thành công! +10 điểm.";
            }

            if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                $cartModel = $this->model('Cart'); 
                $cartModel->syncSessionToDb($userId, $_SESSION['cart']);
                unset($_SESSION['cart']);
            }

            // 6. Chuyển hướng về trang chủ
            if ($role == 'admin') {
                header("Location: /admin/dashboard");
            } else {
                header("Location: /");
            }
            exit;

        } catch (\Exception $e) {
            die("Lỗi: " . $e->getMessage());
        }
    }

    /**
     * Download avatar từ Google và lưu local
     * Lưu file thay vì dùng URL (URL Google có thể expire)
     */
    private function requiredEnv($key) {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($value === false || $value === null || $value === '') {
            throw new \RuntimeException("Missing required environment variable: $key");
        }

        return $value;
    }

    private function downloadGoogleAvatar($imageUrl, $googleId) {
        try {
            $uploadsDir = ROOT_PATH . '/public/uploads/users';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }
            
            $filename = 'avatar_' . $googleId . '.jpg';
            $filepath = $uploadsDir . '/' . $filename;
            
            // Download image từ Google
            $imageData = @file_get_contents($imageUrl);
            if ($imageData === false) {
                // Nếu download fail, trả về URL gốc
                return $imageUrl;
            }
            
            // Lưu file
            if (file_put_contents($filepath, $imageData)) {
                return '/uploads/users/' . $filename;
            }
            
            return $imageUrl;
        } catch (\Exception $e) {
            // Nếu có lỗi, trả về URL gốc
            return $imageUrl;
        }
    }
}   
