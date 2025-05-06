<?php
// Убедитесь, что нет пробелов/переносов до этого тега
require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/db.php';

// Очистка буфера вывода
if (ob_get_length()) ob_clean();

header('Content-Type: application/json; charset=utf-8');

try {
    // Проверка существования параметра
    if (!isset($_GET['specialization_id'])) {
        throw new Exception('Не указан ID специализации');
    }

    $specId = (int)$_GET['specialization_id'];
    
    // Запрос к базе данных
    $stmt = $pdo->prepare("
        SELECT 
            u.id, 
            u.name, 
            d.room_number, 
            s.name as specialization 
        FROM users u
        JOIN doctors d ON u.id = d.user_id
        JOIN specializations s ON d.specialization_id = s.id
        WHERE d.specialization_id = ? 
        AND (u.role = 'doctor' OR u.is_doctor = 1)
    ");
    $stmt->execute([$specId]);
    
    // Формируем ответ
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Всегда возвращаем массив, даже пустой
    $response = [
        'success' => true,
        'data' => $doctors ?: [] // Гарантируем, что data будет массивом
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка базы данных: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// Не закрываем тегом ?>