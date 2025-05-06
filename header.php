<?php
session_start();
require_once   'includes/db.php';
require_once  'includes/functions.php';
require_once  'includes/auth.php';

$check_auth = !isset($skip_auth_check) || $skip_auth_check === false;

// Проверяем новые уведомления для авторизованных пользователей
$hasUnreadNotifications = false;
if ($check_auth && isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications 
                          WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    $hasUnreadNotifications = $stmt->fetchColumn() > 0;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title . ' | ' : '' ?><?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/assets/css/style.css?v=1.0.1">
     <link rel="stylesheet" href="/assets/css/admin.css?v=1.0.1">
     <link rel="stylesheet" href="/assets/css/doctor.css?v=1.0.1">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="/"><?= SITE_NAME ?></a>
            </div>
            <nav class="nav">
                <?php if ($check_auth && isLoggedIn()): ?>
                    <a href="/profile.php">
                        Личный кабинет
                        <?php if ($hasUnreadNotifications): ?>
                        <span class="notification-badge">!</span>
                        <?php endif; ?>
                    </a>
                    <?php if (isDoctor() || isAdmin()): ?>
                        <a href="/doctor/">Кабинет врача</a>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                        <a href="/admin/">Админ-панель</a>
                    <?php endif; ?>
                    <a href="/logout.php">Выйти</a>
                <?php else: ?>
                    <a href="/login.php">Вход</a>
                    <a href="/register.php">Регистрация</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="main">