<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();

$specializations = getSpecializations();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)$_POST['user_id'];
    $specializationId = !empty($_POST['specialization_id']) ? (int)$_POST['specialization_id'] : null;
    $roomNumber = trim($_POST['room_number']);

    try {
        $pdo->beginTransaction();

        // 1. Проверяем существование пользователя
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_doctor = 0");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception('Пользователь не найден или уже является врачом');
        }

        // 2. Проверяем специализацию
        if ($specializationId) {
            $stmt = $pdo->prepare("SELECT id FROM specializations WHERE id = ?");
            $stmt->execute([$specializationId]);
            if (!$stmt->fetch()) {
                throw new Exception('Выбранная специализация не существует');
            }
        }

        // 3. Обновляем пользователя
        $pdo->prepare("UPDATE users SET is_doctor = 1 WHERE id = ?")->execute([$userId]);

        // 4. Добавляем врача
        $stmt = $pdo->prepare("INSERT INTO doctors (user_id, specialization_id, room_number) 
                             VALUES (?, ?, ?)
                             ON DUPLICATE KEY UPDATE 
                             specialization_id = VALUES(specialization_id),
                             room_number = VALUES(room_number)");
        $stmt->execute([$userId, $specializationId, $roomNumber]);

        $pdo->commit();
        $_SESSION['success'] = 'Врач успешно добавлен';
        header("Location: /admin/doctors.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Получаем только пациентов (не врачей и не админов)
$patients = $pdo->query("SELECT id, name, phone FROM users WHERE is_doctor = 0 AND is_admin = 0 ORDER BY name")->fetchAll();
?>

<div class="admin-container">
    <h1>Добавление врача</h1>

    <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="admin-form">
        <div class="form-group">
            <label for="user_id">Пациент:</label>
            <select id="user_id" name="user_id" required class="form-control">
                <option value="">-- Выберите пациента --</option>
                <?php foreach ($patients as $patient): ?>
                <option value="<?= $patient['id'] ?>" <?= isset($_POST['user_id']) && $_POST['user_id'] == $patient['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($patient['name']) ?> (тел: <?= htmlspecialchars($patient['phone']) ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="specialization_id">Специализация:</label>
            <select id="specialization_id" name="specialization_id" class="form-control">
                <option value="">-- Без специализации --</option>
                <?php foreach ($specializations as $spec): ?>
                <option value="<?= $spec['id'] ?>" <?= isset($_POST['specialization_id']) && $_POST['specialization_id'] == $spec['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($spec['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="room_number">Кабинет:</label>
            <input type="text" id="room_number" name="room_number" required 
                   value="<?= htmlspecialchars($_POST['room_number'] ?? '') ?>" class="form-control">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Добавить врача</button>
            <a href="/admin/doctors.php" class="btn btn-secondary">Отмена</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>