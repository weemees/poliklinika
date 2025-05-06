<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    $_SESSION['error'] = 'Неверный запрос';
    header("Location: /admin/specializations.php");
    exit;
}

$specId = (int)$_POST['id'];

// Проверяем, есть ли врачи с этой специализацией
$stmt = $pdo->prepare("SELECT COUNT(*) FROM doctors WHERE specialization_id = ?");
$stmt->execute([$specId]);
$count = $stmt->fetchColumn();

if ($count > 0) {
    $_SESSION['error'] = 'Нельзя удалить специализацию, так как есть врачи с этой специализацией';
    header("Location: /admin/specializations.php");
    exit;
}

// Удаляем специализацию
$stmt = $pdo->prepare("DELETE FROM specializations WHERE id = ?");
$stmt->execute([$specId]);

$_SESSION['success'] = 'Специализация удалена';
header("Location: /admin/specializations.php");
exit;
?>