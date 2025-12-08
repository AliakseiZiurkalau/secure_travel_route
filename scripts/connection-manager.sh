#!/bin/bash
# TravelPi Connection Manager
# Автоматическое переключение между USB Wi-Fi и USB LAN

DB_PATH="/var/www/travelpi/data/travelpi.db"
LOG_FILE="/var/log/travelpi-connection.log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

get_connection_mode() {
    sqlite3 "$DB_PATH" "SELECT value FROM settings WHERE key='connection_mode'" 2>/dev/null || echo "auto"
}

check_interface() {
    local interface=$1
    if ip link show "$interface" &>/dev/null; then
        if ip addr show "$interface" | grep -q "inet "; then
            if ping -c 1 -W 2 -I "$interface" 8.8.8.8 &>/dev/null; then
                return 0
            fi
        fi
    fi
    return 1
}

connect_wifi() {
    log "Попытка подключения через USB Wi-Fi (wlan1)"
    
    if ! ip link show wlan1 &>/dev/null; then
        log "USB Wi-Fi адаптер не найден"
        return 1
    fi
    
    sudo ip link set wlan1 up
    sudo wpa_supplicant -B -i wlan1 -c /etc/wpa_supplicant/wpa_supplicant.conf
    sudo dhclient wlan1
    
    sleep 5
    
    if check_interface wlan1; then
        log "Успешное подключение через USB Wi-Fi"
        return 0
    else
        log "Не удалось подключиться через USB Wi-Fi"
        return 1
    fi
}

connect_lan() {
    log "Попытка подключения через USB LAN (eth0)"
    
    if ! ip link show eth0 &>/dev/null; then
        log "USB LAN адаптер не найден"
        return 1
    fi
    
    sudo ip link set eth0 up
    sudo dhclient eth0
    
    sleep 3
    
    if check_interface eth0; then
        log "Успешное подключение через USB LAN"
        return 0
    else
        log "Не удалось подключиться через USB LAN"
        return 1
    fi
}

main() {
    MODE=$(get_connection_mode)
    log "Режим подключения: $MODE"
    
    case "$MODE" in
        wifi)
            connect_wifi
            ;;
        lan)
            connect_lan
            ;;
        auto)
            # Сначала пробуем LAN (более стабильное), потом Wi-Fi
            if ! connect_lan; then
                connect_wifi
            fi
            ;;
    esac
}

main
