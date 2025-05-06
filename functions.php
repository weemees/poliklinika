<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

// Проверка авторизации пользователя
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Проверка является ли пользователь врачом
function isDoctor() {
    return isset($_SESSION['is_doctor']) && $_SESSION['is_doctor'];
}

// Проверка является ли пользователь администратором
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}

// Вход пользователя
function loginUser($userId, $isDoctor = false, $isAdmin = false) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['is_doctor'] = $isDoctor;
    $_SESSION['is_admin'] = $isAdmin;
}

// Выход пользователя
function logoutUser() {
    session_unset();
    session_destroy();
}

// Получение информации о пользователе
function getUserInfo($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

// Получение списка специализаций
function getSpecializations() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM specializations ORDER BY name");
    return $stmt->fetchAll();
}

// Проверка доступности времени приема у врача
function isTimeSlotAvailable($doctorId, $date, $time) {
    global $pdo;
    $duration = DEFAULT_DOCTOR_APPOINTMENT_DURATION;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments 
                          WHERE doctor_id = ? AND appointment_date = ? 
                          AND (
                              (appointment_time <= ? AND ADDTIME(appointment_time, ?) > ?)
                              OR 
                              (appointment_time < ADDTIME(?, ?) AND ADDTIME(appointment_time, ?) > ADDTIME(?, ?))
                          )");
    $stmt->execute([
        $doctorId, 
        $date, 
        $time, 
        "$duration:00:00", 
        $time,
        $time, 
        "$duration:00:00", 
        "$duration:00:00", 
        $time, 
        "$duration:00:00"
    ]);
    
    return $stmt->fetchColumn() == 0;
}

// Добавление уведомления
function addNotification($userId, $message) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->execute([$userId, $message]);
}