<?php
require_once 'includes/header.php';
requireAuth();

$userId = $_SESSION['user_id'];
$user = getUserInfo($userId);

// Получаем заявки пользователя
$stmt = $pdo->prepare("SELECT a.*, s.name as specialization_name, 
                       CONCAT(u.name, ' (', s.name, ')') as doctor_name
                       FROM appointments a
                       LEFT JOIN specializations s ON a.specialization_id = s.id
                       LEFT JOIN users u ON a.doctor_id = u.id
                       WHERE a.patient_id = ?
                       ORDER BY a.appointment_date DESC, a.appointment_time DESC");
$stmt->execute([$userId]);
$appointments = $stmt->fetchAll();

// Помечаем уведомления как прочитанные
$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$userId]);
if (isset($_SESSION['appointment_message'])) {
    echo '<div class="alert alert-success">'.$_SESSION['appointment_message'].'</div>';
    unset($_SESSION['appointment_message']);
}
?>

<div class="profile-container">
    <div class="profile-sidebar">
        <div class="profile-info">
            <div class="profile-avatar">
                <?= substr($user['name'], 0, 1) ?>
            </div>
            <h2><?= htmlspecialchars($user['name']) ?></h2>
            <p><?= htmlspecialchars($user['phone']) ?></p>
            <p><?= htmlspecialchars($user['email']) ?></p>
        </div>
        
        <nav class="profile-nav">
            <a href="/profile.php" class="active">Мои записи</a>
            <a href="/profile_edit.php">Редактировать профиль</a>
            <a href="/new_appointment.php">Новая запись</a>
            <a href="/logout.php">Выход</a>
        </nav>
    </div>
    
    <div class="profile-content">
        <h1>Мои записи на прием</h1>
        
        <?php if (empty($appointments)): ?>
        <div class="alert alert-info">У вас нет записей на прием</div>
        <a href="/new_appointment.php" class="btn btn-primary">Записаться на прием</a>
        <?php else: ?>
        <div class="appointments-list">
            <?php foreach ($appointments as $app): ?>
            <div class="appointment-card">
                <div class="appointment-header">
                    <h3>
                        <?= date('d.m.Y', strtotime($app['appointment_date'])) ?>, 
                        <?= date('H:i', strtotime($app['appointment_time'])) ?>
                    </h3>
                    <span class="status-badge status-<?= $app['status'] ?>">
                        <?= $app['status'] === 'pending' ? 'На рассмотрении' : 
                           ($app['status'] === 'approved' ? 'Подтверждено' : 
                           ($app['status'] === 'completed' ? 'Завершено' : 'Отменено')) ?>
                    </span>
                </div>
                
                <div class="appointment-body">
                    <?php if ($app['doctor_id']): ?>
                    <p><strong>Врач:</strong> <?= htmlspecialchars($app['doctor_name']) ?></p>
                    <?php else: ?>
                    <p><strong>Специализация:</strong> <?= htmlspecialchars($app['specialization_name']) ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($app['symptoms'])): ?>
                    <p><strong>Симптомы:</strong> <?= htmlspecialchars($app['symptoms']) ?></p>
                    <?php endif; ?>
                </div>
                
                <?php if ($app['status'] === 'pending'): ?>
                <div class="appointment-actions">
                    <form action="/cancel_appointment.php" method="post">
                        <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                        <button type="submit" class="btn btn-danger">Отменить запись</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>