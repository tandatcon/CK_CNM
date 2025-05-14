<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Đơn Đặt Hàng - Đi cùng tôi</title>
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
        .notification-content.success { background: #4ade80; color: white; }
        .notification-content.danger { background: #ef4444; color: white; }
        .notification-content.warning { background: #facc15; color: black; }
        .notification-content i { margin-right: 8px; }
        .notification-content p { margin: 10px 0; }
        .notification-content button {
            padding: 8px 16px;
            background: #fff;
            color: #333;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .notification-content button:hover { background: #e5e7eb; }
        .order-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .order-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .status-pending { background: #facc15; color: black; } /* Chờ xác nhận */
        .status-confirmed { background: #3b82f6; color: white; } /* Đã xác nhận */
        .status-in-progress { background: #f97316; color: white; } /* Đang thực hiện */
        .status-completed { background: #4ade80; color: white; } /* Đã hoàn tất */
        .status-cancelled { background: #ef4444; color: white; } /* Đã từ chối */
        select {
            appearance: none;
            background-image: url("data:image/svg+xml;utf8,<svg fill='black' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/><path d='M0 0h24v24H0z' fill='none'/></svg>");
            background-repeat: no-repeat;
            background-position-x: 100%;
            background-position-y: 5px;
        }
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
<script>
$(document).ready(function () {
    const token = sessionStorage.getItem('token');
    console.log('Token:', token);
    if (!token) {
        console.log('No token found, showing login notification');
        showNotification('Vui lòng đăng nhập để tiếp tục.', 'danger');
        $('#orderList').html('<p class="text-sm text-gray-600">Vui lòng đăng nhập để xem đơn hàng.</p>');
        return;
    }

    // Function to fetch and render orders
    function fetchOrders(apiEndpoint) {
        console.log('Making AJAX request to', apiEndpoint);
        $.ajax({
            url: `http://localhost/WEB_ThueHoTroKhamBenh/api/${apiEndpoint}`,
            method: 'GET',
            data: { token: token },
            dataType: 'json',
            success: function (data) {
                console.log('API Response:', data);
                let html = '';
                if (data.success && data.data && data.data.length > 0) {
                    console.log('Rendering', data.data.length, 'orders');
                    data.data.forEach(item => {
                        
                        html += `
                            <div class="order-card bg-white rounded-lg shadow-md p-4 border border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between">
                                <div class="flex-1">
                                    <div class="flex justify-between items-center mb-2">
                                        <h3 class="text-base font-semibold">Mã đơn: ${item.id || 'N/A'}</h3>
                                        <span class="status-badge ${
                                            item.trangthai === 1 ? 'status-pending' :
                                            item.trangthai === 2 ? 'status-confirmed' :
                                            item.trangthai === 3 ? 'status-in-progress' :
                                            item.trangthai === 4 ? 'status-completed' :
                                            'status-cancelled'
                                        }">
                                            ${
                                                item.trangthai === 1 ? 'Chờ xác nhận' :
                                                item.trangthai === 2 ? 'Đã xác nhận' :
                                                item.trangthai === 3 ? 'Đang thực hiện' :
                                                item.trangthai === 4 ? 'Đã hoàn tất' :
                                                'Đã từ chối'
                                            }
                                        </span>
                                    </div>
                                    <p class="text-sm"><i class="fas fa-hospital mr-2"></i><strong>Loại:</strong> ${item.loai === 0 ? 'Đặt cho bạn' : item.loai === 1 ? 'Đặt hộ người khác': 'N/A'} </p>
                                    <p class="text-sm"><i class="fas fa-hospital mr-2"></i><strong>Bệnh viện:</strong> ${item.ten_benhvien || 'N/A'}</p>
                                    <p class="text-sm"><i class="fas fa-map-marker-alt mr-2"></i><strong>Địa điểm:</strong> ${item.diemhen || 'N/A'}</p>
                                    <p class="text-sm"><i class="fas fa-calendar-alt mr-2"></i><strong>Ngày:</strong> ${item.ngayhen || 'N/A'} <strong>Giờ:</strong> ${item.giohen || 'N/A'}</p>
                                    <p class="text-sm"><i class="fas fa-wallet mr-2"></i><strong>Chi phí:</strong> ${item.tongchiphi ? parseInt(item.tongchiphi).toLocaleString('vi-VN') + ' VND' : 'N/A'}</p>
                                </div>
                                <div class="mt-3 md:mt-0 md:ml-4">
                                    <a href="xemDVCT.php?id=${item.id || ''}" class="inline-block bg-blue-500 text-white px-3 py-1.5 rounded text-sm hover:bg-blue-600">
                                        <i class="fas fa-eye mr-1"></i>Xem chi tiết
                                    </a>
                                </div>
                            </div>`;
                    }
                                    );
                } else {
                    console.log('No orders or API error:', data.message || 'No data');
                    html = `
                        <div class="text-center py-6">
                            <i class="fas fa-box-open text-3xl text-gray-400 mb-2"></i>
                            <p class="text-sm text-gray-600">${data.message || 'Không có đơn đặt dịch vụ nào'}</p>
                        </div>`;
                }
                $('#orderList').html(html);
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error, xhr.responseText);
                let message = 'Lỗi kết nối server. Vui lòng thử lại sau.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    console.log('Error Response:', response);
                    message = response.message || message;
                    if (response.error_code === 'TOKEN_EXPIRED') {
                        console.log('Token expired, clearing sessionStorage');
                        sessionStorage.removeItem('token');
                        showNotification(message, 'danger', 'login.php');
                        $('#orderList').html('<p class="text-sm text-gray-600">Phiên đăng nhập hết hạn. Đang chuyển hướng...</p>');
                        return;
                    }
                } catch (e) {
                    console.error('Failed to parse JSON:', xhr.responseText);
                }
                showNotification(message, 'danger','../index.php');
                $('#orderList').html('<p class="text-sm text-gray-600">Lỗi tải đơn hàng. Vui lòng thử lại.</p>');
            }
        });
    }

    // Initial load: fetch all orders
    fetchOrders('xemDVAPI.php');

    // Handle filter change
    $('#orderFilter').on('change', function () {
        const filter = $(this).val();
        console.log('Filter changed to:', filter);
        $('#orderList').html('<p class="text-sm text-gray-500">Đang tải danh sách đơn hàng...</p>');
        if (filter === 'all') {
            fetchOrders('xemDVAPI.php');
        } else if (filter === 'completed') {
            fetchOrders('xemDVHTAPI.php');
        }
    });
});
</script>