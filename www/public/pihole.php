<?php
session_start();
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelPi - Pi-hole</title>
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
                <a href="devices.php">Устройства</a>
                <a href="users.php">Пользователи</a>
                <a href="pihole.php" class="active">Pi-hole</a>
                <a href="logout.php">Выход</a>
            </nav>
        </header>

        <main>
            <section>
                <h2>Pi-hole - Блокировщик рекламы</h2>
                <p>Pi-hole блокирует рекламу и трекеры на уровне DNS для всех подключенных устройств.</p>
                
                <div class="pihole-actions">
                    <a href="http://travelpi.local/admin" target="_blank" class="btn-primary btn-large">
                        🛡️ Открыть панель Pi-hole
                    </a>
                </div>
            </section>

            <section>
                <h2>Возможности Pi-hole</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">🚫</div>
                        <h3>Блокировка рекламы</h3>
                        <p>Автоматическая блокировка рекламных доменов на всех устройствах</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📊</div>
                        <h3>Статистика</h3>
                        <p>Детальная статистика запросов и заблокированных доменов</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🔒</div>
                        <h3>Приватность</h3>
                        <p>Блокировка трекеров и защита конфиденциальности</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">⚡</div>
                        <h3>Производительность</h3>
                        <p>Ускорение загрузки страниц за счет блокировки рекламы</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📝</div>
                        <h3>Белые списки</h3>
                        <p>Настройка исключений для нужных доменов</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🎯</div>
                        <h3>Черные списки</h3>
                        <p>Добавление дополнительных доменов для блокировки</p>
                    </div>
                </div>
            </section>

            <section>
                <h2>Быстрый доступ</h2>
                <div class="quick-links">
                    <a href="http://travelpi.local/admin" target="_blank" class="quick-link">
                        <span class="link-icon">🏠</span>
                        <span class="link-text">Главная панель</span>
                    </a>
                    <a href="http://travelpi.local/admin/queries.php" target="_blank" class="quick-link">
                        <span class="link-icon">🔍</span>
                        <span class="link-text">Журнал запросов</span>
                    </a>
                    <a href="http://travelpi.local/admin/groups-domains.php" target="_blank" class="quick-link">
                        <span class="link-icon">📋</span>
                        <span class="link-text">Списки доменов</span>
                    </a>
                    <a href="http://travelpi.local/admin/settings.php" target="_blank" class="quick-link">
                        <span class="link-icon">⚙️</span>
                        <span class="link-text">Настройки</span>
                    </a>
                </div>
            </section>

            <section>
                <h2>Информация</h2>
                <div class="info-box">
                    <p>ℹ️ Pi-hole работает автоматически для всех подключенных устройств.</p>
                    <p>🔑 Для доступа к панели Pi-hole используйте пароль, установленный при настройке.</p>
                    <p>📚 Документация: <a href="https://docs.pi-hole.net/" target="_blank">docs.pi-hole.net</a></p>
                </div>
            </section>
        </main>
    </div>
    <script src="js/app.js"></script>
</body>
</html>