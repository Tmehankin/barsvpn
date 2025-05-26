<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование профиля - VPN Service</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
        }
        
        .header {
            background: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #333;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
        }
        
        .nav-links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .nav-links a:hover {
            text-decoration: underline;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .profile-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .profile-header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .profile-header p {
            color: #666;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }
        
        input[type="email"], input[type="text"], input[type="password"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        input:disabled {
            background: #f8fafc;
            color: #94a3b8;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #f8fafc;
            color: #475569;
            border: 2px solid #e2e8f0;
        }
        
        .btn-secondary:hover {
            background: #e2e8f0;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .section {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f1f5f9;
        }
        
        .section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .section h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .info-text {
            color: #666;
            font-size: 14px;
            margin-top: 8px;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php
    require_once 'config/config.php';
    require_once 'classes/User.php';
    
    $user = new User();
    $message = '';
    $message_type = '';
    
    // Проверяем авторизацию
    if (!$current_user = $user->checkSession()) {
        header('Location: login.php');
        exit;
    }
    
    $user_data = $user->getUserById($current_user['id']);
    
    // Обработка формы
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['update_profile'])) {
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            
            $result = $user->updateProfile($current_user['id'], $first_name, $last_name);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'error';
            
            if ($result['success']) {
                // Обновляем данные пользователя
                $user_data = $user->getUserById($current_user['id']);
            }
        }
    }
    ?>

    <div class="header">
        <div class="header-content">
            <div class="logo">🔐 VPN Service</div>
            <div class="nav-links">
                <a href="dashboard.php">← Назад к панели</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <h1>Редактирование профиля</h1>
                <p>Обновите свою личную информацию</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <!-- Основная информация -->
                <div class="section">
                    <h3>Основная информация</h3>
                    
                    <div class="form-group">
                        <label for="email">Email адрес</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" disabled>
                        <div class="info-text">Email адрес нельзя изменить</div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">Имя</label>
                            <input type="text" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($user_data['first_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Фамилия</label>
                            <input type="text" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($user_data['last_name'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Информация об аккаунте -->
                <div class="section">
                    <h3>Информация об аккаунте</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Дата регистрации</label>
                            <input type="text" value="<?php echo date('d.m.Y H:i', strtotime($user_data['created_at'])); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label>Последний вход</label>
                            <input type="text" value="<?php echo $user_data['last_login'] ? date('d.m.Y H:i', strtotime($user_data['last_login'])) : 'Никогда'; ?>" disabled>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Статус аккаунта</label>
                        <input type="text" value="<?php echo ucfirst($user_data['status']); ?>" disabled>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        Сохранить изменения
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>