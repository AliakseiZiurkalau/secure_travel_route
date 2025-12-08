# Инструкция по установке TravelPi

## Подготовка Raspberry Pi

### 1. Установка Raspberry OS
1. Скачайте Raspberry Pi Imager: https://www.raspberrypi.com/software/
2. Выберите "Raspberry Pi OS Lite (64-bit)"
3. Настройте:
   - Hostname: `travelpi`
   - Username: `admin`
   - Password: (ваш пароль)
   - Включите SSH
4. Запишите на microSD карту

### 2. Первый запуск
```bash
# Подключитесь по SSH
ssh admin@travelpi.local

# Обновите систему
sudo apt update && sudo apt upgrade -y
```

## Установка TravelPi

### 1. Клонирование проекта
```bash
cd ~
git clone <repository-url> travelpi
cd travelpi
```

### 2. Запуск установки
```bash
chmod +x setup.sh
sudo ./setup.sh
```

### 3. Установка скриптов
```bash
sudo cp scripts/connection-manager.sh /usr/local/bin/travelpi-connection-manager.sh
sudo cp scripts/device-monitor.sh /usr/local/bin/travelpi-device-monitor.sh
sudo cp scripts/dns-monitor.sh /usr/local/bin/travelpi-dns-monitor.sh
sudo cp scripts/autostart.sh /usr/local/bin/travelpi-autostart.sh
sudo chmod +x /usr/local/bin/travelpi-*.sh

sudo cp systemd/*.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable travelpi-connection travelpi-monitor travelpi-dns-monitor travelpi-autostart
```

**Автозапуск**: Служба `travelpi-autostart` автоматически запустит все компоненты при загрузке системы.

### 4. Создание директорий веб-сервера
```bash
sudo mkdir -p /var/www/travelpi/{public,includes,data}
sudo cp -r www/* /var/www/travelpi/
```

### 5. Настройка прав доступа
```bash
sudo chown -R www-data:www-data /var/www/travelpi
sudo chmod 755 /var/www/travelpi
sudo chmod 666 /var/www/travelpi/data/travelpi.db
```

### 6. Перезагрузка
```bash
sudo reboot
```

## Подключение оборудования

1. Подключите USB Wi-Fi адаптер (TP-Link Archer T2U Nano)
2. Подключите USB LAN адаптер (TP-Link UE300)
3. Используйте OTG кабель для подключения к Raspberry Pi Zero 2W

## Первое подключение

1. Найдите Wi-Fi сеть "TravelPi"
2. Пароль: `TravelPi2024`
3. Откройте браузер: http://192.168.4.1 или http://travelpi.local
4. Войдите:
   - Логин: `admin`
   - Пароль: `admin123`

## Важно!

⚠️ **Сразу после первого входа измените пароль администратора!**

## Проверка статуса служб

```bash
# Проверка автозапуска
sudo systemctl status travelpi-autostart

# Проверка hostapd (точка доступа)
sudo systemctl status hostapd

# Проверка dnsmasq (DHCP/DNS)
sudo systemctl status dnsmasq

# Проверка nginx (веб-сервер)
sudo systemctl status nginx

# Проверка менеджера подключений
sudo systemctl status travelpi-connection

# Проверка монитора устройств
sudo systemctl status travelpi-monitor

# Проверка монитора DNS
sudo systemctl status travelpi-dns-monitor

# Просмотр логов автозапуска
sudo cat /var/log/travelpi-autostart.log
```

## Логи

```bash
# Логи подключений
tail -f /var/log/travelpi-connection.log

# Логи nginx
tail -f /var/log/nginx/error.log

# Логи системы
journalctl -u travelpi-connection -f
```

## Устранение неполадок

### Wi-Fi точка доступа не работает
```bash
sudo systemctl restart hostapd
sudo systemctl restart dnsmasq
```

### Нет интернета
```bash
# Проверьте подключение
ip addr show

# Перезапустите менеджер подключений
sudo systemctl restart travelpi-connection
```

### Веб-интерфейс недоступен
```bash
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```
