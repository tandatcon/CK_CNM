<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Đi cùng tôi</title>
    <link rel="stylesheet" href="Assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="Assets/scripts.js?v=1"></script>
    <style>
        .notification-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000;
        }

        .notification-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 400px;
            width: 90%;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .notification-content.success {
            background: #4ade80;
            color: white;
        }

        .notification-content.danger {
            background: #ef4444;
            color: white;
        }

        .notification-content.warning {
            background: #facc15;
            color: black;
        }

        .notification-content i {
            margin-right: 8px;
        }

        .notification-content p {
            margin: 10px 0;
        }

        .notification-content button {
            padding: 8px 16px;
            background: #fff;
            color: #333;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .notification-content button:hover {
            background: #e5e7eb;
        }
    </style>
</head>
<body class="bg-white text-gray-800">
    <?php include_once(__DIR__ . '/Assets/header.php'); ?>
    
    <div id="userInfo" class="text-center my-4"></div>
    <div class="notification-modal" id="notificationModal">
        <div class="notification-content" id="notificationContent">
            <i id="notificationIcon"></i>
            <p id="notificationMessage"></p>
            <button onclick="closeNotification()">Đóng</button>
        </div>
    </div>
    <?php include_once(__DIR__ . '/Assets/footer.php'); ?>
    
</body>
</html>
<script>
    
    $(document).ready(function () {
    const token = sessionStorage.getItem('token');
    console.log('Token:', token); // Debug token
    if (!token) {
        showNotification('Vui lòng đăng nhập để tiếp tục.', 'warning');
        return;
    }

    $.ajax({
        url: 'http://localhost/WEB_ThueHoTroKhamBenh/api/xemDVAPI.php',
        method: 'GET',
        data: { token: token },
        dataType: 'json',
        success: function (data) {
            if (data.success) {
                $('#userInfo').html(`SĐT: ${data.data.sdt}<br>Tên: ${data.data.name}`);
            } else {
                showNotification(data.message || 'Lỗi không xác định.', 'error');
            }
        },
        error: function (xhr, status, error) {
    let message = 'Lỗi kết nối server. Vui lòng thử lại sau.';
    
    try {
        const response = JSON.parse(xhr.responseText);
        message = response.message || message;

        // Nếu token hết hạn, xóa token & chuyển trang
        if (response.error_code === 'TOKEN_EXPIRED') {
            sessionStorage.removeItem('token');
            showNotification(message, 'danger', 'login.php');
            return;
            //setTimeout(() => window.location.href = '/login.html', 200000);
        }

    } catch (e) {
        console.error("Không thể parse JSON từ server:", xhr.responseText);
    }

    showNotification(message, 'error');
    console.error('Lỗi kết nối server:', xhr.status, error, xhr.responseText);
}
    });

    
});
</script>