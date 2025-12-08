<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentSession = getCurrentSession();
$systemStats = getSystemStats();
$sessionResources = $currentSession ? getSessionResources($currentSession['id']) : [];
$connectedDevices = getConnectedDevices();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelPi - Мониторинг</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🌍 TravelPi</h1>
            <nav>
                <a href="connect.php">Подключение</a>
                <a href="monitoring.php" class="active">Мониторинг</a>
                <a href="history.php">История</a>
                <a href="devices.php">Устройства</a>
                <a href="users.php">Пользователи</a>
                <a href="pihole.php">Pi-hole</a>
                <a href="logout.php">Выход</a>
            </nav>
        </header>

        <main>
            <section class="stats">
                <h2>Ресурсы системы</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">💻</div>
                        <h3>CPU</h3>
                        <p class="stat-value" id="cpu-value"><?= $systemStats['cpu'] ?>%</p>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $systemStats['cpu'] ?>%"></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🧠</div>
                        <h3>Память</h3>
                        <p class="stat-value" id="memory-value"><?= $systemStats['memory'] ?>%</p>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $systemStats['memory'] ?>%"></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🌡️</div>
                        <h3>Температура</h3>
                        <p class="stat-value" id="temp-value"><?= $systemStats['temp'] ?>°C</p>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📱</div>
                        <h3>Устройства</h3>
                        <p class="stat-value" id="devices-value"><?= count($connectedDevices) ?></p>
                    </div>
                </div>
            </section>

            <?php if ($currentSession): ?>
            <section>
                <h2>Текущая сессия</h2>
                <div class="session-info">
                    <div class="session-card">
                        <h3>📡 <?= htmlspecialchars($currentSession['ssid']) ?></h3>
                        <div class="session-stats">
                            <div class="session-stat">
                                <span class="label">Длительность:</span>
                                <span class="value" id="session-duration">
                                    <?= formatDuration(time() - strtotime($currentSession['started_at'])) ?>
                                </span>
                            </div>
                            <div class="session-stat">
                                <span class="label">Получено:</span>
                                <span class="value" id="bytes-received">
                                    <?= formatBytes($currentSession['bytes_received']) ?>
                                </span>
                            </div>
                            <div class="session-stat">
                                <span class="label">Отправлено:</span>
                                <span class="value" id="bytes-sent">
                                    <?= formatBytes($currentSession['bytes_sent']) ?>
                                </span>
                            </div>
                            <div class="session-stat">
                                <span class="label">Всего:</span>
                                <span class="value" id="total-bytes">
                                    <?= formatBytes($currentSession['total_bytes']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section>
                <h2>Посещенные ресурсы (текущая сессия)</h2>
                <?php if (empty($sessionResources)): ?>
                <p class="empty-state">Пока нет записей о посещенных ресурсах</p>
                <?php else: ?>
                <div class="resources-list">
                    <table>
                        <thead>
                            <tr>
                                <th>Домен</th>
                                <th>Запросов</th>
                                <th>Первый доступ</th>
                                <th>Последний доступ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sessionResources as $resource): ?>
                            <tr>
                                <td>🌐 <?= htmlspecialchars($resource['domain']) ?></td>
                                <td><?= $resource['request_count'] ?></td>
                                <td><?= date('H:i:s', strtotime($resource['first_access'])) ?></td>
                                <td><?= date('H:i:s', strtotime($resource['last_access'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </section>
            <?php else: ?>
            <section>
                <div class="empty-state-large">
                    <h2>Нет активной сессии</h2>
                    <p>Подключитесь к сети для начала мониторинга</p>
                    <a href="connect.php" class="btn-primary">Перейти к подключению</a>
                </div>
            </section>
            <?php endif; ?>

            <section>
                <h2>Подключенные устройства</h2>
                <div class="devices-grid">
                    <?php foreach ($connectedDevices as $device): ?>
                    <div class="device-card">
                        <div class="device-icon">📱</div>
                        <h4><?= htmlspecialchars($device['name']) ?></h4>
                        <p class="device-mac"><?= htmlspecialchars($device['mac']) ?></p>
                        <p class="device-ip"><?= htmlspecialchars($device['ip']) ?></p>
                        <span class="status-badge online">Активно</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>
    <script src="js/app.js"></script>
    <script src="js/monitoring.js"></script>
</body>
</html>

<?php
function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    
    if ($hours > 0) {
        return sprintf('%d ч %d мин', $hours, $minutes);
    } elseif ($minutes > 0) {
        return sprintf('%d мин %d сек', $minutes, $secs);
    } else {
        return sprintf('%d сек', $secs);
    }
}

function formatBytes($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
    return round($bytes / 1073741824, 2) . ' GB';
}
?>