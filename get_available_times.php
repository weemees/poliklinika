<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['doctor_id']) || !isset($_GET['date'])) {
        throw new Exception('Не указаны ID врача или дата');
    }

    $doctorId = (int)$_GET['doctor_id'];
    $date = $_GET['date'];
    
    $stmt = $pdo->prepare("SELECT appointment_time FROM appointments WHERE doctor_id = ? AND appointment_date = ?");
    $stmt->execute([$doctorId, $date]);
    $busyTimes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Генерация доступных временных слотов
    $availableTimes = [];
    $start = strtotime('08:00');
    $end = strtotime('18:00');
    
    for ($time = $start; $time <= $end; $time += 1800) {
        $timeStr = date('H:i', $time);
        if (!in_array($timeStr, $busyTimes)) {
            $availableTimes[] = $timeStr;
        }
    }
    
    echo json_encode($availableTimes);
    exit;
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}