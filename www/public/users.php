<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$users = getAllUsers();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelPi - Управление пользователями</title>
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
                <a href="users.php" class="active">Пользователи</a>
                <a href="pihole.php">Pi-hole</a>
                <a href="logout.php">Выход</a>
            </nav>
        </header>

        <main>
            <section>
                <div class="section-header">
                    <h2>Управление пользователями</h2>
                    <button class="btn-primary" onclick="showAddUserModal()">➕ Добавить пользователя</button>
                </div>
                <p>Контроль доступа пользователей к веб-интерфейсу TravelPi</p>
                
                <table>
                    <thead>
                        <tr>
                            <th>Имя пользователя</th>
                            <th>Дата создания</th>
                            <th>Последний вход</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <span class="user-icon">👤</span>
                                <?= htmlspecialchars($user['username']) ?>
                                <?php if ($user['username'] === 'admin'): ?>
                                <span class="badge-admin">Администратор</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                            <td>
                                <?= $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : 'Никогда' ?>
                            </td>
                            <td>
                                <span class="status-badge <?= $user['is_allowed'] ? 'allowed' : 'blocked' ?>">
                                    <?= $user['is_allowed'] ? 'Разрешен' : 'Заблокирован' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['username'] !== 'admin'): ?>
                                <div class="action-buttons">
                                    <?php if ($user['is_allowed']): ?>
                                    <button class="btn-small btn-danger" onclick="blockUser(<?= $user['id'] ?>)">
                                        Заблокировать
                                    </button>
                                    <?php else: ?>
                                    <button class="btn-small btn-success" onclick="allowUser(<?= $user['id'] ?>)">
                                        Разрешить
                                    </button>
                                    <?php endif; ?>
                                    <button class="btn-small" onclick="resetPassword(<?= $user['id'] ?>)">
                                        Сбросить пароль
                                    </button>
                                    <button class="btn-small btn-danger" onclick="deleteUser(<?= $user['id'] ?>)">
                                        Удалить
                                    </button>
                                </div>
                                <?php else: ?>
                                <span class="text-muted">Системный пользователь</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section>
                <h2>Информация</h2>
                <div class="info-box">
                    <p>ℹ️ Пользователи с доступом могут входить в веб-интерфейс TravelPi и управлять настройками роутера.</p>
                    <p>⚠️ Администратор не может быть удален или заблокирован.</p>
                    <p>🔒 Рекомендуется использовать сложные пароли для всех пользователей.</p>
                </div>
            </section>
        </main>
    </div>

    <!-- Модальное окно добавления пользователя -->
    <div id="add-user-modal" class="modal">
        <div class="modal-content">
            <h3>Добавить пользователя</h3>
            <form id="add-user-form">
                <div class="form-group">
                    <label for="new-username">Имя пользователя</label>
                    <input type="text" id="new-username" name="username" required 
                           pattern="[a-zA-Z0-9_]+" 
                           title="Только латинские буквы, цифры и подчеркивание">
                </div>
                <div class="form-group">
                    <label for="new-password">Пароль</label>
                    <input type="password" id="new-password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm-password">Подтвердите пароль</label>
                    <input type="password" id="confirm-password" name="confirm_password" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Создать</button>
                    <button type="button" class="btn" onclick="closeModal()">Отмена</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script src="js/users.js"></script>
</body>
</html>