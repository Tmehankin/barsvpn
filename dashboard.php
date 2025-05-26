<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'config/config.php';
require_once 'classes/User.php';

$user = new User();

// Проверяем авторизацию
if (!$current_user = $user->checkSession()) {
    header('Location: login.php');
    exit;
}

// Обработка выхода
if (isset($_GET['logout'])) {
    $user->logout();
    header('Location: login.php');
    exit;
}

$user_data = $user->getUserById($current_user['id']);
$initials = strtoupper(substr($current_user['first_name'], 0, 1) . substr($current_user['last_name'], 0, 1));
if (empty(trim($initials))) {
    $initials = strtoupper(substr($current_user['email'], 0, 2));
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель управления - VPN Service</title>
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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

        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .welcome {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .welcome h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .welcome p {
            color: #666;
            font-size: 16px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }

        .card-icon {
            font-size: 24px;
            margin-right: 15px;
        }

        .card h3 {
            color: #333;
            font-size: 18px;
        }

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
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

        .profile-info {
            color: #666;
            line-height: 1.6;
        }

        .profile-info strong {
            color: #333;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">🔐 VPN Service</div>
            <div class="user-menu">
                <div class="user-info">
                    <div class="avatar"><?php echo $initials; ?></div>
                    <span><?php echo htmlspecialchars($current_user['first_name'] ?: $current_user['email']); ?></span>
                </div>
                <a href="?logout=1" class="logout-btn">Выйти</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="welcome">
            <h1>Добро пожаловать<?php echo $current_user['first_name'] ? ', ' . htmlspecialchars($current_user['first_name']) : ''; ?>!</h1>
            <p>Управляйте своими VPN подключениями и настройками аккаунта</p>
        </div>

        <div class="alert alert-info">
            💡 <strong>Информация:</strong> Для получения доступа к VPN серверам необходимо приобрести подписку.
            Функция оплаты будет добавлена в следующей версии.
        </div>

        <div class="cards">

            <!-- Карточка подписки -->
            <div class="card">
                <div class="card-header">
                    <span class="card-icon">📋</span>
                    <h3>Моя подписка</h3>
                </div>

                <?php
                require_once 'classes/Plan.php';
                $planObj = new Plan();
                $subscription = $planObj->getUserSubscription($current_user['id']);

                if ($subscription): ?>
                    <div class="status status-active">Подписка активна</div>
                    <p><strong>План:</strong> <?php echo htmlspecialchars($subscription['plan_name']); ?></p>
                    <p><strong>Действует до:</strong> <?php echo date('d.m.Y', strtotime($subscription['expires_at'])); ?></p>
                    <a href="configs.php" class="btn btn-primary">Скачать конфиги</a>
                <?php else: ?>
                    <div class="status status-inactive">Подписка неактивна</div>
                    <p style="color: #666; margin-bottom: 20px;">
                        У вас пока нет активной подписки на VPN сервис.
                        Приобретите подписку для доступа к серверам.
                    </p>
                    <a href="plans.php" class="btn btn-primary">Выбрать план</a>
                <?php endif; ?>
            </div>

            <!-- Карточка серверов -->
            <div class="card">
                <div class="card-header">
                    <span class="card-icon">🌐</span>
                    <h3>VPN Серверы</h3>
                </div>

                <p style="color: #666; margin-bottom: 20px;">
                    Доступные типы подключений:
                </p>

                <ul style="margin-bottom: 20px; color: #666;">
                    <li>OpenVPN - универсальный протокол</li>
                    <li>WireGuard - быстрый и современный</li>
                    <li>AmneziaWG - обход блокировок</li>
                </ul>

                <a href="#" class="btn btn-secondary">Скачать конфиги</a>
            </div>

            <!-- Карточка профиля -->
            <div class="card">
                <div class="card-header">
                    <span class="card-icon">👤</span>
                    <h3>Мой профиль</h3>
                </div>

                <div class="profile-info">
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($current_user['email']); ?></p>
                    <p><strong>Имя:</strong> <?php echo htmlspecialchars($current_user['first_name'] ?: 'Не указано'); ?></p>
                    <p><strong>Фамилия:</strong> <?php echo htmlspecialchars($current_user['last_name'] ?: 'Не указано'); ?></p>
                    <p><strong>Дата регистрации:</strong> <?php echo date('d.m.Y', strtotime($user_data['created_at'])); ?></p>
                    <?php if ($user_data['last_login']): ?>
                        <p><strong>Последний вход:</strong> <?php echo date('d.m.Y H:i', strtotime($user_data['last_login'])); ?></p>
                    <?php endif; ?>
                </div>

                <a href="profile.php" class="btn btn-secondary" style="margin-top: 15px;">Редактировать профиль</a>
            </div>

            <!-- Карточка поддержки -->
            <div class="card">
                <div class="card-header">
                    <span class="card-icon">💬</span>
                    <h3>Поддержка</h3>
                </div>

                <p style="color: #666; margin-bottom: 20px;">
                    Нужна помощь с настройкой VPN или есть вопросы?
                    Мы готовы помочь!
                </p>

                <div style="display: flex; gap: 10px;">
                    <a href="#" class="btn btn-secondary">FAQ</a>
                    <a href="#" class="btn btn-secondary">Написать в поддержку</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>