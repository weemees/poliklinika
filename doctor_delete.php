<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    $_SESSION['error'] = 'Неверный запрос';
    header("Location: /admin/doctors.php");
    exit;
}

$doctorId = (int)$_POST['id'];

try {
    $pdo->beginTransaction();

    // 1. Отменяем все будущие записи к врачу
    $pdo->prepare("UPDATE appointments SET status = 'canceled', cancel_reason = 'Врач удален из системы' 
                  WHERE doctor_id = ? AND appointment_date >= CURDATE() AND status = 'approved'")
       ->execute([$doctorId]);

    // 2. Удаляем из таблицы врачей
    $pdo->prepare("DELETE FROM doctors WHERE user_id = ?")->execute([$doctorId]);

    // 3. Снимаем флаг врача
    $pdo->prepare("UPDATE users SET is_doctor = 0 WHERE id = ?")->execute([$doctorId]);

    $pdo->commit();
    
    $_SESSION['success'] = 'Врач и все его будущие записи успешно удалены';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Ошибка при удалении врача: ' . $e->getMessage();
}

header("Location: /admin/doctors.php");
exit;
?>