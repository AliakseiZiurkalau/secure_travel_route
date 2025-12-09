#!/bin/bash
# TravelPi Diagnostic Script
# Быстрая диагностика проблем с точкой доступа

echo "╔════════════════════════════════════════╗"
echo "║   TravelPi Diagnostic Tool             ║"
echo "╚════════════════════════════════════════╝"
echo ""

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

check_status() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓${NC} $1"
    else
        echo -e "${RED}✗${NC} $1"
    fi
}

# 1. Проверка интерфейса wlan0
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "1. Проверка интерфейса wlan0"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
if ip link show wlan0 &>/dev/null; then
    echo -e "${GREEN}✓${NC} Интерфейс wlan0 существует"
    
    # Проверка IP адреса
    IP=$(ip addr show wlan0 | grep 'inet ' | awk '{print $2}')
    if [ -n "$IP" ]; then
        echo -e "${GREEN}✓${NC} IP адрес: $IP"
    else
        echo -e "${RED}✗${NC} IP адрес не назначен"
        echo -e "${YELLOW}→${NC} Исправление: sudo ip addr add 192.168.4.1/24 dev wlan0"
    fi
    
    # Проверка состояния интерфейса
    STATE=$(ip link show wlan0 | grep -o 'state [A-Z]*' | awk '{print $2}')
    if [ "$STATE" = "UP" ]; then
        echo -e "${GREEN}✓${NC} Интерфейс активен (UP)"
    else
        echo -e "${RED}✗${NC} Интерфейс неактивен ($STATE)"
        echo -e "${YELLOW}→${NC} Исправление: sudo ip link set wlan0 up"
    fi
else
    echo -e "${RED}✗${NC} Интерфейс wlan0 не найден"
    echo -e "${YELLOW}→${NC} Проверьте драйвер: lsmod | grep brcm"
fi
echo ""

# 2. Проверка RF Kill
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "2. Проверка блокировки Wi-Fi (rfkill)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
if command -v rfkill &>/dev/null; then
    BLOCKED=$(rfkill list wifi | grep -c "Soft blocked: yes")
    if [ "$BLOCKED" -eq 0 ]; then
        echo -e "${GREEN}✓${NC} Wi-Fi не заблокирован"
    else
        echo -e "${RED}✗${NC} Wi-Fi заблокирован"
        echo -e "${YELLOW}→${NC} Исправление: sudo rfkill unblock wifi"
    fi
else
    echo -e "${YELLOW}!${NC} rfkill не установлен"
fi
echo ""

# 3. Проверка hostapd
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "3. Проверка hostapd (точка доступа)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
if systemctl is-active --quiet hostapd; then
    echo -e "${GREEN}✓${NC} hostapd запущен"
else
    echo -e "${RED}✗${NC} hostapd не запущен"
    echo -e "${YELLOW}→${NC} Исправление: sudo systemctl start hostapd"
fi

if systemctl is-enabled --quiet hostapd; then
    echo -e "${GREEN}✓${NC} hostapd включен в автозапуск"
else
    echo -e "${YELLOW}!${NC} hostapd не включен в автозапуск"
    echo -e "${YELLOW}→${NC} Исправление: sudo systemctl enable hostapd"
fi

# Проверка конфигурации
if [ -f /etc/hostapd/hostapd.conf ]; then
    echo -e "${GREEN}✓${NC} Конфигурация hostapd существует"
    
    # Проверка SSID
    SSID=$(grep "^ssid=" /etc/hostapd/hostapd.conf | cut -d= -f2)
    if [ -n "$SSID" ]; then
        echo -e "${GREEN}✓${NC} SSID: $SSID"
    fi
    
    # Проверка интерфейса в конфиге
    IFACE=$(grep "^interface=" /etc/hostapd/hostapd.conf | cut -d= -f2)
    if [ "$IFACE" = "wlan0" ]; then
        echo -e "${GREEN}✓${NC} Интерфейс в конфиге: $IFACE"
    else
        echo -e "${RED}✗${NC} Неверный интерфейс в конфиге: $IFACE"
    fi
else
    echo -e "${RED}✗${NC} Конфигурация hostapd не найдена"
    echo -e "${YELLOW}→${NC} Исправление: sudo cp ~/travelpi/config/hostapd.conf /etc/hostapd/"
fi
echo ""

# 4. Проверка dnsmasq
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "4. Проверка dnsmasq (DHCP/DNS)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
if systemctl is-active --quiet dnsmasq; then
    echo -e "${GREEN}✓${NC} dnsmasq запущен"
else
    echo -e "${RED}✗${NC} dnsmasq не запущен"
    echo -e "${YELLOW}→${NC} Исправление: sudo systemctl start dnsmasq"
fi

if [ -f /etc/dnsmasq.conf ]; then
    echo -e "${GREEN}✓${NC} Конфигурация dnsmasq существует"
else
    echo -e "${RED}✗${NC} Конфигурация dnsmasq не найдена"
fi
echo ""

# 5. Проверка веб-сервера
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "5. Проверка веб-сервера"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
if systemctl is-active --quiet nginx; then
    echo -e "${GREEN}✓${NC} nginx запущен"
else
    echo -e "${RED}✗${NC} nginx не запущен"
    echo -e "${YELLOW}→${NC} Исправление: sudo systemctl start nginx"
fi

if systemctl is-active --quiet php8.2-fpm; then
    echo -e "${GREEN}✓${NC} PHP-FPM запущен"
else
    echo -e "${RED}✗${NC} PHP-FPM не запущен"
    echo -e "${YELLOW}→${NC} Исправление: sudo systemctl start php8.2-fpm"
fi
echo ""

# 6. Проверка питания
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "6. Проверка питания"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
THROTTLED=$(vcgencmd get_throttled | cut -d= -f2)
if [ "$THROTTLED" = "0x0" ]; then
    echo -e "${GREEN}✓${NC} Питание в норме"
else
    echo -e "${RED}✗${NC} Проблемы с питанием (throttled: $THROTTLED)"
    echo -e "${YELLOW}→${NC} Используйте качественный блок питания 5V 2.5A+"
fi

TEMP=$(vcgencmd measure_temp | cut -d= -f2 | cut -d\' -f1)
echo "   Температура CPU: ${TEMP}°C"
if (( $(echo "$TEMP > 70" | bc -l) )); then
    echo -e "${YELLOW}!${NC} Высокая температура! Требуется охлаждение"
fi
echo ""

# 7. Последние ошибки
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "7. Последние ошибки hostapd"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
journalctl -u hostapd -n 5 --no-pager | grep -i error
echo ""

# Итоговая рекомендация
echo "╔════════════════════════════════════════╗"
echo "║   Быстрое исправление                  ║"
echo "╚════════════════════════════════════════╝"
echo ""
echo "Если сеть TravelPi не видна, выполните:"
echo ""
echo -e "${YELLOW}sudo rfkill unblock wifi${NC}"
echo -e "${YELLOW}sudo systemctl restart hostapd${NC}"
echo -e "${YELLOW}sudo systemctl restart dnsmasq${NC}"
echo ""
echo "Для полной диагностики:"
echo -e "${YELLOW}sudo journalctl -u hostapd -f${NC}"
echo ""
echo "Для полной переустановки:"
echo -e "${YELLOW}cd ~/travelpi && sudo ./setup.sh${NC}"
echo ""
