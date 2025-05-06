<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$error = '';
$phone = $_SESSION['appointment_phone'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $birthDate = trim($_POST['birth_date']);
    $address = trim($_POST['address']);
    $insuranceNumber = trim($_POST['insurance_number']);
    $password = trim($_POST['password']);
    $passwordConfirm = trim($_POST['password_confirm']);
    
    // Валидация
    if (empty($phone)) {
        $error = 'Телефон обязателен для заполнения';
    } elseif (!preg_match('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/', $phone)) {
        $error = 'Введите телефон в правильном формате';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Введите корректный email';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Пароли не совпадают';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен быть не менее 6 символов';
    } else {
        // Проверяем, есть ли пользователь с таким телефоном или email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ? OR email = ?");
        $stmt->execute([$phone, $email]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Пользователь с таким телефоном или email уже зарегистрирован';
        } else {
            // Создаем пользователя
            $stmt = $pdo->prepare("INSERT INTO users 
                (name, phone, email, birth_date, address, insurance_number, password) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $name,
                $phone,
                $email,
                $birthDate,
                $address,
                $insuranceNumber,
                password_hash($password, PASSWORD_DEFAULT)
            ]);
            
            $userId = $pdo->lastInsertId();
            loginUser($userId);
            
            // Создаем заявку если есть данные в сессии
            if (isset($_SESSION['appointment_data'])) {
                $appData = $_SESSION['appointment_data'];
                
                $stmt = $pdo->prepare("INSERT INTO appointments 
                    (patient_id, doctor_id, specialization_id, appointment_date, appointment_time, symptoms, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending')");
                $stmt->execute([
                    $userId,
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
        }
    }
}

$title = 'Регистрация пациента';
include 'includes/header.php';
?>

<div class="auth-form">
    <h1>Регистрация пациента</h1>
    
    <?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label for="name">ФИО</label>
            <input type="text" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label for="phone">Телефон</label>
            <input type="tel" id="phone" name="phone" required 
                   value="<?= htmlspecialchars($phone) ?>"
                   placeholder="+7 (999) 123-45-67">
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required 
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label for="birth_date">Дата рождения</label>
            <input type="date" id="birth_date" name="birth_date" required 
                   value="<?= htmlspecialchars($_POST['birth_date'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label for="address">Адрес</label>
            <input type="text" id="address" name="address" required 
                   value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label for="insurance_number">Номер страхового полиса</label>
            <input type="text" id="insurance_number" name="insurance_number" required 
                   value="<?= htmlspecialchars($_POST['insurance_number'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" required 
                   placeholder="Придумайте пароль (мин. 6 символов)">
        </div>
        
        <div class="form-group">
            <label for="password_confirm">Подтверждение пароля</label>
            <input type="password" id="password_confirm" name="password_confirm" required 
                   placeholder="Повторите пароль">
        </div>
        
        <div class="form-group">
            <div class="checkbox-wrapper">
                <input type="checkbox" id="reg_agreement" name="agreement" required>
                <label for="reg_agreement">Я согласен с <a href="privacy.php" target="_blank">политикой конфиденциальности</a></label>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
    </form>
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