<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
require_once 'includes/header.php';
requireAuth();

$userId = $_SESSION['user_id'];
$user = getUserInfo($userId);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $birthDate = trim($_POST['birth_date']);
    $address = trim($_POST['address']);
    $insuranceNumber = trim($_POST['insurance_number']);
    
    // Валидация
    if (empty($name)) {
        $error = 'ФИО обязательно для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Введите корректный email';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET 
                name = ?, email = ?, birth_date = ?, address = ?, insurance_number = ?
                WHERE id = ?");
            $stmt->execute([
                $name,
                $email,
                $birthDate,
                $address,
                $insuranceNumber,
                $userId
            ]);
            
            $_SESSION['success_message'] = 'Профиль успешно обновлен';
            header("Location: profile.php");
            exit;
        } catch (PDOException $e) {
            $error = 'Ошибка при обновлении профиля: ' . $e->getMessage();
        }
    }
}

$title = 'Редактирование профиля';
?>

<div class="profile-edit-form">
    <h1>Редактирование профиля</h1>
    
    <?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label for="name">ФИО</label>
            <input type="text" id="name" name="name" required 
                   value="<?= htmlspecialchars($user['name']) ?>">
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required 
                   value="<?= htmlspecialchars($user['email']) ?>">
        </div>
        
        <div class="form-group">
    <label for="birth_date">Дата рождения</label>
    <input type="date" id="birth_date" name="birth_date" 
           value="<?= htmlspecialchars($user['birth_date']) ?>"
           min="1900-01-01" 
           max="<?= date('Y-m-d') ?>">
</div>
        
        <div class="form-group">
            <label for="address">Адрес</label>
            <input type="text" id="address" name="address" 
                   value="<?= htmlspecialchars($user['address']) ?>">
        </div>
        
        <div class="form-group">
            <label for="insurance_number">Номер страхового полиса</label>
            <input type="text" id="insurance_number" name="insurance_number" 
                   value="<?= htmlspecialchars($user['insurance_number']) ?>">
        </div>
        
        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
        <a href="profile.php" class="btn btn-secondary">Отмена</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>