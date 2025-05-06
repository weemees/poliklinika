<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$error = '';
$phone = '';

if (!empty($_SESSION['appointment_phone'])) {
    $phone = $_SESSION['appointment_phone'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        loginUser($user['id'], $user['is_doctor'], $user['is_admin']);
        
        if (!empty($_SESSION['appointment_data'])) {
            $appData = $_SESSION['appointment_data'];
            
            $stmt = $pdo->prepare("INSERT INTO appointments 
                (patient_id, doctor_id, specialization_id, appointment_date, appointment_time, symptoms, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([
                $user['id'],
                $appData['doctor_id'] ?? null,
                $appData['specialization_id'],
                $appData['appointment_date'],
                $appData['appointment_time'],
                $appData['symptoms'] ?? ''
            ]);
            
            unset($_SESSION['appointment_data']);
            header("Location: profile.php");
            exit;
        }
        
        header("Location: profile.php");
        exit;
    } else {
        $error = 'Неверный телефон или пароль';
    }
}

$title = 'Вход в личный кабинет';
include 'includes/header.php';
?>

<div class="auth-form">
    <h1>Вход в личный кабинет</h1>
    
    <?php if (isset($_SESSION['login_redirect'])): ?>
    <div class="alert alert-info">Для завершения записи на прием войдите в систему</div>
    <?php unset($_SESSION['login_redirect']); ?>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label for="phone">Телефон</label>
            <input type="tel" id="phone" name="phone" required 
                   value="<?= htmlspecialchars($phone) ?>"
                   placeholder="+7 (999) 123-45-67">
        </div>
        
        <div class="form-group">
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" required 
                   placeholder="Ваш пароль">
        </div>
        
        <button type="submit" class="btn btn-primary">Войти</button>
    </form>
    
    <div class="auth-links">
        Нет аккаунта? <a href="/register.php">Зарегистрируйтесь</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Маска для телефона
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('input', function(e) {
        let value = this.value.replace(/\D/g, '');
        if (value.length > 0) value = '+7' + value.substring(1);
        
        let formattedValue = '';
        if (value.length > 1) {
            formattedValue += value.substring(0, 2) + ' ';
            if (value.length > 2) formattedValue += '(' + value.substring(2, 5);
            if (value.length > 5) formattedValue += ') ' + value.substring(5, 8);
            if (value.length > 8) formattedValue += '-' + value.substring(8, 10);
            if (value.length > 10) formattedValue += '-' + value.substring(10, 12);
        }
        this.value = formattedValue;
    });
});
</script>

<?php include 'includes/footer.php'; ?>