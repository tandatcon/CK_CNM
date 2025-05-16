<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Đơn Đặt Hàng - Đi cùng tôi</title>
    <link rel="stylesheet" href="Assets/styles.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="Assets/scripts.js?v=1"></script>
    <style>
        
        
    </style>
</head>
<body class="bg-white text-gray-800">
    <?php include_once(__DIR__ . '/Assets/header.php'); ?>

    <div class="container mx-auto my-6 px-4">
        <h2 class="text-xl font-bold mb-4">Danh sách đơn dịch vụ</h2>
        <div class="mb-4">
            <label for="orderFilter" class="text-sm font-medium mr-2">Hiển thị:</label>
            <select id="orderFilter" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="all" selected>Đơn dịch vụ đã đặt</option>
                <option value="completed">Đơn dịch vụ đã hoàn tất</option>
            </select>
        </div>
        <div id="orderList" class="space-y-4">
            <p class="text-sm text-gray-500">Đang tải danh sách đơn hàng...</p>
        </div>
    </div>

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
