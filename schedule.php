<?php
require_once __DIR__ . '/includes/header.php';
requireDoctor();

// Подключение к БД


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schedule = $_POST['schedule'];
    $stmt = $pdo->prepare("UPDATE doctors SET schedule = ? WHERE user_id = ?");
    $stmt->execute([$schedule, $_SESSION['user_id']]);
    $_SESSION['message'] = 'Расписание обновлено';
    header("Location: /doctor/schedule.php");
    exit;
}

$stmt = $pdo->prepare("SELECT schedule FROM doctors WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$schedule = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мое расписание</title>
    <link rel="stylesheet" href="/assets/css/doctor.css">
</head>
<body>
    
    
    <main>
        <h2>Настройка расписания</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message"><?= htmlspecialchars($_SESSION['message']) ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <form method="post">
            <textarea name="schedule" rows="10"><?= htmlspecialchars($schedule) ?></textarea>
            <button type="submit">Сохранить</button>
        </form>
    </main>
</body>
</html>