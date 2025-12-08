// Monitoring page functions

// Обновление статистики каждые 5 секунд
setInterval(updateMonitoring, 5000);

function updateMonitoring() {
    fetch('/api.php?action=monitoring_stats')
        .then(response => response.json())
        .then(data => {
            // Обновление системных ресурсов
            if (data.system) {
                updateElement('cpu-value', data.system.cpu + '%');
                updateElement('memory-value', data.system.memory + '%');
                updateElement('temp-value', data.system.temp + '°C');
                updateElement('devices-value', data.system.devices);
                
                updateProgressBar('cpu', data.system.cpu);
                updateProgressBar('memory', data.system.memory);
            }
            
            // Обновление данных сессии
            if (data.session) {
                updateElement('session-duration', formatDuration(data.session.duration));
                updateElement('bytes-received', formatBytes(data.session.bytes_received));
                updateElement('bytes-sent', formatBytes(data.session.bytes_sent));
                updateElement('total-bytes', formatBytes(data.session.total_bytes));
            }
        })
        .catch(error => {
            console.error('Ошибка обновления мониторинга:', error);
        });
}

function updateElement(id, value) {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = value;
    }
}

function updateProgressBar(name, value) {
    const bar = document.querySelector(`#${name}-value`).closest('.stat-card').querySelector('.progress-fill');
    if (bar) {
        bar.style.width = value + '%';
    }
}

function formatDuration(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    
    if (hours > 0) {
        return `${hours} ч ${minutes} мин`;
    } else if (minutes > 0) {
        return `${minutes} мин ${secs} сек`;
    } else {
        return `${secs} сек`;
    }
}

function formatBytes(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';
    if (bytes < 1073741824) return (bytes / 1048576).toFixed(2) + ' MB';
    return (bytes / 1073741824).toFixed(2) + ' GB';
}
