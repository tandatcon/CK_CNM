function showNotification(message, type, redirectUrl = null) {
    console.log('showNotification called:', { message, type, redirectUrl });

    // Lấy các phần tử modal
    const modal = document.getElementById('notificationModal');
    const content = document.getElementById('notificationContent');
    const icon = document.getElementById('notificationIcon');
    const messageElement = document.getElementById('notificationMessage');

    // Reset class của content và thêm class theo type
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

    // Cập nhật nội dung thông báo
    messageElement.textContent = message;

    // Hiển thị modal
    modal.style.display = 'block';

    // Debug: Kiểm tra computed style của modal
    const computedStyle = window.getComputedStyle(modal);
    console.log('Modal computed styles:', {
        display: computedStyle.display,
        visibility: computedStyle.visibility,
        opacity: computedStyle.opacity,
        zIndex: computedStyle.zIndex
    });

    // Tự động đóng modal và chuyển hướng sau 3 giây
    setTimeout(() => {
        console.log('Auto-closing modal and redirecting to:', redirectUrl);
        closeNotification(redirectUrl);
    }, 1000);
}

function closeNotification(redirectUrl = null) {
    console.log('closeNotification called with redirectUrl:', redirectUrl);
    const modal = document.getElementById('notificationModal');
    if (modal) {
        modal.style.display = 'none';
        console.log('Modal closed');
    } else {
        console.error('Modal element not found for closing');
    }

    // Chuyển hướng nếu có redirectUrl
    if (redirectUrl) {
        console.log('Redirecting to:', redirectUrl);
        window.location.href = redirectUrl;
    }
}