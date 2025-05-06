<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();

$users = getAllUsers();
?>

<h1>Управление пользователями</h1>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>ФИО</th>
            <th>Телефон</th>
            <th>Email</th>
            <th>Роль</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['name']) ?></td>
            <td><?= htmlspecialchars($user['phone']) ?></td>
            <td><?= htmlspecialchars($user['email'] ?? 'Не указан') ?></td>
            <td>
                <?= $user['is_admin'] ? 'Админ' : ($user['is_doctor'] ? 'Врач' : 'Пациент') ?>
            </td>
            <td>
                <a href="/admin/user_edit.php?id=<?= $user['id'] ?>" class="btn btn-edit">Редактировать</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/includes/footer.php'; ?>