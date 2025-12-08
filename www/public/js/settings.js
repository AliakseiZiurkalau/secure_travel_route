// Settings management

function openPihole() {
    window.open('http://travelpi.local/admin', '_blank');
}

function rebootSystem() {
    if (confirm('Перезагрузить систему?')) {
        apiCall('reboot')
            .then(() => {
                showNotification('Система перезагружается...', 'info');
            });
    }
}

function shutdownSystem() {
    if (confirm('Выключить систему?')) {
        apiCall('shutdown')
            .then(() => {
                showNotification('Система выключается...', 'info');
            });
    }
}

document.getElementById('ad_blocking')?.addEventListener('change', function() {
    const enabled = this.checked ? '1' : '0';
    apiCall('update_setting', { key: 'ad_blocking', value: enabled })
        .then(data => {
            if (data.success) {
                showNotification('Настройка сохранена', 'success');
            }
        });
});

document.getElementById('ap-settings')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    apiCall('update_ap_settings', data)
        .then(result => {
            if (result.success) {
                showNotification('Настройки сохранены. Перезагрузка...', 'success');
                setTimeout(() => location.reload(), 3000);
            }
        })
        .catch(() => {
            showNotification('Ошибка сохранения', 'error');
        });
});

document.getElementById('password-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const newPass = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('confirm_password').value;
    
    if (newPass !== confirmPass) {
        showNotification('Пароли не совпадают', 'error');
        return;
    }
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    apiCall('change_password', data)
        .then(result => {
            if (result.success) {
                showNotification('Пароль изменен', 'success');
                this.reset();
            } else {
                showNotification(result.error || 'Ошибка изменения пароля', 'error');
            }
        })
        .catch(() => {
            showNotification('Ошибка изменения пароля', 'error');
        });
});
