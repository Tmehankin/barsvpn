<?php
// config/database.php

class Database {
    private $host = 'localhost';
    private $db_name = 'vpn_service';
    private $username = 'admin';
    private $password = '524438';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                ]
            );
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }

        return $this->conn;
    }
}

// config/config.php
define('SITE_URL', 'http://localhost/vpn-service');
define('SITE_NAME', 'VPN Service');
define('SESSION_LIFETIME', 3600 * 24 * 30); // 30 дней

// Настройки для email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-password');
define('FROM_EMAIL', 'noreply@vpnservice.com');
define('FROM_NAME', 'VPN Service');

// Ключ для шифрования
define('ENCRYPTION_KEY', 'your-secret-key-here-32-chars-long');

session_start();
?>