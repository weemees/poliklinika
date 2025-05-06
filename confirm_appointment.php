<?php
require_once 'includes/header.php';
requireAuth();

if (!isset($_SESSION['appointment_data'])) {
    header("Location: new_appointment.php");
    exit;
}

$appData = $_SESSION['appointment_data'];
$user = getUserInfo($_SESSION['user_id']);

// Получаем информацию о специализации/враче
if ($appData['doctor_id']) {
    $stmt = $pdo->prepare("SELECT u.name as doctor_name, s.name as specialization_name 
                          FROM users u
                          JOIN doctors d ON u.id = d.user_id
                          JOIN specializations s ON d.specialization_id = s.id
                          WHERE u.id = ?");
    $stmt->execute([$appData['doctor_id']]);
    $doctorInfo = $stmt->fetch();
} else {
    $stmt = $pdo->prepare("SELECT name as specialization_name FROM specializations WHERE id = ?");
    $stmt->execute([$appData['specialization_id']]);
    $specializationInfo = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Создаем запись
    $stmt = $pdo->prepare("INSERT INTO appointments 
        (patient_id, doctor_id, specialization_id, appointment_date, appointment_time, symptoms, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([
        $_SESSION['user_id'],
        $appData['doctor_id'] ?? null,
        $appData['specialization_id'],
        $appData['appointment_date'],
        $appData['appointment_time'],
        $appData['symptoms'] ?? ''
    ]);
    
    unset($_SESSION['appointment_data']);
    
    // Добавляем уведомление
    addNotification($_SESSION['user_id'], "Вы записаны на прием на " . 
        date('d.m.Y', strtotime($appData['appointment_date'])) . " в " . 
        date('H:i', strtotime($appData['appointment_time'])));
    
    header("Location: profile.php?success=1");
    exit;
}

$title = 'Подтверждение записи';
?>

<div class="confirmation-container">
    <h1>Подтверждение записи на прием</h1>
    
    <div class="appointment-details">
        <h2>Данные записи</h2>
        
        <div class="detail-item">
            <strong>Пациент:</strong> <?= htmlspecialchars($user['name']) ?>
        </div>
        
        <div class="detail-item">
            <strong>Телефон:</strong> <?= htmlspecialchars($user['phone']) ?>
        </div>
        
        <?php if ($appData['doctor_id']): ?>
        <div class="detail-item">
            <strong>Врач:</strong> <?= htmlspecialchars($doctorInfo['doctor_name']) ?>
        </div>
        <div class="detail-item">
            <strong>Специализация:</strong> <?= htmlspecialchars($doctorInfo['specialization_name']) ?>
        </div>
        <?php else: ?>
        <div class="detail-item">
            <strong>Специализация:</strong> <?= htmlspecialchars($specializationInfo['specialization_name']) ?>
        </div>
        <?php endif; ?>
        
        <div class="detail-item">
            <strong>Дата:</strong> <?= date('d.m.Y', strtotime($appData['appointment_date'])) ?>
        </div>
        
        <div class="detail-item">
            <strong>Время:</strong> <?= date('H:i', strtotime($appData['appointment_time'])) ?>
        </div>
        
        <?php if (!empty($appData['symptoms'])): ?>
        <div class="detail-item">
            <strong>Симптомы:</strong> <?= htmlspecialchars($appData['symptoms']) ?>
        </div>
        <?php endif; ?>
    </div>
    
    <form method="post" class="confirmation-form">
        <button type="submit" class="btn btn-primary">Подтвердить запись</button>
        <a href="new_appointment.php" class="btn btn-secondary">Изменить данные</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>