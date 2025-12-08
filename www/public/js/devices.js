// Device management functions

function blockDevice(id) {
    if (confirm('Заблокировать это устройство?')) {
        apiCall('block_device', { id })
            .then(data => {
                if (data.success) {
                    showNotification('Устройство заблокировано', 'success');
                    location.reload();
                }
            })
            .catch(error => {
                showNotification('Ошибка блокировки', 'error');
            });
    }
}

function allowDevice(id) {
    apiCall('allow_device', { id })
        .then(data => {
            if (data.success) {
                showNotification('Устройство разрешено', 'success');
                location.reload();
            }
        })
        .catch(error => {
            showNotification('Ошибка разрешения', 'error');
        });
}

function updateDeviceName(id, name) {
    apiCall('update_device_name', { id, name })
        .then(data => {
            if (data.success) {
                showNotification('Имя обновлено', 'success');
            }
        })
        .catch(error => {
            showNotification('Ошибка обновления', 'error');
        });
}
