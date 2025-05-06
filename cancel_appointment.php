<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    $_SESSION['error'] = 'Неверный запрос';
    header("Location: /admin/appointments.php");
    exit;
}

$appointmentId = (int)$_POST['id'];
$reason = $_POST['reason'] ?? 'Отменено администратором';

// Получаем информацию о записи
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
$stmt->execute([$appointmentId]);
$appointment = $stmt->fetch();

if (!$appointment) {
    $_SESSION['error'] = 'Запись не найдена';
    header("Location: /admin/appointments.php");
    exit;
}

// Отменяем запись
$stmt = $pdo->prepare("UPDATE appointments SET status = 'canceled', cancel_reason = ? WHERE id = ?");
$stmt->execute([$reason, $appointmentId]);

// Добавляем уведомление пациенту
$message = "Ваша запись на " . date('d.m.Y', strtotime($appointment['appointment_date'])) . 
           " в " . date('H:i', strtotime($appointment['appointment_time'])) . " отменена. Причина: " . $reason;
$pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")
    ->execute([$appointment['patient_id'], $message]);

$_SESSION['success'] = 'Запись успешно отменена';
header("Location: /admin/appointments.php");
exit;
?>