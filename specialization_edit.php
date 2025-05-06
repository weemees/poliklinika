<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();

$specId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($specId === 0) {
    $_SESSION['error'] = 'Специализация не указана';
    header("Location: /admin/specializations.php");
    exit;
}

// Получаем данные специализации
$stmt = $pdo->prepare("SELECT * FROM specializations WHERE id = ?");
$stmt->execute([$specId]);
$spec = $stmt->fetch();

if (!$spec) {
    $_SESSION['error'] = 'Специализация не найдена';
    header("Location: /admin/specializations.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $_SESSION['error'] = 'Название специализации обязательно';
    } else {
        $stmt = $pdo->prepare("UPDATE specializations SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $description, $specId]);
        
        $_SESSION['success'] = 'Специализация обновлена';
        header("Location: /admin/specializations.php");
        exit;
    }
}
?>

<h1>Редактирование специализации</h1>

<form method="post">
    <div class="form-group">
        <label for="name">Название</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($spec['name']) ?>" required>
    </div>
    
    <div class="form-group">
        <label for="description">Описание</label>
        <textarea id="description" name="description" rows="4"><?= htmlspecialchars($spec['description'] ?? '') ?></textarea>
    </div>
    
    <button type="submit" class="btn btn-primary">Сохранить</button>
    <a href="/admin/specializations.php" class="btn btn-secondary">Отмена</a>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>