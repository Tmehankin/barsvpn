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
    <!-- Ваши стили из dashboard.php -->
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
        <h1>Выберите тарифный план</h1>

        <?php if ($user_subscription): ?>
            <div class="alert alert-info">
                У вас активная подписка "<?php echo $user_subscription['plan_name']; ?>"
                до <?php echo date('d.m.Y', strtotime($user_subscription['expires_at'])); ?>
            </div>
        <?php endif; ?>

        <div class="plans-grid">
            <?php foreach ($plans as $p): ?>
                <div class="plan-card">
                    <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                    <div class="price"><?php echo number_format($p['price'], 0); ?> ₽</div>
                    <div class="duration">на <?php echo $p['duration_days']; ?> дней</div>

                    <ul class="features">
                        <?php
                        $features = json_decode($p['features'], true);
                        foreach ($features as $feature): ?>
                            <li><?php echo htmlspecialchars($feature); ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <button onclick="buyPlan(<?php echo $p['id']; ?>)" class="btn btn-primary">
                        Купить план
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function buyPlan(planId) {
            if (confirm('Купить этот план?')) {
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
                            alert('План успешно приобретен!');
                            location.reload();
                        } else {
                            alert('Ошибка: ' + data.message);
                        }
                    });
            }
        }
    </script>
</body>

</html>