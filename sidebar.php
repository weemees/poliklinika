<div class="doctor-info">
    <?php
    $stmt = $pdo->prepare("SELECT u.*, d.specialization_id, d.room_number, s.name as specialization_name
                          FROM users u
                          JOIN doctors d ON u.id = d.user_id
                          LEFT JOIN specializations s ON d.specialization_id = s.id
                          WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $doctor = $stmt->fetch();
    ?>
    
    <div class="doctor-avatar">
        <?= substr($doctor['name'], 0, 1) ?>
    </div>
    <h2>Доктор <?= htmlspecialchars($doctor['name']) ?></h2>
    <p><?= htmlspecialchars($doctor['specialization_name'] ?? 'Специализация не указана') ?></p>
    <p>Кабинет: <?= htmlspecialchars($doctor['room_number'] ?? 'Не указан') ?></p>
</div>

<nav class="doctor-nav">
    <a href="/doctor/" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Расписание</a>
    <a href="/doctor/patients.php" class="<?= basename($_SERVER['PHP_SELF']) == 'patients.php' ? 'active' : '' ?>">Мои пациенты</a>
    <a href="/doctor/schedule.php" class="<?= basename($_SERVER['PHP_SELF']) == 'schedule.php' ? 'active' : '' ?>">Настройка расписания</a>
    <a href="/logout.php">Выход</a>
</nav>