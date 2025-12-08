// User management functions

function showAddUserModal() {
    document.getElementById('add-user-modal').classList.add('active');
}

function closeModal() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.classList.remove('active');
    });
    document.querySelectorAll('form').forEach(form => form.reset());
}

document.getElementById('add-user-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const password = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    if (password !== confirmPassword) {
        showNotification('Пароли не совпадают', 'error');
        return;
    }
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    apiCall('add_user', data)
        .then(result => {
            if (result.success) {
                showNotification('Пользователь создан', 'success');
                closeModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(result.error || 'Ошибка создания пользователя', 'error');
            }
        })
        .catch(() => {
            showNotification('Ошибка создания пользователя', 'error');
        });
});

function blockUser(id) {
    if (confirm('Заблокировать этого пользователя?')) {
        apiCall('block_user', { id })
            .then(data => {
                if (data.success) {
                    showNotification('Пользователь заблокирован', 'success');
                    location.reload();
                }
            })
            .catch(() => {
                showNotification('Ошибка блокировки', 'error');
            });
    }
}

function allowUser(id) {
    apiCall('allow_user', { id })
        .then(data => {
            if (data.success) {
                showNotification('Пользователь разблокирован', 'success');
                location.reload();
            }
        })
        .catch(() => {
            showNotification('Ошибка разблокировки', 'error');
        });
}

function resetPassword(id) {
    const newPassword = prompt('Введите новый пароль (минимум 6 символов):');
    
    if (newPassword && newPassword.length >= 6) {
        apiCall('reset_user_password', { id, password: newPassword })
            .then(data => {
                if (data.success) {
                    showNotification('Пароль изменен', 'success');
                } else {
                    showNotification('Ошибка изменения пароля', 'error');
                }
            })
            .catch(() => {
                showNotification('Ошибка изменения пароля', 'error');
            });
    } else if (newPassword !== null) {
        showNotification('Пароль должен содержать минимум 6 символов', 'error');
    }
}

function deleteUser(id) {
    if (confirm('Удалить этого пользователя? Это действие нельзя отменить.')) {
        apiCall('delete_user', { id })
            .then(data => {
                if (data.success) {
                    showNotification('Пользователь удален', 'success');
                    location.reload();
                }
            })
            .catch(() => {
                showNotification('Ошибка удаления', 'error');
            });
    }
}
