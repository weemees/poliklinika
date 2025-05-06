<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($userId === 0) {
    $_SESSION['error'] = 'Пользователь не указан';
    header("Location: /admin/users.php");
    exit;
}

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = 'Пользователь не найден';
    header("Location: /admin/users.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $isDoctor = isset($_POST['is_doctor']) ? 1 : 0;
    $isAdmin = isset($_POST['is_admin']) ? 1 : 0;

    // Обновляем данные
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, is_doctor = ?, is_admin = ? WHERE id = ?");
    $stmt->execute([$name, $email, $isDoctor, $isAdmin, $userId]);

    // Если назначили врачом, добавляем в таблицу doctors
    if ($isDoctor) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO doctors (user_id) VALUES (?)");
        $stmt->execute([$userId]);
    }

    $_SESSION['success'] = 'Данные пользователя обновлены';
    header("Location: /admin/users.php");
    exit;
}
?>

<h1>Редактирование пользователя</h1>

<form method="post">
    <div class="form-group">
        <label for="name">ФИО</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
    </div>
    
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
    </div>
    
    <div class="form-group">
        <label>
            <input type="checkbox" name="is_doctor" <?= $user['is_doctor'] ? 'checked' : '' ?>>
            Врач
        </label>
    </div>
    
    <div class="form-group">
        <label>
            <input type="checkbox" name="is_admin" <?= $user['is_admin'] ? 'checked' : '' ?>>
            Администратор
        </label>
    </div>
    
    <button type="submit" class="btn btn-primary">Сохранить</button>
    <a href="/admin/users.php" class="btn btn-secondary">Отмена</a>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>