<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['appointment_id']) || !isset($_POST['action'])) {
    header("Location: index.php");
    exit;
}

$appointmentId = (int)$_POST['appointment_id'];
$action = $_POST['action'];

// Получаем информацию о записи
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
$stmt->execute([$appointmentId]);
$appointment = $stmt->fetch();

if (!$appointment) {
    $_SESSION['admin_message'] = 'Запись не найдена';
    header("Location: index.php");
    exit;
}

// Обработка действий
switch ($action) {
    case 'approve':
        $pdo->prepare("UPDATE appointments SET status = 'approved' WHERE id = ?")->execute([$appointmentId]);
        addNotification($appointment['patient_id'], 
            "Ваша запись на " . date('d.m.Y', strtotime($appointment['appointment_date'])) . 
            " в " . date('H:i', strtotime($appointment['appointment_time'])) . " подтверждена");
        $_SESSION['admin_message'] = 'Запись подтверждена';
        break;
        
    case 'reject':
        $reason = $_POST['reason'] ?? 'Причина не указана';
        $pdo->prepare("UPDATE appointments SET status = 'canceled' WHERE id = ?")->execute([$appointmentId]);
        addNotification($appointment['patient_id'], 
            "Ваша запись на " . date('d.m.Y', strtotime($appointment['appointment_date'])) . 
            " в " . date('H:i', strtotime($appointment['appointment_time'])) . " отменена. Причина: " . $reason);
        $_SESSION['admin_message'] = 'Запись отменена';
        break;
        
    case 'complete':
        $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE id = ?")->execute([$appointmentId]);
        addNotification($appointment['patient_id'], 
            "Ваш прием " . date('d.m.Y', strtotime($appointment['appointment_date'])) . 
            " в " . date('H:i', strtotime($appointment['appointment_time'])) . " завершен");
        $_SESSION['admin_message'] = 'Прием завершен';
        break;
        
    default:
        $_SESSION['admin_message'] = 'Неизвестное действие';
}

header("Location: index.php");
exit;