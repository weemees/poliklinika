<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();

$specializations = getSpecializations();
?>

<h1>Управление специализациями</h1>

<div class="admin-actions">
    <a href="/admin/specialization_add.php" class="btn btn-primary">Добавить специализацию</a>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Описание</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($specializations as $spec): ?>
        <tr>
            <td><?= $spec['id'] ?></td>
            <td><?= htmlspecialchars($spec['name']) ?></td>
            <td><?= htmlspecialchars($spec['description'] ?? '') ?></td>
            <td>
                <a href="/admin/specialization_edit.php?id=<?= $spec['id'] ?>" class="btn btn-edit">Редактировать</a>
                <form action="/admin/specialization_delete.php" method="post" style="display: inline;">
                    <input type="hidden" name="id" value="<?= $spec['id'] ?>">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Удалить специализацию?')">Удалить</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/includes/footer.php'; ?>