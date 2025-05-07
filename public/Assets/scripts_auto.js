function showNotification(message, type, redirectUrl = null) {
    const modal = document.getElementById('notificationModal');
    const content = document.getElementById('notificationContent');
    const icon = document.getElementById('notificationIcon');
    const messageElement = document.getElementById('notificationMessage');

    content.className = 'notification-content';
    if (type === 'success') {
        content.classList.add('success');
        icon.className = 'fas fa-check-circle';
    } else if (type === 'danger') {
        content.classList.add('danger');
        icon.className = 'fas fa-exclamation-circle';
    } else if (type === 'warning') {
        content.classList.add('warning');
        icon.className = 'fas fa-exclamation-triangle';
    }

    messageElement.textContent = message;
    modal.style.display = 'block';

    // Gán redirectUrl vào hàm closeNotification (lưu tạm)
    closeNotification.redirectUrl = redirectUrl;
}


function closeNotification() {
    const modal = document.getElementById('notificationModal');
    modal.style.display = 'none';

    // Chuyển trang tại đây nếu có URL
    if (closeNotification.redirectUrl) {
        window.location.href = closeNotification.redirectUrl;
    }
}
