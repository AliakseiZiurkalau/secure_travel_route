<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = getDatabase();
$wifiNetworks = getSavedWifiNetworks();
$availableNetworks = scanWifiNetworks();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelPi - Управление сетью</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🌍 TravelPi</h1>
            <nav>
                <a href="index.php">Главная</a>
                <a href="network.php" class="active">Сеть</a>
                <a href="devices.php">Устройства</a>
                <a href="settings.php">Настройки</a>
                <a href="logout.php">Выход</a>
            </nav>
        </header>

        <main>
            <section>
                <h2>Режим подключения</h2>
                <div class="connection-mode">
                    <button class="btn" onclick="setMode('wifi')">📡 USB Wi-Fi</button>
                    <button class="btn" onclick="setMode('lan')">🔌 USB LAN</button>
                    <button class="btn" onclick="setMode('auto')">🔄 Авто</button>
                </div>
            </section>

            <section>
                <h2>Доступные Wi-Fi сети</h2>
                <button class="btn-primary" onclick="scanNetworks()">🔍 Сканировать</button>
                <table>
                    <thead>
                        <tr>
                            <th>SSID</th>
                            <th>Сигнал</th>
                            <th>Защита</th>
                            <th>Действие</th>
                        </tr>
                    </thead>
                    <tbody id="available-networks">
                        <?php foreach ($availableNetworks as $network): ?>
                        <tr>
                            <td><?= htmlspecialchars($network['ssid']) ?></td>
                            <td><?= $network['signal'] ?>%</td>
                            <td><?= $network['security'] ?></td>
                            <td>
                                <button class="btn-small" onclick="connectWifi('<?= htmlspecialchars($network['ssid']) ?>')">
                                    Подключить
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section>
                <h2>Сохраненные сети</h2>
                <table>
                    <thead>
                        <tr>
                            <th>SSID</th>
                            <th>Приоритет</th>
                            <th>Действие</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($wifiNetworks as $network): ?>
                        <tr>
                            <td><?= htmlspecialchars($network['ssid']) ?></td>
                            <td><?= $network['priority'] ?></td>
                            <td>
                                <button class="btn-small btn-danger" onclick="deleteNetwork(<?= $network['id'] ?>)">
                                    Удалить
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <div id="wifi-modal" class="modal">
        <div class="modal-content">
            <h3>Подключение к Wi-Fi</h3>
            <form id="wifi-form">
                <input type="hidden" id="wifi-ssid" name="ssid">
                <div class="form-group">
                    <label>SSID: <span id="wifi-ssid-display"></span></label>
                </div>
                <div class="form-group">
                    <label for="wifi-password">Пароль</label>
                    <input type="password" id="wifi-password" name="password" required>
                </div>
                <button type="submit" class="btn-primary">Подключить</button>
                <button type="button" class="btn" onclick="closeModal()">Отмена</button>
            </form>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script src="js/network.js"></script>
</body>
</html>
