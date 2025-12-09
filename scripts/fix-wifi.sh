#!/bin/bash
# TravelPi Quick Fix Script
# Быстрое исправление проблем с точкой доступа

echo "🔧 TravelPi Quick Fix"
echo "Исправление проблем с точкой доступа..."
echo ""

# Проверка прав root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Запустите с sudo: sudo ./fix-wifi.sh"
    exit 1
fi

# 1. Разблокировка Wi-Fi
echo "1. Разблокировка Wi-Fi..."
rfkill unblock wifi
rfkill unblock all
sleep 1

# 2. Остановка служб
echo "2. Остановка служб..."
systemctl stop hostapd
systemctl stop dnsmasq
sleep 2

# 3. Убийство зависших процессов
echo "3. Очистка процессов..."
killall hostapd 2>/dev/null
killall dnsmasq 2>/dev/null
sleep 1

# 4. Настройка интерфейса wlan0
echo "4. Настройка интерфейса wlan0..."
ip link set wlan0 down
sleep 1
ip link set wlan0 up
sleep 1
ip addr flush dev wlan0
ip addr add 192.168.4.1/24 dev wlan0
sleep 1

# 5. Запуск hostapd
echo "5. Запуск hostapd..."
systemctl start hostapd
sleep 3

# 6. Проверка hostapd
if systemctl is-active --quiet hostapd; then
    echo "✅ hostapd запущен"
else
    echo "❌ hostapd не запустился"
    echo "Логи:"
    journalctl -u hostapd -n 10 --no-pager
    exit 1
fi

# 7. Запуск dnsmasq
echo "6. Запуск dnsmasq..."
systemctl start dnsmasq
sleep 2

# 8. Проверка dnsmasq
if systemctl is-active --quiet dnsmasq; then
    echo "✅ dnsmasq запущен"
else
    echo "❌ dnsmasq не запустился"
    journalctl -u dnsmasq -n 10 --no-pager
fi

# 9. Проверка IP forwarding
echo "7. Включение IP forwarding..."
echo 1 > /proc/sys/net/ipv4/ip_forward

# 10. Восстановление iptables
if [ -f /etc/iptables.rules ]; then
    echo "8. Восстановление iptables..."
    iptables-restore < /etc/iptables.rules
fi

# Итоговая проверка
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Проверка результата:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Проверка интерфейса
IP=$(ip addr show wlan0 | grep 'inet ' | awk '{print $2}')
if [ -n "$IP" ]; then
    echo "✅ IP адрес wlan0: $IP"
else
    echo "❌ IP адрес не назначен"
fi

# Проверка hostapd
if systemctl is-active --quiet hostapd; then
    echo "✅ hostapd: активен"
    SSID=$(grep "^ssid=" /etc/hostapd/hostapd.conf | cut -d= -f2)
    echo "   SSID: $SSID"
else
    echo "❌ hostapd: не активен"
fi

# Проверка dnsmasq
if systemctl is-active --quiet dnsmasq; then
    echo "✅ dnsmasq: активен"
else
    echo "❌ dnsmasq: не активен"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Исправление завершено!"
echo ""
echo "Попробуйте найти сеть TravelPi на вашем устройстве."
echo "Если сеть не появилась, подождите 10-20 секунд."
echo ""
echo "Для просмотра логов:"
echo "  sudo journalctl -u hostapd -f"
echo ""
