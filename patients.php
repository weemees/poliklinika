<?php
require_once __DIR__ . '/includes/header.php';
requireDoctor();

// Подключение к БД

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои пациенты</title>
    <link rel="stylesheet" href="/assets/css/doctor.css">
</head>
<body>
    <main>
        <h2>Мои пациенты</h2>
        <table>
            <thead>
                <tr>
                    <th>ФИО</th>
                    <th>Последний визит</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->prepare("SELECT DISTINCT u.id, u.name, MAX(a.appointment_date) as last_visit
                                      FROM appointments a
                                      JOIN users u ON a.patient_id = u.id
                                      WHERE a.doctor_id = ?
                                      GROUP BY u.id
                                      ORDER BY last_visit DESC");
                $stmt->execute([$_SESSION['user_id']]);
                
                while ($patient = $stmt->fetch()) {
                    echo "<tr>
                        <td>" . htmlspecialchars($patient['name']) . "</td>
                        <td>" . ($patient['last_visit'] ? date('d.m.Y', strtotime($patient['last_visit'])) : 'Еще не было') . "</td>
                        <td><a href='/doctor/patient.php?id=" . $patient['id'] . "'>История</a></td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </main>
</body>
</html>