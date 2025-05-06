<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();

$stmt = $pdo->query("SELECT a.*, 
                     u.name as patient_name, 
                     d.name as doctor_name,
                     s.name as specialization_name
                     FROM appointments a
                     LEFT JOIN users u ON a.patient_id = u.id
                     LEFT JOIN users d ON a.doctor_id = d.id
                     LEFT JOIN specializations s ON a.specialization_id = s.id
                     ORDER BY a.appointment_date DESC, a.appointment_time DESC");
$appointments = $stmt->fetchAll();
?>

<h1>Управление записями</h1>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Пациент</th>
            <th>Врач/Специализация</th>
            <th>Дата и время</th>
            <th>Статус</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($appointments as $app): ?>
        <tr>
            <td><?= $app['id'] ?></td>
            <td><?= htmlspecialchars($app['patient_name']) ?></td>
            <td>
                <?= $app['doctor_id'] ? 
                    htmlspecialchars($app['doctor_name']) : 
                    htmlspecialchars($app['specialization_name']) ?>
            </td>
            <td>
                <?= date('d.m.Y H:i', strtotime($app['appointment_date'] . ' ' . $app['appointment_time'])) ?>
            </td>
            <td>
                <span class="status-badge status-<?= $app['status'] ?>">
                    <?= $app['status'] === 'pending' ? 'На рассмотрении' : 
                       ($app['status'] === 'approved' ? 'Подтверждено' : 
                       ($app['status'] === 'completed' ? 'Завершено' : 'Отменено')) ?>
                </span>
            </td>
            <td>
                <?php if ($app['status'] === 'pending'): ?>
                    <form action="/admin/approve_appointment.php" method="post" style="display: inline;">
                        <input type="hidden" name="id" value="<?= $app['id'] ?>">
                        <button type="submit" class="btn btn-success">Подтвердить</button>
                    </form>
                <?php endif; ?>
                <form action="/admin/cancel_appointment.php" method="post" style="display: inline;">
                    <input type="hidden" name="id" value="<?= $app['id'] ?>">
                    <button type="submit" class="btn btn-danger">Отменить</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/includes/footer.php'; ?>