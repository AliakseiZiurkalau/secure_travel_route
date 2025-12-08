<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$availableNetworks = scanWifiNetworks();
$savedNetworks = getSavedWifiNetworks();
$currentConnection = getCurrentConnection();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelPi - Настройка подключения</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🌍 TravelPi</h1>
            <nav>
                <a href="connect.php" class="active">Подключение</a>
                <a href="monitoring.php">Мониторинг</a>
                <a href="history.php">История</a>
                <a href="devices.php">Устройства</a>
                <a href="users.php">Пользователи</a>
                <a href="pihole.php">Pi-hole</a>
                <a href="logout.php">Выход</a>
            </nav>
        </header>

        <main>
            <?php if ($currentConnection): ?>
            <section class="current-connection">
                <h2>Текущее подключение</h2>
                <div class="connection-card active">
                    <div class="connection-info">
                        <h3>📡 <?= htmlspecialchars($currentConnection['ssid']) ?></h3>
                        <p>Интерфейс: <?= htmlspecialchars($currentConnection['interface']) ?></p>
                        <p>Подключено: <?= formatDuration($currentConnection['duration']) ?></p>
                        <p>Трафик: ↓ <?= formatBytes($currentConnection['bytes_received']) ?> 
                           ↑ <?= formatBytes($currentConnection['bytes_sent']) ?></p>
                    </div>
                    <button class="btn btn-danger" onclick="disconnect()">Отключить</button>
                </div>
            </section>
            <?php endif; ?>

            <section>
                <h2>Режим подключения</h2>
                <div class="connection-mode">
                    <button class="btn mode-btn" data-mode="wifi" onclick="setMode('wifi')">
                        📡 USB Wi-Fi
                    </button>
                    <button class="btn mode-btn" data-mode="lan" onclick="setMode('lan')">
                        🔌 USB LAN
                    </button>
                    <button class="btn mode-btn active" data-mode="auto" onclick="setMode('auto')">
                        🔄 Авто
                    </button>
                </div>
            </section>

            <section>
                <div class="section-header">
                    <h2>Доступные Wi-Fi сети</h2>
                    <button class="btn-primary" onclick="scanNetworks()">🔍 Сканировать</button>
                </div>
                
                <div class="networks-list">
                    <?php if (empty($availableNetworks)): ?>
                    <p class="empty-state">Нажмите "Сканировать" для поиска сетей</p>
                    <?php else: ?>
                    <?php foreach ($availableNetworks as $network): ?>
                    <div class="network-card">
                        <div class="network-info">
                            <h3><?= htmlspecialchars($network['ssid']) ?></h3>
                            <div class="network-details">
                                <span class="signal">
                                    <?= getSignalIcon($network['signal']) ?> <?= $network['signal'] ?>%
                                </span>
                                <span class="security">
                                    <?= $network['security'] === 'Open' ? '🔓' : '🔒' ?> 
                                    <?= htmlspecialchars($network['security']) ?>
                                </span>
                            </div>
                        </div>
                        <button class="btn-primary" onclick="connectWifi('<?= htmlspecialchars($network['ssid'], ENT_QUOTES) ?>')">
                            Подключить
                        </button>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <?php if (!empty($savedNetworks)): ?>
            <section>
                <h2>Сохраненные сети</h2>
                <div class="saved-networks">
                    <?php foreach ($savedNetworks as $network): ?>
                    <div class="saved-network-item">
                        <span>📶 <?= htmlspecialchars($network['ssid']) ?></span>
                        <div>
                            <button class="btn-small" onclick="connectSaved(<?= $network['id'] ?>)">
                                Подключить
                            </button>
                            <button class="btn-small btn-danger" onclick="deleteSaved(<?= $network['id'] ?>)">
                                Удалить
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        </main>
    </div>

    <div id="wifi-modal" class="modal">
        <div class="modal-content">
            <h3>Подключение к Wi-Fi</h3>
            <form id="wifi-form">
                <input type="hidden" id="wifi-ssid" name="ssid">
                <div class="form-group">
                    <label>Сеть: <strong id="wifi-ssid-display"></strong></label>
                </div>
                <div class="form-group">
                    <label for="wifi-password">Пароль</label>
                    <input type="password" id="wifi-password" name="password" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Подключить</button>
                    <button type="button" class="btn" onclick="closeModal()">Отмена</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script src="js/connect.js"></script>
</body>
</html>

<?php
function getSignalIcon($signal) {
    if ($signal >= 75) return '📶';
    if ($signal >= 50) return '📶';
    if ($signal >= 25) return '📶';
    return '📶';
}

function formatDuration($seconds) {
    if ($seconds < 60) return $seconds . ' сек';
    if ($seconds < 3600) return floor($seconds / 60) . ' мин';
    return floor($seconds / 3600) . ' ч ' . floor(($seconds % 3600) / 60) . ' мин';
}

function formatBytes($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
    return round($bytes / 1073741824, 2) . ' GB';
}
?>