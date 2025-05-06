<?php
require_once 'includes/header.php';
require_once 'includes/db.php';
requireAuth();

// Получаем список специализаций
$specializations = $pdo->query("SELECT * FROM specializations")->fetchAll(PDO::FETCH_ASSOC);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $specializationId = (int)$_POST['specialization_id'];
    $doctorId = !empty($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : null;
    $appointmentDate = $_POST['appointment_date'];
    $appointmentTime = $_POST['appointment_time'];
    $symptoms = trim($_POST['symptoms']);
    
    // Валидация
    if (empty($specializationId)) {
        $error = 'Выберите специализацию';
    } elseif (empty($appointmentDate)) {
        $error = 'Выберите дату приема';
    } elseif (empty($appointmentTime)) {
        $error = 'Выберите время приема';
    } elseif (strtotime($appointmentDate) < strtotime('today')) {
        $error = 'Нельзя записаться на прошедшую дату';
    } else {
        // Проверка доступности времени для врача
        if ($doctorId) {
            $stmt = $pdo->prepare("SELECT id FROM appointments 
                                 WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? 
                                 AND status != 'canceled'");
            $stmt->execute([$doctorId, $appointmentDate, $appointmentTime]);
            if ($stmt->fetch()) {
                $error = 'Выбранное время уже занято';
            }
        }
        
        if (empty($error)) {
            try {
                // Создаем запись
                $stmt = $pdo->prepare("INSERT INTO appointments 
                    (patient_id, doctor_id, specialization_id, appointment_date, appointment_time, symptoms, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending')");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $doctorId,
                    $specializationId,
                    $appointmentDate,
                    $appointmentTime,
                    $symptoms
                ]);
                
                // Добавляем уведомление
                $message = "Вы записаны на прием на $appointmentDate в $appointmentTime";
                $stmt = $pdo->prepare("INSERT INTO notifications 
                                      (user_id, message, type) VALUES (?, ?, 'appointment')");
                $stmt->execute([$_SESSION['user_id'], $message]);
                
                $_SESSION['success_message'] = 'Запись на прием успешно создана';
                header("Location: profile.php");
                exit;
            } catch (PDOException $e) {
                $error = 'Ошибка при создании записи: ' . $e->getMessage();
            }
        }
    }
}

$title = 'Запись на прием';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .appointment-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .form-title {
            color: #2c3e50;
            margin-bottom: 25px;
            font-weight: 600;
            text-align: center;
        }
        .form-control, .form-select {
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #ced4da;
            transition: border-color 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.25rem rgba(0,123,255,0.25);
        }
        .btn-primary {
            background-color: #3498db;
            border: none;
            padding: 10px 20px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #2980b9;
        }
        .btn-secondary {
            background-color: #95a5a6;
            border: none;
        }
        .loading-text {
            color: #7f8c8d;
            font-style: italic;
        }
        .invalid-feedback {
            color: #e74c3c;
        }
    </style>
</head>
<body>
<div class="container appointment-container">
    <h1 class="form-title">Запись на прием</h1>
    
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <form method="post" id="appointmentForm" class="needs-validation" novalidate>
        <div class="mb-4">
            <label for="specialization_id" class="form-label fw-bold">Специализация *</label>
            <select id="specialization_id" name="specialization_id" class="form-select" required>
                <option value="">-- Выберите специализацию --</option>
                <?php foreach ($specializations as $spec): ?>
                <option value="<?= htmlspecialchars($spec['id']) ?>" 
                    <?= isset($_POST['specialization_id']) && $_POST['specialization_id'] == $spec['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($spec['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Пожалуйста, выберите специализацию</div>
        </div>
        
        <div class="mb-4">
            <label for="doctor_id" class="form-label fw-bold">Врач</label>
            <select id="doctor_id" name="doctor_id" class="form-select">
                <option value="">-- Сначала выберите специализацию --</option>
            </select>
            <small class="text-muted">Если не выбрать врача, система назначит любого свободного специалиста</small>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6 mb-3 mb-md-0">
                <label for="appointment_date" class="form-label fw-bold">Дата приема *</label>
                <input type="date" id="appointment_date" name="appointment_date" class="form-control" required 
                       min="<?= date('Y-m-d') ?>" 
                       max="<?= date('Y-m-d', strtotime('+3 months')) ?>"
                       value="<?= htmlspecialchars($_POST['appointment_date'] ?? '') ?>">
                <div class="invalid-feedback">Пожалуйста, выберите дату</div>
            </div>
            
            <div class="col-md-6">
                <label for="appointment_time" class="form-label fw-bold">Время приема *</label>
                <select id="appointment_time" name="appointment_time" class="form-select" required>
                    <option value="">-- Выберите время --</option>
                </select>
                <div class="invalid-feedback">Пожалуйста, выберите время</div>
            </div>
        </div>
        
        <div class="mb-4">
            <label for="symptoms" class="form-label fw-bold">Симптомы/жалобы</label>
            <textarea id="symptoms" name="symptoms" class="form-control" rows="4" 
                      placeholder="Опишите ваши симптомы..."><?= htmlspecialchars($_POST['symptoms'] ?? '') ?></textarea>
            <small class="text-muted">Это поможет врачу лучше подготовиться к приему</small>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
            <a href="profile.php" class="btn btn-secondary btn-lg">Отмена</a>
            <button type="submit" class="btn btn-primary btn-lg">Записаться на прием</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const specializationSelect = document.getElementById('specialization_id');
    const doctorSelect = document.getElementById('doctor_id');
    const dateInput = document.getElementById('appointment_date');
    const timeSelect = document.getElementById('appointment_time');
    const form = document.getElementById('appointmentForm');

    // Функция для показа ошибки
    function showError(element, message) {
        const errorElement = document.createElement('div');
        errorElement.className = 'text-danger mt-1 small';
        errorElement.textContent = message;
        element.parentNode.appendChild(errorElement);
        return errorElement;
    }

    // Функция для очистки ошибок
    function clearErrors(element) {
        const errors = element.parentNode.querySelectorAll('.text-danger');
        errors.forEach(error => error.remove());
    }

    // Валидация формы
    form.addEventListener('submit', function(event) {
        let isValid = true;
        
        // Проверяем обязательные поля
        if (!specializationSelect.value) {
            showError(specializationSelect, 'Выберите специализацию');
            isValid = false;
        }
        
        if (!dateInput.value) {
            showError(dateInput, 'Выберите дату приема');
            isValid = false;
        }
        
        if (!timeSelect.value) {
            showError(timeSelect, 'Выберите время приема');
            isValid = false;
        }
        
        if (!isValid) {
            event.preventDefault();
            // Прокрутка к первой ошибке
            const firstError = form.querySelector('.text-danger');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    // Загрузка врачей при выборе специализации
    specializationSelect.addEventListener('change', async function() {
        const specId = this.value;
        clearErrors(specializationSelect);
        doctorSelect.innerHTML = '<option value="">-- Загрузка врачей... --</option>';
        doctorSelect.disabled = true;
        
        if (!specId) {
            doctorSelect.innerHTML = '<option value="">-- Выберите специализацию --</option>';
            doctorSelect.disabled = false;
            return;
        }

        try {
            // Добавляем индикатор загрузки
            const loadingIndicator = showError(doctorSelect, 'Идет загрузка данных...');
            
            const response = await fetch(`get_doctors.php?specialization_id=${specId}`);
            
            // Удаляем индикатор загрузки
            loadingIndicator.remove();
            
            // Проверяем статус ответа
            if (!response.ok) {
                throw new Error(`Ошибка сервера: ${response.status}`);
            }
            
            // Проверяем формат ответа
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                throw new Error(`Ожидался JSON, получен: ${text.substring(0, 50)}...`);
            }
            
            const result = await response.json();
            
            // Проверяем структуру ответа
            if (!result || typeof result !== 'object') {
                throw new Error('Некорректный формат ответа от сервера');
            }
            
            if (!result.success) {
                throw new Error(result.error || 'Неизвестная ошибка сервера');
            }
            
            // Гарантируем, что data - массив
            const doctors = Array.isArray(result.data) ? result.data : [];
            
            doctorSelect.innerHTML = '<option value="">-- Любой врач --</option>';
            
            if (doctors.length === 0) {
                doctorSelect.innerHTML += '<option value="">-- Нет доступных врачей --</option>';
                showError(doctorSelect, 'Для выбранной специализации нет доступных врачей');
            } else {
                doctors.forEach(doctor => {
                    const option = new Option(
                        `${doctor.name} (${doctor.specialization}, каб. ${doctor.room_number})`,
                        doctor.id
                    );
                    doctorSelect.add(option);
                });
            }
        } catch (error) {
            console.error('Ошибка загрузки врачей:', error);
            doctorSelect.innerHTML = '<option value="">-- Ошибка загрузки --</option>';
            showError(doctorSelect, error.message);
        } finally {
            doctorSelect.disabled = false;
        }
    });

    // Загрузка доступного времени
    async function loadAvailableTimes() {
        const doctorId = doctorSelect.value;
        const date = dateInput.value;
        
        clearErrors(timeSelect);
        timeSelect.innerHTML = '<option value="">-- Загрузка времени... --</option>';
        timeSelect.disabled = true;
        
        if (!doctorId || !date) {
            timeSelect.innerHTML = '<option value="">-- Сначала выберите врача и дату --</option>';
            timeSelect.disabled = false;
            return;
        }

        try {
            // Индикатор загрузки
            const loadingIndicator = showError(timeSelect, 'Проверяем доступное время...');
            
            const response = await fetch(`get_available_times.php?doctor_id=${doctorId}&date=${date}`);
            
            // Удаляем индикатор
            loadingIndicator.remove();
            
            if (!response.ok) {
                throw new Error(`Ошибка сервера: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                throw new Error(`Ожидался JSON, получен: ${text.substring(0, 50)}...`);
            }
            
            const result = await response.json();
            
            // Обрабатываем разные форматы ответа
            const times = Array.isArray(result) ? result : 
                         (result && Array.isArray(result.data)) ? result.data : [];
            
            timeSelect.innerHTML = '<option value="">-- Выберите время --</option>';
            
            if (times.length === 0) {
                timeSelect.innerHTML += '<option value="">-- Нет свободного времени --</option>';
                showError(timeSelect, 'Нет доступных временных слотов на выбранную дату');
            } else {
                times.forEach(time => {
                    const option = new Option(time, time);
                    timeSelect.add(option);
                });
            }
        } catch (error) {
            console.error('Ошибка загрузки времени:', error);
            timeSelect.innerHTML = '<option value="">-- Ошибка загрузки --</option>';
            showError(timeSelect, error.message);
        } finally {
            timeSelect.disabled = false;
        }
    }
    
    // Обработчики событий
    doctorSelect.addEventListener('change', loadAvailableTimes);
    dateInput.addEventListener('change', loadAvailableTimes);

    // Очистка ошибок при изменении
    specializationSelect.addEventListener('input', () => clearErrors(specializationSelect));
    doctorSelect.addEventListener('input', () => clearErrors(doctorSelect));
    dateInput.addEventListener('input', () => clearErrors(dateInput));
    timeSelect.addEventListener('input', () => clearErrors(timeSelect));
});

</script>

<?php include 'includes/footer.php'; ?>