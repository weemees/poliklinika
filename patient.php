<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
requireAuth();
requireDoctor(); // Функция проверки, что пользователь - врач

// Проверяем параметр id
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: /doctor/patients.php");
    exit;
}

$patientId = (int)$_GET['id'];

// Получаем информацию о пациенте
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$patientId]);
$patient = $stmt->fetch();

if (!$patient) {
    $_SESSION['error_message'] = 'Пациент не найден';
    header("Location: /doctor/patients.php");
    exit;
}

// Получаем историю записей
$stmt = $pdo->prepare("
    SELECT a.*, s.name as specialization_name 
    FROM appointments a
    LEFT JOIN specializations s ON a.specialization_id = s.id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute([$patientId]);
$appointments = $stmt->fetchAll();

$title = 'История пациента: ' . htmlspecialchars($patient['name']);
?>

<div class="container mt-4">
    <h1>История пациента: <?= htmlspecialchars($patient['name']) ?></h1>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_message'] ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <div class="card mt-3">
        <div class="card-header">
            <h3>Контактная информация</h3>
        </div>
        <div class="card-body">
            <p><strong>Телефон:</strong> <?= htmlspecialchars($patient['phone']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($patient['email'] ?? 'не указан') ?></p>
        </div>
    </div>
    
    <div class="card mt-3">
        <div class="card-header">
            <h3>История записей</h3>
        </div>
        <div class="card-body">
            <?php if (empty($appointments)): ?>
                <p>Нет записей в истории</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Время</th>
                                <th>Специализация</th>
                                <th>Статус</th>
                                <th>Симптомы</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appointment): ?>
                                <tr>
                                    <td><?= date('d.m.Y', strtotime($appointment['appointment_date'])) ?></td>
                                    <td><?= date('H:i', strtotime($appointment['appointment_time'])) ?></td>
                                    <td><?= htmlspecialchars($appointment['specialization_name']) ?></td>
                                    <td>
                                        <?php switch ($appointment['status']):
                                            case 'completed': ?>
                                                <span class="badge bg-success">Завершён</span>
                                                <?php break; ?>
                                            case 'canceled': ?>
                                                <span class="badge bg-danger">Отменён</span>
                                                <?php break; ?>
                                            default: ?>
                                                <span class="badge bg-warning"><?= $appointment['status'] ?></span>
                                        <?php endswitch; ?>
                                    </td>
                                    <td><?= htmlspecialchars($appointment['symptoms'] ?? '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <a href="/doctor/patients.php" class="btn btn-secondary mt-3">Назад к списку пациентов</a>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>