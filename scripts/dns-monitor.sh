#!/bin/bash
# TravelPi DNS Monitor
# Мониторинг DNS запросов и запись посещенных ресурсов

DB_PATH="/var/www/travelpi/data/travelpi.db"
PIHOLE_LOG="/var/log/pihole.log"

# Получение ID активной сессии
get_active_session() {
    sqlite3 "$DB_PATH" "SELECT id FROM connection_sessions WHERE is_active = 1 LIMIT 1" 2>/dev/null
}

# Обработка DNS запроса
process_dns_query() {
    local domain=$1
    local session_id=$2
    
    # Игнорируем локальные и служебные домены
    if [[ "$domain" =~ ^(localhost|travelpi\.local|pi\.hole)$ ]]; then
        return
    fi
    
    # Проверяем, есть ли уже запись для этого домена в текущей сессии
    local exists=$(sqlite3 "$DB_PATH" "SELECT COUNT(*) FROM session_resources WHERE session_id=$session_id AND domain='$domain'")
    
    if [ "$exists" -eq 0 ]; then
        # Добавляем новую запись
        sqlite3 "$DB_PATH" "INSERT INTO session_resources (session_id, domain) VALUES ($session_id, '$domain')"
    else
        # Обновляем счетчик и время последнего доступа
        sqlite3 "$DB_PATH" "UPDATE session_resources SET request_count = request_count + 1, last_access = datetime('now') WHERE session_id=$session_id AND domain='$domain'"
    fi
}

# Мониторинг логов dnsmasq
monitor_dnsmasq() {
    tail -F /var/log/syslog 2>/dev/null | while read line; do
        # Проверяем активную сессию
        SESSION_ID=$(get_active_session)
        
        if [ -z "$SESSION_ID" ]; then
            continue
        fi
        
        # Извлекаем домен из запроса dnsmasq
        if echo "$line" | grep -q "dnsmasq.*query\[A\]"; then
            DOMAIN=$(echo "$line" | grep -oP 'query\[A\] \K[^ ]+')
            
            if [ -n "$DOMAIN" ]; then
                process_dns_query "$DOMAIN" "$SESSION_ID"
            fi
        fi
    done
}

# Запуск мониторинга
echo "Starting DNS monitor..."
monitor_dnsmasq
