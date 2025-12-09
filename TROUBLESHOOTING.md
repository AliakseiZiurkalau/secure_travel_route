# 🔧 Устранение неполадок TravelPi

## Проблема: Сеть TravelPi не доступна

### Быстрая диагностика

Подключитесь к Raspberry Pi через SSH или монитор и выполните:

```bash
# 1. Проверка статуса hostapd (точка доступа)
sudo systemctl status hostapd

# 2. Проверка интерфейса wlan0
ip addr show wlan0

# 3. Проверка процесса hostapd
ps aux | grep hostapd

# 4. Проверка логов
sudo journalctl -u hostapd -n 50
```

### Решение 1: Перезапуск hostapd

```bash
# Остановить hostapd
sudo systemctl stop hostapd

# Проверить, что процесс остановлен
sudo killall hostapd

# Запустить заново
sudo systemctl start hostapd

# Проверить статус
sudo systemctl status hostapd
```

### Решение 2: Проверка конфигурации

```bash
# Проверить конфигурацию hostapd
sudo cat /etc/hostapd/hostapd.conf

# Должно быть:
# interface=wlan0
# driver=nl80211
# ssid=TravelPi
# hw_mode=g
# channel=7
# ...
```

Если файл отсутствует или поврежден:

```bash
# Скопировать из проекта
sudo cp ~/travelpi/config/hostapd.conf /etc/hostapd/hostapd.conf

# Перезапустить
sudo systemctl restart hostapd
```

### Решение 3: Проверка интерфейса wlan0

```bash
# Проверить, что wlan0 существует
iwconfig

# Поднять интерфейс
sudo ip link set wlan0 up

# Назначить IP адрес
sudo ip addr add 192.168.4.1/24 dev wlan0

# Перезапустить hostapd
sudo systemctl restart hostapd
```

### Решение 4: Проверка dhcpcd

```bash
# Проверить конфигурацию dhcpcd
sudo cat /etc/dhcpcd.conf

# Должно содержать:
# interface wlan0
#     static ip_address=192.168.4.1/24
#     nohook wpa_supplicant

# Если нет, добавить:
sudo nano /etc/dhcpcd.conf

# Перезапустить dhcpcd
sudo systemctl restart dhcpcd
```

### Решение 5: Проверка rfkill (блокировка Wi-Fi)

```bash
# Проверить блокировку Wi-Fi
sudo rfkill list

# Если Wi-Fi заблокирован, разблокировать:
sudo rfkill unblock wifi
sudo rfkill unblock all

# Перезапустить hostapd
sudo systemctl restart hostapd
```

### Решение 6: Конфликт с NetworkManager

```bash
# Остановить NetworkManager (если установлен)
sudo systemctl stop NetworkManager
sudo systemctl disable NetworkManager

# Или настроить игнорирование wlan0
sudo nano /etc/NetworkManager/NetworkManager.conf

# Добавить:
# [keyfile]
# unmanaged-devices=interface-name:wlan0

# Перезапустить
sudo systemctl restart NetworkManager
sudo systemctl restart hostapd
```

### Решение 7: Полная переустановка hostapd

```bash
# Удалить hostapd
sudo apt remove --purge hostapd

# Установить заново
sudo apt update
sudo apt install hostapd

# Скопировать конфигурацию
sudo cp ~/travelpi/config/hostapd.conf /etc/hostapd/hostapd.conf

# Указать путь к конфигурации
sudo nano /etc/default/hostapd
# Раскомментировать и установить:
# DAEMON_CONF="/etc/hostapd/hostapd.conf"

# Включить и запустить
sudo systemctl unmask hostapd
sudo systemctl enable hostapd
sudo systemctl start hostapd
```

### Решение 8: Проверка драйвера Wi-Fi

```bash
# Проверить загруженные модули
lsmod | grep brcm

# Должен быть brcmfmac для встроенного Wi-Fi Raspberry Pi

# Если модуль не загружен:
sudo modprobe brcmfmac

# Перезагрузить систему
sudo reboot
```

### Решение 9: Изменение канала Wi-Fi

Иногда канал 7 может быть занят. Попробуйте другой:

```bash
sudo nano /etc/hostapd/hostapd.conf

# Изменить строку:
# channel=7
# на
# channel=1  (или 6, или 11)

sudo systemctl restart hostapd
```

### Решение 10: Проверка питания

Raspberry Pi Zero 2W требует стабильного питания:

- Используйте качественный блок питания (минимум 5V 2.5A)
- Проверьте кабель USB
- Избегайте питания от USB порта компьютера

```bash
# Проверить напряжение
vcgencmd get_throttled

# Если результат не 0x0, есть проблемы с питанием
```

## Проверка после исправления

```bash
# 1. Статус hostapd
sudo systemctl status hostapd
# Должно быть: active (running)

# 2. Интерфейс wlan0
ip addr show wlan0
# Должен быть: inet 192.168.4.1/24

# 3. Сканирование сетей с другого устройства
# На телефоне/ноутбуке должна появиться сеть "TravelPi"

# 4. Логи без ошибок
sudo journalctl -u hostapd -n 20
```

## Дополнительные проблемы

### Сеть видна, но не подключается

```bash
# Проверить dnsmasq
sudo systemctl status dnsmasq

# Перезапустить dnsmasq
sudo systemctl restart dnsmasq

# Проверить конфигурацию
sudo cat /etc/dnsmasq.conf
```

### Подключается, но нет IP адреса

```bash
# Проверить DHCP сервер
sudo systemctl status dnsmasq

# Проверить leases
sudo cat /var/lib/misc/dnsmasq.leases

# Перезапустить dnsmasq
sudo systemctl restart dnsmasq
```

### Есть IP, но нет доступа к веб-интерфейсу

```bash
# Проверить nginx
sudo systemctl status nginx

# Проверить PHP-FPM
sudo systemctl status php8.2-fpm

# Перезапустить веб-сервер
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm

# Проверить доступность
curl http://192.168.4.1
```

## Полная переустановка

Если ничего не помогает:

```bash
# 1. Остановить все службы
sudo systemctl stop hostapd dnsmasq nginx

# 2. Удалить конфигурации
sudo rm /etc/hostapd/hostapd.conf
sudo rm /etc/dnsmasq.conf
sudo rm /etc/dhcpcd.conf

# 3. Перейти в папку проекта
cd ~/travelpi

# 4. Запустить установку заново
sudo ./setup.sh

# 5. Перезагрузить
sudo reboot
```

## Логи для диагностики

```bash
# Системные логи
sudo journalctl -xe

# Логи hostapd
sudo journalctl -u hostapd -f

# Логи dnsmasq
sudo journalctl -u dnsmasq -f

# Логи автозапуска
sudo cat /var/log/travelpi-autostart.log

# Логи ядра (для проблем с драйверами)
dmesg | grep -i wifi
dmesg | grep -i brcm
```

## Тестовая конфигурация hostapd

Минимальная рабочая конфигурация для теста:

```bash
sudo nano /etc/hostapd/hostapd.conf
```

```
interface=wlan0
driver=nl80211
ssid=TestPi
hw_mode=g
channel=1
wmm_enabled=0
macaddr_acl=0
auth_algs=1
ignore_broadcast_ssid=0
wpa=2
wpa_passphrase=12345678
wpa_key_mgmt=WPA-PSK
wpa_pairwise=TKIP
rsn_pairwise=CCMP
```

```bash
sudo systemctl restart hostapd
```

Если сеть "TestPi" появилась - проблема в основной конфигурации.

## Получение помощи

Если проблема не решена, соберите диагностическую информацию:

```bash
# Создать файл с диагностикой
cat > ~/travelpi-diagnostics.txt << EOF
=== System Info ===
$(uname -a)
$(cat /etc/os-release)

=== Network Interfaces ===
$(ip addr)

=== Wireless Info ===
$(iwconfig)

=== RF Kill ===
$(rfkill list)

=== Hostapd Status ===
$(systemctl status hostapd)

=== Hostapd Config ===
$(cat /etc/hostapd/hostapd.conf)

=== Hostapd Logs ===
$(journalctl -u hostapd -n 50)

=== DHCPCD Config ===
$(cat /etc/dhcpcd.conf)

=== Dnsmasq Status ===
$(systemctl status dnsmasq)

=== Kernel Messages ===
$(dmesg | grep -i wifi | tail -20)
EOF

# Просмотреть файл
cat ~/travelpi-diagnostics.txt
```

Отправьте этот файл при создании issue на GitHub.

---

**Большинство проблем решается перезапуском hostapd и dnsmasq!** 🔄
