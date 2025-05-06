<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();

$doctorId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$specializations = getSpecializations();

// Проверка существования врача
$stmt = $pdo->prepare("SELECT u.*, d.specialization_id, d.room_number 
                      FROM users u
                      JOIN doctors d ON u.id = d.user_id
                      WHERE u.id = ? AND u.is_doctor = 1");
$stmt->execute([$doctorId]);
$doctor = $stmt->fetch();

if (!$doctor) {
    $_SESSION['error'] = 'Врач не найден';
    header("Location: /admin/doctors.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $specializationId = !empty($_POST['specialization_id']) ? (int)$_POST['specialization_id'] : null;
    $roomNumber = trim($_POST['room_number']);

    try {
        $pdo->beginTransaction();
        
        // Проверяем специализацию
        if ($specializationId) {
            $stmt = $pdo->prepare("SELECT id FROM specializations WHERE id = ?");
            $stmt->execute([$specializationId]);
            if (!$stmt->fetch()) {
                throw new Exception('Выбранная специализация не существует');
            }
        }

        // Обновляем данные врача
        $stmt = $pdo->prepare("UPDATE doctors SET 
                             specialization_id = ?,
                             room_number = ?
                             WHERE user_id = ?");
        $stmt->execute([$specializationId, $roomNumber, $doctorId]);

        $pdo->commit();
        $_SESSION['success'] = 'Данные врача обновлены';
        header("Location: /admin/doctors.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}
?>

<div class="admin-container">
    <h1>Редактирование врача: <?= htmlspecialchars($doctor['name']) ?></h1>

    <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="admin-form">
        <div class="form-group">
            <label>ID:</label>
            <input type="text" value="<?= $doctor['id'] ?>" readonly class="form-control">
        </div>

        <div class="form-group">
            <label>ФИО:</label>
            <input type="text" value="<?= htmlspecialchars($doctor['name']) ?>" readonly class="form-control">
        </div>

        <div class="form-group">
            <label for="specialization_id">Специализация:</label>
            <select id="specialization_id" name="specialization_id" class="form-control">
                <option value="">-- Без специализации --</option>
                <?php foreach ($specializations as $spec): ?>
                <option value="<?= $spec['id'] ?>" 
                    <?= $spec['id'] == $doctor['specialization_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($spec['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="room_number">Кабинет:</label>
            <input type="text" id="room_number" name="room_number" 
                   value="<?= htmlspecialchars($doctor['room_number']) ?>" required class="form-control">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
            <a href="/admin/doctors.php" class="btn btn-secondary">Отмена</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>