#!/bin/bash
# TravelPi Setup Script
# Установка и настройка защищенного роутера

set -e

echo "=== TravelPi Setup ==="
echo "Обновление системы..."
sudo apt update && sudo apt upgrade -y

echo "Установка необходимых пакетов..."
sudo apt install -y \
    hostapd \
    dnsmasq \
    iptables \
    nginx \
    php-fpm php-sqlite3 \
    sqlite3 \
    python3-pip \
    git \
    net-tools \
    wireless-tools \
    wpasupplicant

echo "Установка Pi-hole для блокировки рекламы..."
curl -sSL https://install.pi-hole.net | bash

echo "Копирование конфигурационных файлов..."
sudo cp config/hostapd.conf /etc/hostapd/hostapd.conf
sudo cp config/dnsmasq.conf /etc/dnsmasq.conf
sudo cp config/dhcpcd.conf /etc/dhcpcd.conf

echo "Настройка веб-сервера..."
sudo cp config/nginx-travelpi.conf /etc/nginx/sites-available/travelpi
sudo ln -sf /etc/nginx/sites-available/travelpi /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

echo "Создание базы данных..."
sqlite3 /var/www/travelpi/data/travelpi.db < database/schema.sql

echo "Настройка прав доступа..."
sudo chown -R www-data:www-data /var/www/travelpi
sudo chmod 755 /var/www/travelpi
sudo chmod 666 /var/www/travelpi/data/travelpi.db

echo "Настройка sudo для веб-сервера..."
sudo cp config/travelpi-sudoers /etc/sudoers.d/travelpi
sudo chmod 440 /etc/sudoers.d/travelpi

echo "Включение IP forwarding..."
sudo sed -i 's/#net.ipv4.ip_forward=1/net.ipv4.ip_forward=1/' /etc/sysctl.conf
sudo sysctl -p

echo "Настройка iptables..."
sudo iptables -t nat -A POSTROUTING -o wlan1 -j MASQUERADE
sudo iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE
sudo sh -c "iptables-save > /etc/iptables.rules"

echo "Создание службы для восстановления iptables..."
sudo cp config/iptables-restore.service /etc/systemd/system/
sudo systemctl enable iptables-restore

echo "Установка скриптов автозапуска..."
sudo cp scripts/autostart.sh /usr/local/bin/travelpi-autostart.sh
sudo chmod +x /usr/local/bin/travelpi-autostart.sh
sudo cp systemd/travelpi-autostart.service /etc/systemd/system/

echo "Включение служб..."
sudo systemctl daemon-reload
sudo systemctl unmask hostapd
sudo systemctl enable hostapd
sudo systemctl enable dnsmasq
sudo systemctl enable nginx
sudo systemctl enable php8.2-fpm
sudo systemctl enable travelpi-autostart

echo "=== Установка завершена ==="
echo ""
echo "✅ TravelPi установлен и настроен для автозапуска"
echo ""
echo "Перезагрузите систему: sudo reboot"
echo ""
echo "После перезагрузки:"
echo "  - Точка доступа: TravelPi (пароль: TravelPi2024)"
echo "  - Веб-интерфейс: http://192.168.4.1 или http://travelpi.local"
echo "  - Логин: admin / Пароль: admin123"
echo ""
echo "⚠️  Не забудьте сменить пароли после первого входа!"
