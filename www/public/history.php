<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$sessionId = $_GET['session'] ?? null;

if ($sessionId) {
    $session = getSessionById($sessionId);
    $resources = getSessionResources($sessionId);
} else {
    $sessions = getAllSessions();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelPi - История подключений</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🌍 TravelPi</h1>
            <nav>
                <a href="connect.php">Подключение</a>
                <a href="monitoring.php">Мониторинг</a>
                <a href="history.php" class="active">История</a>
                <a href="devices.php">Устройства</a>
                <a href="users.php">Пользователи</a>
                <a href="pihole.php">Pi-hole</a>
                <a href="logout.php">Выход</a>
            </nav>
        </header>

        <main>
            <?php if ($sessionId && $session): ?>
            <!-- Детали сессии -->
            <section>
                <div class="section-header">
                    <h2>Детали сессии</h2>
                    <a href="history.php" class="btn">← Назад к списку</a>
                </div>
                
                <div class="session-details">
                    <div class="detail-card">
                        <h3>📡 <?= htmlspecialchars($session['ssid']) ?></h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="label">Интерфейс:</span>
                                <span class="value"><?= htmlspecialchars($session['interface']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Начало:</span>
                                <span class="value"><?= date('d.m.Y H:i:s', strtotime($session['started_at'])) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Окончание:</span>
                                <span class="value">
                                    <?= $session['ended_at'] ? date('d.m.Y H:i:s', strtotime($session['ended_at'])) : 'Активна' ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Длительность:</span>
                                <span class="value"><?= formatSessionDuration($session) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="traffic-stats">
                        <h3>Статистика трафика</h3>
                        <div class="traffic-grid">
                            <div class="traffic-card">
                                <div class="traffic-icon">⬇️</div>
                                <div class="traffic-info">
                                    <span class="traffic-label">Получено</span>
                                    <span class="traffic-value"><?= formatBytes($session['bytes_received']) ?></span>
                                </div>
                            </div>
                            <div class="traffic-card">
                                <div class="traffic-icon">⬆️</div>
                                <div class="traffic-info">
                                    <span class="traffic-label">Отправлено</span>
                                    <span class="traffic-value"><?= formatBytes($session['bytes_sent']) ?></span>
                                </div>
                            </div>
                            <div class="traffic-card">
                                <div class="traffic-icon">📊</div>
                                <div class="traffic-info">
                                    <span class="traffic-label">Всего</span>
                                    <span class="traffic-value"><?= formatBytes($session['total_bytes']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section>
                <h2>Посещенные ресурсы</h2>
                <?php if (empty($resources)): ?>
                <p class="empty-state">Нет записей о посещенных ресурсах</p>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Домен</th>
                            <th>Количество запросов</th>
                            <th>Первый доступ</th>
                            <th>Последний доступ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resources as $resource): ?>
                        <tr>
                            <td>
                                <span class="domain-icon">🌐</span>
                                <?= htmlspecialchars($resource['domain']) ?>
                            </td>
                            <td><?= $resource['request_count'] ?></td>
                            <td><?= date('d.m.Y H:i:s', strtotime($resource['first_access'])) ?></td>
                            <td><?= date('d.m.Y H:i:s', strtotime($resource['last_access'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </section>

            <?php else: ?>
            <!-- Список всех сессий -->
            <section>
                <h2>История подключений</h2>
                <?php if (empty($sessions)): ?>
                <div class="empty-state-large">
                    <h3>История пуста</h3>
                    <p>Здесь будут отображаться все ваши подключения</p>
                </div>
                <?php else: ?>
                <div class="sessions-list">
                    <?php foreach ($sessions as $sess): ?>
                    <div class="session-card" onclick="location.href='history.php?session=<?= $sess['id'] ?>'">
                        <div class="session-header">
                            <h3>📡 <?= htmlspecialchars($sess['ssid']) ?></h3>
                            <?php if ($sess['is_active']): ?>
                            <span class="status-badge active">Активна</span>
                            <?php else: ?>
                            <span class="status-badge">Завершена</span>
                            <?php endif; ?>
                        </div>
                        <div class="session-info-grid">
                            <div class="info-item">
                                <span class="icon">📅</span>
                                <span><?= date('d.m.Y H:i', strtotime($sess['started_at'])) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="icon">⏱️</span>
                                <span><?= formatSessionDuration($sess) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="icon">📊</span>
                                <span><?= formatBytes($sess['total_bytes']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="icon">🌐</span>
                                <span><?= getResourceCount($sess['id']) ?> ресурсов</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>
        </main>
    </div>
    <script src="js/app.js"></script>
</body>
</html>

<?php
function formatSessionDuration($session) {
    $start = strtotime($session['started_at']);
    $end = $session['ended_at'] ? strtotime($session['ended_at']) : time();
    $duration = $end - $start;
    
    $hours = floor($duration / 3600);
    $minutes = floor(($duration % 3600) / 60);
    
    if ($hours > 0) {
        return sprintf('%d ч %d мин', $hours, $minutes);
    } else {
        return sprintf('%d мин', $minutes);
    }
}

function formatBytes($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
    return round($bytes / 1073741824, 2) . ' GB';
}

function getResourceCount($sessionId) {
    $db = getDatabase();
    $stmt = $db->prepare('SELECT COUNT(*) as count FROM session_resources WHERE session_id = ?');
    $stmt->execute([$sessionId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'];
}
?>