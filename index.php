<?php
require_once '../includes/header.php';
requireDoctor();

$userId = $_SESSION['user_id'];

// Получаем информацию о враче с более строгой проверкой
$stmt = $pdo->prepare("SELECT u.*, d.specialization_id, d.room_number, s.name as specialization_name
                       FROM users u
                       JOIN doctors d ON u.id = d.user_id
                       JOIN specializations s ON d.specialization_id = s.id
                       WHERE u.id = ? AND u.role = 'doctor' AND u.is_doctor = 1");
$stmt->execute([$userId]);
$doctor = $stmt->fetch();

if (!$doctor) {
    // Детальная диагностика проблемы
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        die("Ошибка: пользователь не найден.");
    } elseif ($user['role'] != 'doctor' || !$user['is_doctor']) {
        die("Ошибка: доступ запрещен. Ваша роль: " . $user['role'] . 
            ", is_doctor: " . $user['is_doctor'] . 
            ". Убедитесь, что вы врач и правильно зарегистрированы в системе.");
    } else {
        die("Ошибка: информация о враче не найдена в таблице doctors, хотя пользователь помечен как врач.");
    }
}

// Получаем заявки для этого врача
$currentDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$stmt = $pdo->prepare("SELECT a.*, u.name as patient_name, u.phone as patient_phone
                       FROM appointments a
                       JOIN users u ON a.patient_id = u.id
                       WHERE a.doctor_id = ? AND a.status = 'approved' AND a.appointment_date = ?
                       ORDER BY a.appointment_time");
$stmt->execute([$userId, $currentDate]);
$appointments = $stmt->fetchAll();
?>

<!-- Остальная часть кода остается без изменений -->

<div class="doctor-container">
    <div class="doctor-sidebar">
        <div class="doctor-info">
            <div class="doctor-avatar">
                <?= substr($doctor['name'], 0, 1) ?>
            </div>
            <h2>Доктор <?= htmlspecialchars($doctor['name']) ?></h2>
            <p><?= htmlspecialchars($doctor['specialization_name']) ?></p>
            <p>Кабинет: <?= htmlspecialchars($doctor['room_number']) ?></p>
        </div>
        
        <nav class="doctor-nav">
            <a href="/doctor/" class="active">Расписание</a>
            <a href="/doctor/patients.php">Мои пациенты</a>
            <a href="/doctor/schedule.php">Настройка расписания</a>
            <a href="/logout.php">Выход</a>
        </nav>
    </div>
    
    <div class="doctor-content">
        <h1>Мое расписание</h1>
        
        <div class="schedule-nav">
            <a href="?date=<?= date('Y-m-d', strtotime($currentDate . ' -1 day')) ?>" class="btn btn-secondary">← Предыдущий день</a>
            <h2><?= date('d.m.Y', strtotime($currentDate)) ?></h2>
            <a href="?date=<?= date('Y-m-d', strtotime($currentDate . ' +1 day')) ?>" class="btn btn-secondary">Следующий день →</a>
        </div>
        
        <?php if (empty($appointments)): ?>
        <div class="alert alert-info">На <?= date('d.m.Y', strtotime($currentDate)) ?> записей нет</div>
        <?php else: ?>
        <div class="appointments-list">
            <?php foreach ($appointments as $app): ?>
            <div class="appointment-card">
                <div class="appointment-header">
                    <h3>
                        <?= date('H:i', strtotime($app['appointment_time'])) ?>
                        - <?= date('H:i', strtotime($app['appointment_time']) + 60*30) ?>
                    </h3>
                    <span class="status-badge status-<?= $app['status'] ?>">
                        Подтверждено
                    </span>
                </div>
                
                <div class="appointment-body">
                    <p><strong>Пациент:</strong> <?= htmlspecialchars($app['patient_name']) ?></p>
                    <p><strong>Телефон:</strong> <?= htmlspecialchars($app['patient_phone']) ?></p>
                    
                    <?php if (!empty($app['symptoms'])): ?>
                    <p><strong>Жалобы:</strong> <?= htmlspecialchars($app['symptoms']) ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="appointment-actions">
                    <form action="complete_appointment.php" method="post">
                        <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                        <button type="submit" class="btn btn-complete">Завершить прием</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>