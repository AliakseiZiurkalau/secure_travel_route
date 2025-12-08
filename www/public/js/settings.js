// Settings management

function openPihole() {
    window.open('http://travelpi.local/admin', '_blank');
}

function rebootSystem() {
    if (confirm('⚠️ Перезагрузить систему?\n\nВсе подключения будут разорваны на время перезагрузки.')) {
        showNotification('Система перезагружается...', 'info');
        
        apiCall('reboot')
            .then(() => {
                // Показываем сообщение о перезагрузке
                document.body.innerHTML = `
                    <div style="display: flex; align-items: center; justify-content: center; height: 100vh; flex-direction: column; background: #2c3e50; color: white;">
                        <h1 style="font-size: 3em; margin-bottom: 20px;">🔄</h1>
                        <h2>Система перезагружается...</h2>
                        <p style="margin-top: 20px; color: #95a5a6;">Подождите около 30 секунд</p>
                        <p style="margin-top: 10px; color: #95a5a6;">Страница обновится автоматически</p>
                    </div>
                `;
                
                // Пытаемся переподключиться через 30 секунд
                setTimeout(() => {
                    location.reload();
                }, 30000);
            })
            .catch(() => {
                showNotification('Ошибка перезагрузки', 'error');
            });
    }
}

function shutdownSystem() {
    if (confirm('⚠️ ВНИМАНИЕ! Выключить систему?\n\nУстройство полностью выключится.\nДля включения потребуется физический доступ к Raspberry Pi.\n\nВы уверены?')) {
        showNotification('Система выключается...', 'info');
        
        apiCall('shutdown')
            .then(() => {
                // Показываем сообщение о выключении
                document.body.innerHTML = `
                    <div style="display: flex; align-items: center; justify-content: center; height: 100vh; flex-direction: column; background: #e74c3c; color: white;">
                        <h1 style="font-size: 3em; margin-bottom: 20px;">⏻</h1>
                        <h2>Система выключается...</h2>
                        <p style="margin-top: 20px; opacity: 0.9;">Устройство будет полностью выключено через несколько секунд</p>
                        <p style="margin-top: 10px; opacity: 0.9;">Для включения используйте кнопку питания на Raspberry Pi</p>
                    </div>
                `;
            })
            .catch(() => {
                showNotification('Ошибка выключения', 'error');
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
