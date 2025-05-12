let redirectAfterClose = null; // biến toàn cục

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

    // Gán URL chuyển hướng
    redirectAfterClose = redirectUrl;
}

function closeNotification() {
    const modal = document.getElementById('notificationModal');
    modal.style.display = 'none';

    // Thực hiện chuyển hướng nếu có URL
    if (redirectAfterClose) {
        window.location.href = redirectAfterClose;
    }
}
// async function checkToken() {
//     const token = sessionStorage.getItem('token');
//     if (!token) {
//         showNotification('Phiên đăng nhập đã hết, vui lòng đăng nhập lại', 'danger', '/WEB_ThueHoTroKhamBenh/login.php');
//         return false;
//     }
// }