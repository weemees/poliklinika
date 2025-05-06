<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();

// Получаем полную информацию о врачах
$doctors = $pdo->query("
    SELECT 
        u.id, 
        u.name, 
        u.email, 
        u.phone,
        d.room_number,
        d.specialization_id,
        s.name as specialization_name,
        (SELECT COUNT(*) FROM appointments WHERE doctor_id = u.id) as appointments_count
    FROM users u
    JOIN doctors d ON u.id = d.user_id
    LEFT JOIN specializations s ON d.specialization_id = s.id
    WHERE u.is_doctor = 1
    ORDER BY u.name
")->fetchAll();
?>

<div class="admin-container">
    <h1>Управление врачами</h1>

    <div class="admin-actions">
        <a href="/admin/doctor_add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Добавить врача
        </a>
    </div>

    <?php if (empty($doctors)): ?>
        <div class="alert alert-info">Нет зарегистрированных врачей</div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ФИО</th>
                    <th>Контакты</th>
                    <th>Специализация</th>
                    <th>Кабинет</th>
                    <th>Записей</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($doctors as $doctor): ?>
                <tr>
                    <td><?= $doctor['id'] ?></td>
                    <td><?= htmlspecialchars($doctor['name']) ?></td>
                    <td>
                        <div><?= htmlspecialchars($doctor['phone']) ?></div>
                        <div><?= htmlspecialchars($doctor['email'] ?? '') ?></div>
                    </td>
                    <td><?= htmlspecialchars($doctor['specialization_name'] ?? 'Не указана') ?></td>
                    <td><?= htmlspecialchars($doctor['room_number']) ?></td>
                    <td><?= $doctor['appointments_count'] ?></td>
                    <td class="actions">
                        <a href="/admin/doctor_edit.php?id=<?= $doctor['id'] ?>" class="btn btn-sm btn-edit">
                            <i class="fas fa-edit"></i> Редактировать
                        </a>
                        <form action="/admin/doctor_delete.php" method="post" class="inline-form">
                            <input type="hidden" name="id" value="<?= $doctor['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger" 
                                    onclick="return confirm('Удалить врача? Все его записи будут отменены!')">
                                <i class="fas fa-trash"></i> Удалить
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>