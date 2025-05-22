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
<body class="bg-white text-gray-800" id="xemDV">
    <?php include_once(__DIR__ . '/Assets/header.php'); ?>

    <div class="container mx-auto my-6 px-4" id="xemDV">
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
    // Function to fetch and render orders
    function fetchOrders(apiEndpoint) {
        console.log('Fetching orders from', apiEndpoint);
        fetch(`http://localhost/WEB_ThueHoTroKhamBenh/api/${apiEndpoint}`, {
            method: 'GET',
            credentials: 'include', // Gửi cookie
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            console.log('API Response:', data);
            let html = '';
            if (data.success) {
                if (data.data && data.data.length > 0) {
                    console.log('Rendering', data.data.length, 'orders');
                    data.data.forEach(item => {
                        html += `
                            <div class="order-card bg-white rounded-lg shadow-md p-4 border border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between">
                                <div class="flex-1">
                                    <div class="flex justify-between items-center mb-2">
                                        <h3 class="text-base font-semibold">Mã đơn: ${item.id || 'N/A'}</h3>
                                        <span class="status-badge ${
                                            item.trangthai == 0 ? 'status-pending' :
                                            item.trangthai == 1 ? 'status-confirmed' :
                                            item.trangthai == 2 ? 'status-in-progress' :
                                            item.trangthai == 3 ? 'status-completed' :
                                            'status-cancelled'
                                        }">
                                            ${
                                                item.trangthai == 0 ? 'Chờ xác nhận' :
                                                item.trangthai == 1 ? 'Đã xác nhận' :
                                                item.trangthai == 2 ? 'Đang thực hiện' :
                                                item.trangthai == 3 ? 'Đã hoàn tất' :
                                                'Đã từ chối'
                                            }
                                        </span>
                                    </div>
                                    <p class="text-sm"><i class="fas fa-hospital mr-2"></i><strong>Loại:</strong> ${item.loai == 0 ? 'Đặt cho bạn' : item.loai == 1 ? 'Đặt hộ người khác' : 'N/A'}</p>
                                    <p class="text-sm"><i class="fas fa-hospital mr-2"></i><strong>Bệnh viện:</strong> ${item.ten_benhvien || 'N/A'}</p>
                                    <p class="text-sm"><i class="fas fa-map-marker-alt mr-2"></i><strong>Địa điểm:</strong> ${item.diemhen || 'N/A'}</p>
                                    <p class="text-sm"><i class="fas fa-calendar-alt mr-2"></i><strong>Ngày:</strong> ${item.ngayhen || 'N/A'} <strong>Giờ:</strong> ${item.giohen || 'N/A'}</p>
                                    <p class="text-sm"><i class="fas fa-wallet mr-2"></i><strong>Chi phí:</strong> ${item.tongchiphi ? parseInt(item.tongchiphi).toLocaleString('vi-VN') + ' VND' : 'N/A'}</p>
                                </div>
                                <div class="mt-3 md:mt-0 md:ml-4">
                                <a href="${item.trangthai == 3 ? 'xemDVHTCT.php' : 'xemDVCT.php'}?id=${item.id || ''}" 
                                class="inline-block bg-blue-500 text-white px-3 py-1.5 rounded text-sm hover:bg-blue-600">
                                    <i class="fas fa-eye mr-1"></i>Xem chi tiết
                                </a>
                                </div>
                            </div>`;
                    });
                } else {
                    console.log('No orders found');
                    html = `
                        <div class="text-center py-6">
                            <i class="fas fa-box-open text-3xl text-gray-400 mb-2"></i>
                            <p class="text-sm text-gray-600">Không có đơn đặt dịch vụ nào</p>
                        </div>`;
                }
            } else {
                // Xử lý lỗi từ API
                console.log('API Error:', data.message || 'No message provided');
                if (data.error_code === 'FORBIDDEN') {
                    const an = document.getElementById('xemDV');
                    showNotification('Không có quyền truy cập. Vui lòng kiểm tra lại tài khoản.', 'danger','../index.php');
                    
                    html = '<p class="text-sm text-gray-600">Không có quyền truy cập dữ liệu đơn hàng.</p>';
                } else if (data.error_code === 'INVALID_REFRESH_TOKEN') {
                    showNotification('Phiên đăng nhập hết hạn. Đang chuyển hướng...', 'danger', '/WEB_ThueHoTroKhamBenh/public/login.php');
                    html = '<p class="text-sm text-gray-600">Phiên đăng nhập hết hạn. Đang chuyển hướng...</p>';
                } else {
                    showNotification(data.message || 'Lỗi không xác định từ server.', 'danger');
                    html = '<p class="text-sm text-gray-600">Lỗi tải đơn hàng. Vui lòng thử lại.</p>';
                }
            }
            $('#orderList').html(html);
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            let message = 'Lỗi kết nối server. Vui lòng thử lại sau.';
            if (error.message.includes('Failed to parse JSON')) {
                message = 'Lỗi máy chủ không phản hồi đúng định dạng.';
            } else {
                try {
                    const response = error.response ? JSON.parse(error.response) : {};
                    message = response.message || message;
                    if (response.error_code === 'INVALID_REFRESH_TOKEN') {
                        showNotification(message, 'danger', '/WEB_ThueHoTroKhamBenh/public/login.php');
                        $('#orderList').html('<p class="text-sm text-gray-600">Phiên đăng nhập hết hạn. Đang chuyển hướng...</p>');
                        return;
                    }
                } catch (e) {
                    console.error('Failed to parse error response:', e);
                }
            }
            showNotification(message, 'danger');
            $('#orderList').html('<p class="text-sm text-gray-600">Lỗi tải đơn hàng. Vui lòng thử lại.</p>');
        });
    }

    // Kiểm tra trạng thái đăng nhập từ header
    window.addEventListener('userChecked', (event) => {
        const userData = event.detail;
        
        if (!userData) {
            showNotification('Vui lòng đăng nhập để tiếp tục.', 'danger', '/WEB_ThueHoTroKhamBenh/public/login.php');
            $('#orderList').html('<p class="text-sm text-gray-600">Vui lòng đăng nhập để xem đơn hàng.</p>');
            return;
        }
        
        // Nếu đã đăng nhập, tải danh sách đơn hàng
        fetchOrders('xemDVAPI.php');
    });

    // Handle filter change
    $('#orderFilter').on('change', function () {
        const filter = $(this).val();
        console.log('Filter changed to:', filter);
        if (!window.currentUser) {
            showNotification('Vui lòng đăng nhập để tiếp tục.', 'danger', '/WEB_ThueHoTroKhamBenh/public/login.php');
            return;
        }
        $('#orderList').html('<p class="text-sm text-gray-500">Đang tải danh sách đơn hàng...</p>');
        if (filter === 'all') {
            fetchOrders('xemDVAPI.php');
        } else if (filter === 'completed') {
            fetchOrders('xemDVHTAPI.php');
        }
    });
});
</script>