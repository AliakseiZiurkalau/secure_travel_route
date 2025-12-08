<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = getDatabase();
$settings = $db->query('SELECT * FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelPi - Настройки</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🌍 TravelPi</h1>
            <nav>
                <a href="index.php">Главная</a>
                <a href="network.php">Сеть</a>
                <a href="devices.php">Устройства</a>
                <a href="settings.php" class="active">Настройки</a>
                <a href="logout.php">Выход</a>
            </nav>
        </header>

        <main>
            <section>
                <h2>Настройки точки доступа</h2>
                <form id="ap-settings">
                    <div class="form-group">
                        <label for="ap_ssid">Имя сети (SSID)</label>
                        <input type="text" id="ap_ssid" name="ap_ssid" 
                               value="<?= htmlspecialchars($settings['ap_ssid'] ?? 'TravelPi') ?>">
                    </div>
                    <div class="form-group">
                        <label for="ap_password">Пароль</label>
                        <input type="password" id="ap_password" name="ap_password" 
                               value="<?= htmlspecialchars($settings['ap_password'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn-primary">Сохранить</button>
                    <p class="note">⚠️ После изменения потребуется переподключение</p>
                </form>
            </section>

            <section>
                <h2>Блокировка рекламы</h2>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="ad_blocking" 
                               <?= ($settings['ad_blocking'] ?? '1') == '1' ? 'checked' : '' ?>>
                        Включить блокировку рекламы (Pi-hole)
                    </label>
                </div>
                <button class="btn" onclick="openPihole()">Открыть панель Pi-hole</button>
            </section>

            <section>
                <h2>Смена пароля администратора</h2>
                <form id="password-form">
                    <div class="form-group">
                        <label for="current_password">Текущий пароль</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">Новый пароль</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Подтвердите пароль</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn-primary">Изменить пароль</button>
                </form>
            </section>

            <section>
                <h2>Системная информация</h2>
                <table>
                    <tr>
                        <td>Версия ОС</td>
                        <td><?= shell_exec('cat /etc/os-release | grep PRETTY_NAME | cut -d"=" -f2 | tr -d \'"\'') ?></td>
                    </tr>
                    <tr>
                        <td>Uptime</td>
                        <td><?= shell_exec('uptime -p') ?></td>
                    </tr>
                    <tr>
                        <td>Свободное место</td>
                        <td><?= shell_exec('df -h / | tail -1 | awk \'{print $4}\'') ?></td>
                    </tr>
                </table>
            </section>

            <section>
                <h2>Управление системой</h2>
                <div class="system-actions">
                    <button class="btn btn-reboot" onclick="rebootSystem()">
                        <span class="btn-icon">🔄</span>
                        <span>Перезагрузить</span>
                    </button>
                    <button class="btn btn-shutdown" onclick="shutdownSystem()">
                        <span class="btn-icon">⏻</span>
                        <span>Выключить</span>
                    </button>
                </div>
                <p class="warning-text">⚠️ При выключении устройство полностью отключится. Для включения потребуется физический доступ к Raspberry Pi.</p>
            </section>
        </main>
    </div>
    <script src="js/app.js"></script>
    <script src="js/settings.js"></script>
</body>
</html>
