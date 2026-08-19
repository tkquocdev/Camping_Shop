<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {
   
    // Biến tĩnh lưu trữ kết nối (Singleton Pattern)
    private static $pdo = null;

    // Phương thức tĩnh để lấy kết nối
    public static function getConnection() {
       
        // Nếu chưa có kết nối thì mới khởi tạo (Chỉ kết nối 1 lần duy nhất)
        if (self::$pdo === null) {
           
            self::loadEnvironment();

            $host = self::requiredEnv('DB_HOST');
            $port = self::requiredEnv('DB_PORT');
            $dbname = self::requiredEnv('DB_NAME');
            $user = self::requiredEnv('DB_USER');
            $pass = self::requiredEnv('DB_PASS');

            try {
                // Chuỗi kết nối PostgreSQL
                $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
               
                // Khởi tạo PDO
                self::$pdo = new PDO($dsn, $user, $pass);
               
                // Cấu hình các chế độ báo lỗi và lấy dữ liệu
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Tăng bảo mật
               
            } catch (PDOException $e) {
                // Ghi log lỗi hệ thống (tùy chọn)
                error_log("DB Connection Error: " . $e->getMessage());
               
                // Dừng chương trình và báo lỗi
                die("Lỗi hệ thống: Không thể kết nối cơ sở dữ liệu QL_CampingShop.");
            }
        }
       
        return self::$pdo;
    }

    private static function loadEnvironment() {
        if (class_exists('\\Dotenv\\Dotenv') && defined('ROOT_PATH')) {
            \Dotenv\Dotenv::createImmutable(ROOT_PATH)->safeLoad();
        }
    }

    private static function requiredEnv($key) {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($value === false || $value === null || $value === '') {
            throw new \RuntimeException("Missing required environment variable: $key");
        }

        return $value;
    }
}

