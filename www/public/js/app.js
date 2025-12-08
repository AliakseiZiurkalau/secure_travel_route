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
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
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
