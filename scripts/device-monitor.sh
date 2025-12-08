#!/bin/bash
# TravelPi Device Monitor
# Мониторинг подключенных устройств и обновление базы данных

DB_PATH="/var/www/travelpi/data/travelpi.db"
LEASES_FILE="/var/lib/misc/dnsmasq.leases"

update_devices() {
    if [ ! -f "$LEASES_FILE" ]; then
        return
    fi
    
    while read -r line; do
        MAC=$(echo "$line" | awk '{print $2}')
        IP=$(echo "$line" | awk '{print $3}')
        NAME=$(echo "$line" | awk '{print $4}')
        
        if [ -n "$MAC" ]; then
            # Проверяем, есть ли устройство в базе
            EXISTS=$(sqlite3 "$DB_PATH" "SELECT COUNT(*) FROM devices WHERE mac_address='$MAC'")
            
            if [ "$EXISTS" -eq 0 ]; then
                # Добавляем новое устройство
                sqlite3 "$DB_PATH" "INSERT INTO devices (mac_address, device_name, last_seen) VALUES ('$MAC', '$NAME', datetime('now'))"
                echo "Новое устройство: $MAC ($NAME)"
            else
                # Обновляем время последнего подключения
                sqlite3 "$DB_PATH" "UPDATE devices SET last_seen=datetime('now') WHERE mac_address='$MAC'"
            fi
            
            # Проверяем, разрешено ли устройство
            ALLOWED=$(sqlite3 "$DB_PATH" "SELECT is_allowed FROM devices WHERE mac_address='$MAC'")
            
            if [ "$ALLOWED" -eq 0 ]; then
                # Блокируем устройство через iptables
                sudo iptables -I FORWARD -m mac --mac-source "$MAC" -j DROP
                echo "Заблокировано устройство: $MAC"
            fi
        fi
    done < "$LEASES_FILE"
}

# Запускаем мониторинг каждые 30 секунд
while true; do
    update_devices
    sleep 30
done
