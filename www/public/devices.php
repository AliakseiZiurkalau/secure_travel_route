<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$devices = getAllDevices();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelPi - Управление устройствами</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🌍 TravelPi</h1>
            <nav>
                <a href="connect.php">Подключение</a>
                <a href="monitoring.php">Мониторинг</a>
                <a href="history.php">История</a>
                <a href="devices.php" class="active">Устройства</a>
                <a href="users.php">Пользователи</a>
                <a href="pihole.php">Pi-hole</a>
                <a href="logout.php">Выход</a>
            </nav>
        </header>

        <main>
            <section>
                <h2>Управление устройствами</h2>
                <p>Контроль доступа устройств к роутеру</p>
                
                <table>
                    <thead>
                        <tr>
                            <th>Название</th>
                            <th>MAC адрес</th>
                            <th>Последнее подключение</th>
                            <th>Статус</th>
                            <th>Действие</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($devices as $device): ?>
                        <tr>
                            <td>
                                <input type="text" 
                                       value="<?= htmlspecialchars($device['device_name'] ?? 'Без имени') ?>"
                                       onchange="updateDeviceName(<?= $device['id'] ?>, this.value)">
                            </td>
                            <td><?= htmlspecialchars($device['mac_address']) ?></td>
                            <td><?= $device['last_seen'] ? date('d.m.Y H:i', strtotime($device['last_seen'])) : 'Никогда' ?></td>
                            <td>
                                <span class="status-<?= $device['is_allowed'] ? 'allowed' : 'blocked' ?>">
                                    <?= $device['is_allowed'] ? 'Разрешено' : 'Заблокировано' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($device['is_allowed']): ?>
                                <button class="btn-small btn-danger" onclick="blockDevice(<?= $device['id'] ?>)">
                                    Заблокировать
                                </button>
                                <?php else: ?>
                                <button class="btn-small btn-success" onclick="allowDevice(<?= $device['id'] ?>)">
                                    Разрешить
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
    <script src="js/app.js"></script>
    <script src="js/devices.js"></script>
</body>
</html>
