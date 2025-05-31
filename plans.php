<?php
require_once 'config/config.php';
require_once 'classes/User.php';
require_once 'classes/Plan.php';

$user = new User();
$plan = new Plan();

// Проверяем авторизацию
if (!$current_user = $user->checkSession()) {
    header('Location: login.php');
    exit;
}

$plans = $plan->getAllPlans();
$user_subscription = $plan->getUserSubscription($current_user['id']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тарифные планы - VPN Service</title>
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
        
        .nav-links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        
        .nav-links a:hover {
            color: #764ba2;
            transform: translateX(-3px);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .page-header h1 {
            font-size: 36px;
            color: #333;
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .page-header p {
            font-size: 18px;
            color: #666;
        }
        
        .alert {
            max-width: 800px;
            margin: 0 auto 40px;
            padding: 20px;
            border-radius: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        
        .alert-icon {
            font-size: 24px;
        }
        
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .plan-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .plan-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .plan-card.popular {
            border: 2px solid #667eea;
        }
        
        .popular-badge {
            position: absolute;
            top: 20px;
            right: -30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 40px;
            font-size: 12px;
            font-weight: 600;
            transform: rotate(45deg);
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .plan-card h3 {
            font-size: 24px;
            color: #333;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .price-wrapper {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .price {
            font-size: 48px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }
        
        .price-currency {
            font-size: 24px;
            font-weight: 500;
        }
        
        .duration {
            color: #666;
            font-size: 16px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .features {
            list-style: none;
            margin-bottom: 30px;
            flex-grow: 1;
        }
        
        .features li {
            padding: 12px 0;
            color: #555;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .features li:last-child {
            border-bottom: none;
        }
        
        .features li::before {
            content: '✓';
            color: #22c55e;
            font-weight: 700;
            font-size: 18px;
        }
        
        .btn {
            width: 100%;
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-disabled {
            background: #e2e8f0;
            color: #94a3b8;
            cursor: not-allowed;
        }
        
        .btn-disabled:hover {
            transform: none;
            box-shadow: none;
        }
        
        .current-plan-badge {
            background: #dcfce7;
            color: #166534;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 28px;
            }
            
            .plans-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .plan-card {
                padding: 30px 20px;
            }
            
            .price {
                font-size: 36px;
            }
        }
        
        /* Анимация появления */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .plan-card {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        .plan-card:nth-child(1) {
            animation-delay: 0.1s;
        }
        
        .plan-card:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .plan-card:nth-child(3) {
            animation-delay: 0.3s;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">🔐 VPN Service</div>
            <div class="nav-links">
                <a href="dashboard.php">← Назад к панели</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1>Выберите тарифный план</h1>
            <p>Получите безопасный и быстрый доступ к интернету с нашими VPN серверами</p>
        </div>

        <?php if ($user_subscription): ?>
            <div class="alert alert-info">
                <span class="alert-icon">✨</span>
                <div>
                    <strong>У вас активная подписка "<?php echo htmlspecialchars($user_subscription['plan_name']); ?>"</strong><br>
                    Действует до <?php echo date('d.m.Y', strtotime($user_subscription['expires_at'])); ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="plans-grid">
            <?php 
            $planIndex = 0;
            foreach ($plans as $p): 
                $planIndex++;
                $isCurrentPlan = $user_subscription && $user_subscription['plan_id'] == $p['id'];
                $isPopular = $planIndex == 2; // Делаем второй план "популярным"
            ?>
                <div class="plan-card <?php echo $isPopular ? 'popular' : ''; ?>">
                    <?php if ($isPopular): ?>
                        <div class="popular-badge">Популярный</div>
                    <?php endif; ?>
                    
                    <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                    
                    <?php if ($isCurrentPlan): ?>
                        <div class="current-plan-badge">Ваш текущий план</div>
                    <?php endif; ?>
                    
                    <div class="price-wrapper">
                        <div class="price">
                            <?php echo number_format($p['price'], 0); ?><span class="price-currency"> ₽</span>
                        </div>
                    </div>
                    
                    <div class="duration">на <?php echo $p['duration_days']; ?> дней</div>

                    <ul class="features">
                        <?php
                        $features = json_decode($p['features'], true) ?: [];
                        if (empty($features)) {
                            // Добавляем дефолтные фичи если их нет в БД
                            $features = [
                                'Безлимитный трафик',
                                'Высокая скорость',
                                'Защита данных',
                                'Техподдержка 24/7'
                            ];
                        }
                        foreach ($features as $feature): ?>
                            <li><?php echo htmlspecialchars($feature); ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if ($isCurrentPlan): ?>
                        <button class="btn btn-disabled" disabled>
                            Текущий план
                        </button>
                    <?php else: ?>
                        <button onclick="buyPlan(<?php echo $p['id']; ?>)" class="btn btn-primary">
                            Выбрать план
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function buyPlan(planId) {
            if (confirm('Вы уверены, что хотите приобрести этот план?')) {
                fetch('buy-plan.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        plan_id: planId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('План успешно приобретен! Спасибо за покупку.');
                        location.reload();
                    } else {
                        alert('Ошибка: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Произошла ошибка при обработке запроса. Пожалуйста, попробуйте позже.');
                    console.error('Error:', error);
                });
            }
        }
    </script>
</body>
</html>