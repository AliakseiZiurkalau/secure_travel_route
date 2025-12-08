#!/bin/bash
# TravelPi Autostart Script
# Автоматический запуск всех компонентов при загрузке системы

LOG_FILE="/var/log/travelpi-autostart.log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log "=== TravelPi Autostart ==="

# Ожидание готовности сети
log "Ожидание готовности сети..."
sleep 5

# Проверка и запуск hostapd (точка доступа)
if ! systemctl is-active --quiet hostapd; then
    log "Запуск hostapd..."
    systemctl start hostapd
    sleep 2
fi

# Проверка и запуск dnsmasq (DHCP/DNS)
if ! systemctl is-active --quiet dnsmasq; then
    log "Запуск dnsmasq..."
    systemctl start dnsmasq
    sleep 2
fi

# Проверка и запуск nginx
if ! systemctl is-active --quiet nginx; then
    log "Запуск nginx..."
    systemctl start nginx
    sleep 1
fi

# Проверка и запуск PHP-FPM
if ! systemctl is-active --quiet php8.2-fpm; then
    log "Запуск PHP-FPM..."
    systemctl start php8.2-fpm
    sleep 1
fi

# Проверка и запуск Pi-hole
if command -v pihole &> /dev/null; then
    if ! systemctl is-active --quiet pihole-FTL; then
        log "Запуск Pi-hole..."
        systemctl start pihole-FTL
        sleep 2
    fi
fi

# Запуск менеджера подключений
if ! systemctl is-active --quiet travelpi-connection; then
    log "Запуск менеджера подключений..."
    systemctl start travelpi-connection
fi

# Запуск монитора устройств
if ! systemctl is-active --quiet travelpi-monitor; then
    log "Запуск монитора устройств..."
    systemctl start travelpi-monitor
fi

# Запуск монитора DNS
if ! systemctl is-active --quiet travelpi-dns-monitor; then
    log "Запуск монитора DNS..."
    systemctl start travelpi-dns-monitor
fi

# Проверка статуса всех служб
log "Проверка статуса служб..."
SERVICES=("hostapd" "dnsmasq" "nginx" "php8.2-fpm" "travelpi-connection" "travelpi-monitor" "travelpi-dns-monitor")

for service in "${SERVICES[@]}"; do
    if systemctl is-active --quiet "$service"; then
        log "✓ $service: активна"
    else
        log "✗ $service: не активна"
    fi
done

# Настройка IP forwarding (на случай если не применилось)
if [ "$(cat /proc/sys/net/ipv4/ip_forward)" != "1" ]; then
    log "Включение IP forwarding..."
    echo 1 > /proc/sys/net/ipv4/ip_forward
fi

# Восстановление правил iptables
if [ -f /etc/iptables.rules ]; then
    log "Восстановление правил iptables..."
    iptables-restore < /etc/iptables.rules
fi

# Вывод информации о системе
log "=== Информация о системе ==="
log "IP адрес точки доступа: $(ip addr show wlan0 | grep 'inet ' | awk '{print $2}' | cut -d/ -f1)"
log "Температура CPU: $(vcgencmd measure_temp | cut -d= -f2)"
log "Использование памяти: $(free -h | grep Mem | awk '{print $3 "/" $2}')"

log "=== TravelPi успешно запущен ==="
log "Веб-интерфейс доступен по адресу: http://192.168.4.1"
log "Или: http://travelpi.local"

exit 0
