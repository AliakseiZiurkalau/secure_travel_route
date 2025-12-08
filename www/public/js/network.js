// Network management functions

function setMode(mode) {
    apiCall('set_mode', { mode })
        .then(data => {
            if (data.success) {
                showNotification(`Режим изменен на: ${mode}`, 'success');
                setTimeout(() => location.reload(), 2000);
            }
        })
        .catch(error => {
            showNotification('Ошибка изменения режима', 'error');
        });
}

function scanNetworks() {
    showNotification('Сканирование сетей...', 'info');
    
    apiCall('scan_wifi')
        .then(data => {
            const tbody = document.getElementById('available-networks');
            tbody.innerHTML = '';
            
            data.forEach(network => {
                const row = `
                    <tr>
                        <td>${network.ssid}</td>
                        <td>${network.signal}%</td>
                        <td>${network.security}</td>
                        <td>
                            <button class="btn-small" onclick="connectWifi('${network.ssid}')">
                                Подключить
                            </button>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
            
            showNotification('Сканирование завершено', 'success');
        })
        .catch(error => {
            showNotification('Ошибка сканирования', 'error');
        });
}

function connectWifi(ssid) {
    document.getElementById('wifi-ssid').value = ssid;
    document.getElementById('wifi-ssid-display').textContent = ssid;
    document.getElementById('wifi-modal').classList.add('active');
}

function closeModal() {
    document.getElementById('wifi-modal').classList.remove('active');
}

document.getElementById('wifi-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const ssid = document.getElementById('wifi-ssid').value;
    const password = document.getElementById('wifi-password').value;
    
    apiCall('connect_wifi', { ssid, password })
        .then(data => {
            if (data.success) {
                showNotification('Подключение к сети...', 'success');
                closeModal();
                setTimeout(() => location.reload(), 3000);
            }
        })
        .catch(error => {
            showNotification('Ошибка подключения', 'error');
        });
});

function deleteNetwork(id) {
    if (confirm('Удалить эту сеть?')) {
        apiCall('delete_network', { id })
            .then(data => {
                if (data.success) {
                    showNotification('Сеть удалена', 'success');
                    location.reload();
                }
            });
    }
}
