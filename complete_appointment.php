<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

// Проверка авторизации врача
if (!isLoggedIn() || (!isDoctor() && !isAdmin())) {
    $_SESSION['error'] = 'Доступ запрещен';
    header("Location: /login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['appointment_id'])) {
    $_SESSION['error'] = 'Неверный запрос';
    header("Location: /doctor/");
    exit;
}

$appointmentId = (int)$_POST['appointment_id'];
$doctorId = $_SESSION['user_id'];

// Проверяем, что запись принадлежит врачу
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ? AND doctor_id = ?");
$stmt->execute([$appointmentId, $doctorId]);
$appointment = $stmt->fetch();

if (!$appointment) {
    $_SESSION['error'] = 'Запись не найдена или у вас нет прав для ее завершения';
    header("Location: /doctor/");
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Завершаем прием
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE id = ?");
    $stmt->execute([$appointmentId]);

    // Добавляем уведомление пациенту
    $message = "Ваш прием " . date('d.m.Y', strtotime($appointment['appointment_date'])) . 
               " в " . date('H:i', strtotime($appointment['appointment_time'])) . " завершен";
    $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")
        ->execute([$appointment['patient_id'], $message]);
    
    $pdo->commit();
    
    $_SESSION['success'] = 'Прием успешно завершен';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Ошибка при завершении приема: ' . $e->getMessage();
}

header("Location: /doctor/");
exit;
?>