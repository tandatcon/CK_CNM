<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Xuất - Đi cùng tôi</title>
    <link rel="stylesheet" href="Assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="Assets/scripts.js?v=2"></script>
    <style>
        .notification-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; }
        .notification-content { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); max-width: 400px; width: 90%; padding: 20px; border-radius: 8px; text-align: center; }
        .notification-content.success { background: #4ade80; color: white; }
        .notification-content.error { background: #ef4444; color: white; }
        .notification-content.warning { background: #facc15; color: black; }
        .notification-content i { margin-right: 8px; }
        .notification-content p { margin: 10px 0; }
        .notification-content button { padding: 8px 16px; background: #fff; color: #333; border: none; border-radius: 4px; cursor: pointer; }
        .notification-content button:hover { background: #e5e7eb; }
    </style>
</head>
<body>
    <div class="notification-modal" id="notificationModal">
        <div class="notification-content" id="notificationContent">
            <i id="notificationIcon"></i>
            <p id="notificationMessage"></p>
            <button onclick="closeNotification()">Đóng</button>
        </div>
    </div>
    <script>
        localStorage.removeItem('token');
        localStorage.removeItem('full_name');
        showNotification('Đăng xuất thành công!', 'success', '../index.php');
    </script>
</body>
</html>