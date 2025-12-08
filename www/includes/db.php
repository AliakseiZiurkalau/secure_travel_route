<?php
function getDatabase() {
    static $db = null;
    
    if ($db === null) {
        $db = new PDO('sqlite:/var/www/travelpi/data/travelpi.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    return $db;
}

function getSystemStats() {
    $cpu = trim(shell_exec("top -bn1 | grep 'Cpu(s)' | awk '{print $2}' | cut -d'%' -f1"));
    $memory = trim(shell_exec("free | grep Mem | awk '{print ($3/$2) * 100.0}'"));
    $temp = trim(shell_exec("vcgencmd measure_temp | cut -d'=' -f2 | cut -d\"'\" -f1"));
    $internet = checkInternetConnection();
    $interface = getCurrentInterface();
    
    return [
        'cpu' => round($cpu, 1),
        'memory' => round($memory, 1),
        'temp' => $temp,
        'internet' => $internet,
        'interface' => $interface
    ];
}

function checkInternetConnection() {
    $result = shell_exec('ping -c 1 -W 2 8.8.8.8 2>&1');
    return strpos($result, '1 received') !== false;
}

function getCurrentInterface() {
    $route = shell_exec('ip route get 8.8.8.8 2>/dev/null');
    if (preg_match('/dev\s+(\S+)/', $route, $matches)) {
        return $matches[1];
    }
    return 'none';
}

function getConnectedDevices() {
    $devices = [];
    $leases = file_get_contents('/var/lib/misc/dnsmasq.leases');
    
    if ($leases) {
        $lines = explode("\n", trim($leases));
        foreach ($lines as $line) {
            $parts = explode(' ', $line);
            if (count($parts) >= 5) {
                $devices[] = [
                    'mac' => $parts[1],
                    'ip' => $parts[2],
                    'name' => $parts[3] !== '*' ? $parts[3] : 'Unknown'
                ];
            }
        }
    }
    
    return $devices;
}

function getAllDevices() {
    $db = getDatabase();
    $stmt = $db->query('SELECT * FROM devices ORDER BY last_seen DESC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSavedWifiNetworks() {
    $db = getDatabase();
    $stmt = $db->query('SELECT * FROM wifi_networks ORDER BY priority DESC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function scanWifiNetworks() {
    $output = shell_exec('sudo iwlist wlan1 scan 2>/dev/null');
    $networks = [];
    
    if ($output) {
        preg_match_all('/ESSID:"([^"]+)"/', $output, $ssids);
        preg_match_all('/Quality=(\d+)\/70/', $output, $qualities);
        preg_match_all('/Encryption key:(on|off)/', $output, $encryptions);
        
        for ($i = 0; $i < count($ssids[1]); $i++) {
            if (!empty($ssids[1][$i])) {
                $networks[] = [
                    'ssid' => $ssids[1][$i],
                    'signal' => isset($qualities[1][$i]) ? round(($qualities[1][$i] / 70) * 100) : 0,
                    'security' => isset($encryptions[1][$i]) && $encryptions[1][$i] === 'on' ? 'WPA/WPA2' : 'Open'
                ];
            }
        }
    }
    
    return $networks;
}

function getCurrentConnection() {
    $interface = getCurrentInterface();
    if ($interface === 'none') {
        return null;
    }
    
    $db = getDatabase();
    $stmt = $db->query('SELECT * FROM connection_sessions WHERE is_active = 1 ORDER BY started_at DESC LIMIT 1');
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($session) {
        $session['duration'] = time() - strtotime($session['started_at']);
        updateSessionTraffic($session['id']);
        return $session;
    }
    
    return null;
}

function getCurrentSession() {
    $db = getDatabase();
    $stmt = $db->query('SELECT * FROM connection_sessions WHERE is_active = 1 ORDER BY started_at DESC LIMIT 1');
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getSessionById($id) {
    $db = getDatabase();
    $stmt = $db->prepare('SELECT * FROM connection_sessions WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAllSessions() {
    $db = getDatabase();
    $stmt = $db->query('SELECT * FROM connection_sessions ORDER BY started_at DESC LIMIT 50');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSessionResources($sessionId) {
    $db = getDatabase();
    $stmt = $db->prepare('SELECT * FROM session_resources WHERE session_id = ? ORDER BY request_count DESC');
    $stmt->execute([$sessionId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateSessionTraffic($sessionId) {
    // Получение статистики трафика из системы
    $stats = getTrafficStats();
    
    $db = getDatabase();
    $stmt = $db->prepare('UPDATE connection_sessions SET bytes_sent = ?, bytes_received = ?, total_bytes = ? WHERE id = ?');
    $stmt->execute([
        $stats['tx_bytes'],
        $stats['rx_bytes'],
        $stats['tx_bytes'] + $stats['rx_bytes'],
        $sessionId
    ]);
}

function getTrafficStats() {
    $interface = getCurrentInterface();
    if ($interface === 'none') {
        return ['tx_bytes' => 0, 'rx_bytes' => 0];
    }
    
    $rx = trim(shell_exec("cat /sys/class/net/$interface/statistics/rx_bytes 2>/dev/null") ?: '0');
    $tx = trim(shell_exec("cat /sys/class/net/$interface/statistics/tx_bytes 2>/dev/null") ?: '0');
    
    return [
        'rx_bytes' => intval($rx),
        'tx_bytes' => intval($tx)
    ];
}

function getAllUsers() {
    $db = getDatabase();
    $stmt = $db->query('SELECT * FROM users ORDER BY created_at ASC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
