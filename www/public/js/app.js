// TravelPi Web Interface JavaScript

// Обновление статистики каждые 5 секунд
setInterval(updateStats, 5000);

function updateStats() {
    fetch('/api.php?action=stats')
        .then(response => response.json())
        .then(data => {
            // Обновление статистики на странице
            console.log('Stats updated:', data);
        })
        .catch(error => console.error('Error:', error));
}

// Утилиты
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Стили для уведомления
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        background: ${type === 'error' ? '#e74c3c' : type === 'success' ? '#2ecc71' : '#3498db'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function apiCall(action, data = {}) {
    const formData = new FormData();
    for (const key in data) {
        formData.append(key, data[key]);
    }
    
    return fetch(`/api.php?action=${action}`, {
        method: 'POST',
        body: formData
    }).then(response => response.json());
}

// Быстрое выключение
function quickShutdown() {
    const modal = document.getElementById('shutdown-confirm-modal');
    if (modal) {
        modal.classList.add('active');
    }
}

function closeShutdownModal() {
    const modal = document.getElementById('shutdown-confirm-modal');
    if (modal) {
        modal.classList.remove('active');
    }
}

function confirmShutdown() {
    closeShutdownModal();
    showNotification('Система выключается...', 'info');
    
    apiCall('shutdown')
        .then(() => {
            document.body.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: center; height: 100vh; flex-direction: column; background: #e74c3c; color: white; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                    <h1 style="font-size: 4em; margin-bottom: 20px; animation: pulse 2s infinite;">⏻</h1>
                    <h2 style="font-size: 2em; margin-bottom: 10px;">Система выключается...</h2>
                    <p style="margin-top: 20px; opacity: 0.9; font-size: 1.1em;">Устройство будет полностью выключено через несколько секунд</p>
                    <p style="margin-top: 10px; opacity: 0.8;">Для включения используйте кнопку питания на Raspberry Pi</p>
                </div>
                <style>
                    @keyframes pulse {
                        0%, 100% { transform: scale(1); opacity: 1; }
                        50% { transform: scale(1.1); opacity: 0.8; }
                    }
                </style>
            `;
        })
        .catch(() => {
            showNotification('Ошибка выключения', 'error');
        });
}

// Добавление кнопки быстрого выключения и модального окна при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Добавляем плавающую кнопку выключения
    const shutdownBtn = document.createElement('button');
    shutdownBtn.className = 'quick-shutdown';
    shutdownBtn.innerHTML = '⏻';
    shutdownBtn.title = 'Быстрое выключение';
    shutdownBtn.onclick = quickShutdown;
    document.body.appendChild(shutdownBtn);
    
    // Добавляем модальное окно подтверждения
    const modal = document.createElement('div');
    modal.id = 'shutdown-confirm-modal';
    modal.className = 'confirm-modal';
    modal.innerHTML = `
        <div class="confirm-content">
            <h3>⚠️ Выключить систему?</h3>
            <p>Устройство полностью выключится.<br>Для включения потребуется физический доступ к Raspberry Pi.</p>
            <div class="confirm-actions">
                <button class="btn btn-danger" onclick="confirmShutdown()">Выключить</button>
                <button class="btn" onclick="closeShutdownModal()">Отмена</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    // Закрытие модального окна при клике вне его
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeShutdownModal();
        }
    });
});
