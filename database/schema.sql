-- TravelPi Database Schema

-- Пользователи веб-интерфейса
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    is_allowed INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME
);

-- Устройства с доступом к роутеру
CREATE TABLE IF NOT EXISTS devices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    mac_address TEXT UNIQUE NOT NULL,
    device_name TEXT,
    is_allowed INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_seen DATETIME
);

-- Сохраненные Wi-Fi сети
CREATE TABLE IF NOT EXISTS wifi_networks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ssid TEXT NOT NULL,
    password TEXT,
    priority INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Настройки системы
CREATE TABLE IF NOT EXISTS settings (
    key TEXT PRIMARY KEY,
    value TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Сессии подключений к внешним сетям
CREATE TABLE IF NOT EXISTS connection_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ssid TEXT NOT NULL,
    interface TEXT,
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ended_at DATETIME,
    bytes_sent INTEGER DEFAULT 0,
    bytes_received INTEGER DEFAULT 0,
    total_bytes INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1
);

-- Посещенные ресурсы во время сессии
CREATE TABLE IF NOT EXISTS session_resources (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id INTEGER NOT NULL,
    domain TEXT NOT NULL,
    request_count INTEGER DEFAULT 1,
    first_access DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_access DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES connection_sessions(id)
);

-- Логи подключений устройств
CREATE TABLE IF NOT EXISTS connection_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    device_mac TEXT,
    device_name TEXT,
    event_type TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Статистика трафика по устройствам
CREATE TABLE IF NOT EXISTS device_traffic (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    device_mac TEXT NOT NULL,
    session_id INTEGER,
    bytes_sent INTEGER DEFAULT 0,
    bytes_received INTEGER DEFAULT 0,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES connection_sessions(id)
);

-- Создание администратора по умолчанию (пароль: admin123)
INSERT INTO users (username, password_hash) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Настройки по умолчанию
INSERT INTO settings (key, value) VALUES ('ap_ssid', 'TravelPi');
INSERT INTO settings (key, value) VALUES ('ap_password', 'TravelPi2024');
INSERT INTO settings (key, value) VALUES ('connection_mode', 'auto');
INSERT INTO settings (key, value) VALUES ('ad_blocking', '1');
