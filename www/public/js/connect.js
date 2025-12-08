// Connection management

function setMode(mode) {
    // Обновление активной кнопки
    document.querySelectorAll('.mode-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-mode="${mode}"]`).classList.add('active');
    
    apiCall('set_mode', { mode })
        .then(data => {
            if (data.success) {
                showNotification(`Режим изменен: ${getModeText(mode)}`, 'success');
            }
        })
        .catch(() => {
            showNotification('Ошибка изменения режима', 'error');
        });
}

function getModeText(mode) {
    const modes = {
        'wifi': 'USB Wi-Fi',
        'lan': 'USB LAN',
        'auto': 'Автоматический'
    };
    return modes[mode] || mode;
}

function scanNetworks() {
    showNotification('Сканирование сетей...', 'info');
    
    fetch('/api.php?action=scan_wifi')
        .then(response => response.json())
        .then(networks => {
            const container = document.querySelector('.networks-list');
            
            if (networks.length === 0) {
                container.innerHTML = '<p class="empty-state">Сети не найдены</p>';
                return;
            }
            
            container.innerHTML = networks.map(network => `
                <div class="network-card">
                    <div class="network-info">
                        <h3>${escapeHtml(network.ssid)}</h3>
                        <div class="network-details">
                            <span class="signal">
                                ${getSignalIcon(network.signal)} ${network.signal}%
                            </span>
                            <span class="security">
                                ${network.security === 'Open' ? '🔓' : '🔒'} 
                                ${network.security}
                            </span>
                        </div>
                    </div>
                    <button class="btn-primary" onclick="connectWifi('${escapeHtml(network.ssid)}')">
                        Подключить
                    </button>
                </div>
            `).join('');
            
            showNotification('Сканирование завершено', 'success');
        })
        .catch(() => {
            showNotification('Ошибка сканирования', 'error');
        });
}

function getSignalIcon(signal) {
    if (signal >= 75) return '📶';
    if (signal >= 50) return '📶';
    if (signal >= 25) return '📶';
    return '📶';
}

function connectWifi(ssid) {
    document.getElementById('wifi-ssid').value = ssid;
    document.getElementById('wifi-ssid-display').textContent = ssid;
    document.getElementById('wifi-modal').classList.add('active');
}

function closeModal() {
    document.getElementById('wifi-modal').classList.remove('active');
    document.getElementById('wifi-form').reset();
}

document.getElementById('wifi-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const ssid = document.getElementById('wifi-ssid').value;
    const password = document.getElementById('wifi-password').value;
    
    showNotification('Подключение к сети...', 'info');
    
    apiCall('connect_wifi', { ssid, password })
        .then(data => {
            if (data.success) {
                showNotification('Подключено успешно!', 'success');
                closeModal();
                setTimeout(() => location.reload(), 2000);
            } else {
                showNotification('Ошибка подключения', 'error');
            }
        })
        .catch(() => {
            showNotification('Ошибка подключения', 'error');
        });
});

function connectSaved(id) {
    showNotification('Подключение...', 'info');
    
    apiCall('connect_saved', { id })
        .then(data => {
            if (data.success) {
                showNotification('Подключено!', 'success');
                setTimeout(() => location.reload(), 2000);
            }
        })
        .catch(() => {
            showNotification('Ошибка подключения', 'error');
        });
}

function deleteSaved(id) {
    if (confirm('Удалить эту сеть из сохраненных?')) {
        apiCall('delete_saved', { id })
            .then(data => {
                if (data.success) {
                    showNotification('Сеть удалена', 'success');
                    location.reload();
                }
            });
    }
}

function disconnect() {
    if (confirm('Отключиться от текущей сети?')) {
        apiCall('disconnect')
            .then(data => {
                if (data.success) {
                    showNotification('Отключено', 'success');
                    setTimeout(() => location.reload(), 1000);
                }
            });
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
