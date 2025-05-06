<?php
require_once 'functions.php';

// Перенаправление если пользователь не авторизован
function requireAuth() {
    if (!isLoggedIn()) {
        $_SESSION['login_redirect'] = true;
        header("Location: login.php");
        exit;
    }
}

// Перенаправление если пользователь не врач
function requireDoctor() {
    requireAuth();
    if (!isDoctor() && !isAdmin()) {
        header("Location: profile.php");
        exit;
    }
}

// Перенаправление если пользователь не администратор
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        header("Location: profile.php");
        exit;
    }
}