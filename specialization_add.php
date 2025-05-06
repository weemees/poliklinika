<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $_SESSION['error'] = 'Название специализации обязательно';
    } else {
        $stmt = $pdo->prepare("INSERT INTO specializations (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        
        $_SESSION['success'] = 'Специализация добавлена';
        header("Location: /admin/specializations.php");
        exit;
    }
}
?>

<h1>Добавление специализации</h1>

<form method="post">
    <div class="form-group">
        <label for="name">Название</label>
        <input type="text" id="name" name="name" required>
    </div>
    
    <div class="form-group">
        <label for="description">Описание</label>
        <textarea id="description" name="description" rows="4"></textarea>
    </div>
    
    <button type="submit" class="btn btn-primary">Добавить</button>
    <a href="/admin/specializations.php" class="btn btn-secondary">Отмена</a>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>