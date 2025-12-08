<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'stats':
        echo json_encode(getSystemStats());
        break;
        
    case 'monitoring_stats':
        $session = getCurrentSession();
        if ($session) {
            updateSessionTraffic($session['id']);
            $session = getCurrentSession(); // Обновленные данные
        }
        echo json_encode([
            'system' => array_merge(getSystemStats(), ['devices' => count(getConnectedDevices())]),
            'session' => $session ? [
                'duration' => time() - strtotime($session['started_at']),
                'bytes_received' => $session['bytes_received'],
                'bytes_sent' => $session['bytes_sent'],
                'total_bytes' => $session['total_bytes']
            ] : null
        ]);
        break;
        
    case 'devices':
        echo json_encode(getConnectedDevices());
        break;
        
    case 'scan_wifi':
        echo json_encode(scanWifiNetworks());
        break;
        
    case 'connect_wifi':
        $ssid = $_POST['ssid'] ?? '';
        $password = $_POST['password'] ?? '';
        $result = connectToWifi($ssid, $password);
        echo json_encode(['success' => $result]);
        break;
        
    case 'connect_saved':
        $id = $_POST['id'] ?? 0;
        $result = connectSavedNetwork($id);
        echo json_encode(['success' => $result]);
        break;
        
    case 'delete_saved':
        $id = $_POST['id'] ?? 0;
        $db = getDatabase();
        $stmt = $db->prepare('DELETE FROM wifi_networks WHERE id = ?');
        $result = $stmt->execute([$id]);
        echo json_encode(['success' => $result]);
        break;
        
    case 'disconnect':
        $result = disconnectNetwork();
        echo json_encode(['success' => $result]);
        break;
        
    case 'set_mode':
        $mode = $_POST['mode'] ?? 'auto';
        $result = setConnectionMode($mode);
        echo json_encode(['success' => $result]);
        break;
        
    case 'block_device':
        $id = $_POST['id'] ?? 0;
        $db = getDatabase();
        $stmt = $db->prepare('UPDATE devices SET is_allowed = 0 WHERE id = ?');
        $result = $stmt->execute([$id]);
        echo json_encode(['success' => $result]);
        break;
        
    case 'allow_device':
        $id = $_POST['id'] ?? 0;
        $db = getDatabase();
        $stmt = $db->prepare('UPDATE devices SET is_allowed = 1 WHERE id = ?');
        $result = $stmt->execute([$id]);
        echo json_encode(['success' => $result]);
        break;
        
    case 'update_device_name':
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $db = getDatabase();
        $stmt = $db->prepare('UPDATE devices SET device_name = ? WHERE id = ?');
        $result = $stmt->execute([$name, $id]);
        echo json_encode(['success' => $result]);
        break;
        
    case 'add_user':
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $result = addUser($username, $password);
        echo json_encode($result);
        break;
        
    case 'block_user':
        $id = $_POST['id'] ?? 0;
        $db = getDatabase();
        $stmt = $db->prepare('UPDATE users SET is_allowed = 0 WHERE id = ? AND username != "admin"');
        $result = $stmt->execute([$id]);
        echo json_encode(['success' => $result]);
        break;
        
    case 'allow_user':
        $id = $_POST['id'] ?? 0;
        $db = getDatabase();
        $stmt = $db->prepare('UPDATE users SET is_allowed = 1 WHERE id = ?');
        $result = $stmt->execute([$id]);
        echo json_encode(['success' => $result]);
        break;
        
    case 'reset_user_password':
        $id = $_POST['id'] ?? 0;
        $password = $_POST['password'] ?? '';
        $result = resetUserPassword($id, $password);
        echo json_encode(['success' => $result]);
        break;
        
    case 'delete_user':
        $id = $_POST['id'] ?? 0;
        $db = getDatabase();
        $stmt = $db->prepare('DELETE FROM users WHERE id = ? AND username != "admin"');
        $result = $stmt->execute([$id]);
        echo json_encode(['success' => $result]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function connectToWifi($ssid, $password) {
    $db = getDatabase();
    
    // Завершаем текущую сессию
    $db->exec('UPDATE connection_sessions SET is_active = 0, ended_at = datetime("now") WHERE is_active = 1');
    
    // Сохраняем сеть
    $stmt = $db->prepare('INSERT OR REPLACE INTO wifi_networks (ssid, password) VALUES (?, ?)');
    $stmt->execute([$ssid, $password]);
    
    // Подключаемся
    $config = "network={\n";
    $config .= "    ssid=\"$ssid\"\n";
    $config .= "    psk=\"$password\"\n";
    $config .= "}\n";
    
    file_put_contents('/tmp/wpa_temp.conf', $config);
    shell_exec('sudo wpa_cli -i wlan1 reconfigure');
    
    // Создаем новую сессию
    sleep(3);
    $interface = getCurrentInterface();
    if ($interface !== 'none') {
        $stmt = $db->prepare('INSERT INTO connection_sessions (ssid, interface) VALUES (?, ?)');
        $stmt->execute([$ssid, $interface]);
    }
    
    return true;
}

function connectSavedNetwork($id) {
    $db = getDatabase();
    $stmt = $db->prepare('SELECT * FROM wifi_networks WHERE id = ?');
    $stmt->execute([$id]);
    $network = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($network) {
        return connectToWifi($network['ssid'], $network['password']);
    }
    
    return false;
}

function disconnectNetwork() {
    $db = getDatabase();
    $db->exec('UPDATE connection_sessions SET is_active = 0, ended_at = datetime("now") WHERE is_active = 1');
    
    shell_exec('sudo wpa_cli -i wlan1 disconnect');
    shell_exec('sudo ip link set eth0 down');
    
    return true;
}

function setConnectionMode($mode) {
    $db = getDatabase();
    $stmt = $db->prepare('UPDATE settings SET value = ? WHERE key = "connection_mode"');
    return $stmt->execute([$mode]);
}

function addUser($username, $password) {
    if (strlen($username) < 3 || strlen($password) < 6) {
        return ['success' => false, 'error' => 'Имя пользователя должно быть минимум 3 символа, пароль - 6'];
    }
    
    $db = getDatabase();
    
    // Проверка существования
    $stmt = $db->prepare('SELECT COUNT(*) as count FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        return ['success' => false, 'error' => 'Пользователь уже существует'];
    }
    
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
    $success = $stmt->execute([$username, $passwordHash]);
    
    return ['success' => $success];
}

function resetUserPassword($id, $password) {
    if (strlen($password) < 6) {
        return false;
    }
    
    $db = getDatabase();
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    return $stmt->execute([$passwordHash, $id]);
}
