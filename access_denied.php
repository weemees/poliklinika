<?php
require_once __DIR__ . '/includes/header.php';

$message = $_SESSION['access_denied']['message'] ?? 'Доступ запрещен';
$redirect = $_SESSION['access_denied']['redirect'] ?? '/profile.php';
unset($_SESSION['access_denied']);
?>

<div class="alert alert-danger">
    <h2>Ошибка доступа</h2>
    <p><?= htmlspecialchars($message) ?></p>
    <a href="<?= htmlspecialchars($redirect) ?>" class="btn btn-primary">Вернуться</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>