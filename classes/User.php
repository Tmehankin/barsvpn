<?php
// classes/User.php

require_once 'config/database.php';

class User {
    private $conn;
    private $table = 'users';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Регистрация пользователя
    public function register($email, $password, $first_name = '', $last_name = '') {
        // Проверяем, существует ли пользователь
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'Email уже зарегистрирован'];
        }

        // Валидация email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Неверный формат email'];
        }

        // Валидация пароля
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Пароль должен быть не менее 6 символов'];
        }

        try {
            $query = "INSERT INTO " . $this->table . " 
                     (email, password_hash, first_name, last_name, verification_token) 
                     VALUES (:email, :password_hash, :first_name, :last_name, :token)";
            
            $stmt = $this->conn->prepare($query);
            
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $verification_token = $this->generateToken();
            
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':token', $verification_token);
            
            if ($stmt->execute()) {
                $user_id = $this->conn->lastInsertId();
                
                // Отправляем email для подтверждения (опционально)
                // $this->sendVerificationEmail($email, $verification_token);
                
                return [
                    'success' => true, 
                    'message' => 'Регистрация успешна',
                    'user_id' => $user_id
                ];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Ошибка регистрации: ' . $e->getMessage()];
        }
        
        return ['success' => false, 'message' => 'Ошибка регистрации'];
    }

    // Авторизация пользователя
    public function login($email, $password, $remember = false) {
        try {
            $query = "SELECT id, email, password_hash, status FROM " . $this->table . " 
                     WHERE email = :email AND status = 'active'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch();
                
                if (password_verify($password, $user['password_hash'])) {
                    // Обновляем время последнего входа
                    $this->updateLastLogin($user['id']);
                    
                    // Создаем сессию
                    $session_token = $this->createSession($user['id'], $remember);
                    
                    return [
                        'success' => true,
                        'message' => 'Успешная авторизация',
                        'user' => $user,
                        'session_token' => $session_token
                    ];
                }
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Ошибка авторизации: ' . $e->getMessage()];
        }
        
        return ['success' => false, 'message' => 'Неверный email или пароль'];
    }

    // Создание сессии
    private function createSession($user_id, $remember = false) {
        $session_token = $this->generateToken();
        $lifetime = $remember ? SESSION_LIFETIME : 3600; // 1 час или 30 дней
        $expires_at = date('Y-m-d H:i:s', time() + $lifetime);
        
        try {
            $query = "INSERT INTO user_sessions 
                     (user_id, session_token, expires_at, ip_address, user_agent) 
                     VALUES (:user_id, :token, :expires_at, :ip, :user_agent)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':token', $session_token);
            $stmt->bindParam(':expires_at', $expires_at);
            $stmt->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
            $stmt->bindParam(':user_agent', $_SERVER['HTTP_USER_AGENT']);
            
            if ($stmt->execute()) {
                // Устанавливаем куки
                setcookie('session_token', $session_token, time() + $lifetime, '/');
                $_SESSION['user_id'] = $user_id;
                $_SESSION['session_token'] = $session_token;
                
                return $session_token;
            }
        } catch (Exception $e) {
            error_log("Session creation error: " . $e->getMessage());
        }
        
        return false;
    }

    // Проверка сессии
    public function checkSession($session_token = null) {
        if (!$session_token) {
            $session_token = $_COOKIE['session_token'] ?? $_SESSION['session_token'] ?? null;
        }
        
        if (!$session_token) {
            return false;
        }
        
        try {
            $query = "SELECT u.id, u.email, u.first_name, u.last_name, u.status 
                     FROM users u 
                     JOIN user_sessions s ON u.id = s.user_id 
                     WHERE s.session_token = :token AND s.expires_at > NOW() AND u.status = 'active'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $session_token);
            $stmt->execute();
            
            if ($stmt->rowCount() == 1) {
                return $stmt->fetch();
            }
        } catch (Exception $e) {
            error_log("Session check error: " . $e->getMessage());
        }
        
        return false;
    }

    // Выход
    public function logout($session_token = null) {
        if (!$session_token) {
            $session_token = $_COOKIE['session_token'] ?? $_SESSION['session_token'] ?? null;
        }
        
        if ($session_token) {
            try {
                $query = "DELETE FROM user_sessions WHERE session_token = :token";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':token', $session_token);
                $stmt->execute();
            } catch (Exception $e) {
                error_log("Logout error: " . $e->getMessage());
            }
        }
        
        // Очищаем сессию и куки
        setcookie('session_token', '', time() - 3600, '/');
        session_destroy();
        
        return true;
    }

    // Проверка существования email
    private function emailExists($email) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    // Обновление времени последнего входа
    private function updateLastLogin($user_id) {
        $query = "UPDATE " . $this->table . " SET last_login = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
    }

    // Генерация токена
    private function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    // Получение информации о пользователе
    public function getUserById($id) {
        $query = "SELECT id, email, first_name, last_name, status, created_at, last_login 
                 FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    // Обновление профиля
    public function updateProfile($user_id, $first_name, $last_name) {
        try {
            $query = "UPDATE " . $this->table . " 
                     SET first_name = :first_name, last_name = :last_name 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':id', $user_id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Профиль обновлен'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Ошибка обновления: ' . $e->getMessage()];
        }
        
        return ['success' => false, 'message' => 'Ошибка обновления профиля'];
    }
}
?>