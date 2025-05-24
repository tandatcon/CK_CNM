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

        .status-pending {
            background: #facc15;
            color: black;
        }

        .status-confirmed {
            background: #3b82f6;
            color: white;
        }

        .status-in-progress {
            background: #f97316;
            color: white;
        }

        .status-completed {
            background: #4ade80;
            color: white;
        }

        .status-cancelled {
            background: #ef4444;
            color: white;
        }

        .loading-spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">
    <?php include_once(__DIR__ . '/Assets/header.php'); ?>

    <div class="container mx-auto my-6 px-4">
        <h2 class="text-2xl font-bold mb-6 text-center">Phân công nhân viên cho các đơn dịch vụ</h2>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold">Danh sách đơn chưa phân công</h3>
                    <p class="text-sm text-gray-500">Các đơn dịch vụ trong 3 ngày tới chưa được phân công</p>
                </div>
                <button id="xepLichBtn"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-calendar-check"></i>
                    <span>Xếp lịch tự động</span>
                </button>
            </div>
        </div>

        <b><div id="orderSummary" class="mb-4 text-right text-sm text-gray-700 hidden" ></div> </b>

        <div id="orderList" class="space-y-4 w-[80%] mx-auto">
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500 mb-4">
                </div>
                <p class="text-gray-600">Đang tải danh sách đơn dịch vụ...</p>
            </div>
        </div>

        <div id="resultList" class="space-y-6 mt-8 w-[80%] hidden mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-4">Kết quả phân công</h3>
                <div id="phanCongThanhCong" class="space-y-4"></div>
                <div id="phanCongThatBai" class="space-y-4 mt-6"></div>
            </div>
        </div>

        <div class="notification-modal" id="notificationModal">
            <div class="notification-content" id="notificationContent">
                <i id="notificationIcon"></i>
                <p id="notificationMessage"></p>
                <button onclick="closeNotification()">Đóng</button>
            </div>
        </div>
    </div>

    <?php include_once(__DIR__ . '/Assets/footer.php'); ?>

    <script>
        $(document).ready(function () {
            function getCookie(name) {
                let cookieArr = document.cookie.split(";");
                for (let i = 0; i < cookieArr.length; i++) {
                    let cookie = cookieArr[i].trim();
                    if (cookie.startsWith(name + "=")) {
                        return cookie.substring(name.length + 1);
                    }
                }
            return null;
        }
            // Hàm gọi API đơn giản (đã có xác thực trong header)
            const csrfToken = getCookie('csrf_token'); // JS đọc cookie
            async function callApi(url, method = 'GET', body = null) {
                const options = {
                    method: method,
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json','X-CSRF-Token': csrfToken
                    }
                };

                if (body) {
                    options.body = JSON.stringify(body);
                }

                try {
                    const response = await fetch(url, options);
                    const data = await response.json();

                    if (!response.ok) {
                        // Kiểm tra nếu hàm showNotification tồn tại trước khi gọi
                        if (typeof showNotification === 'function') {
                            showNotification(data.message || 'Lỗi từ máy chủ', 'danger');
                        } else {
                            console.error('Lỗi từ máy chủ:', data.message);
                        }
                        throw new Error(data.message || 'Lỗi từ máy chủ');
                    }

                    return data;
                } catch (error) {
                    console.error('API call failed:', error);
                    if (typeof showNotification === 'function') {
                        showNotification(error.message || 'Lỗi kết nối đến máy chủ. Vui lòng thử lại sau.', 'danger');
                    } else {
                        console.error('Lỗi:', error.message || 'Lỗi kết nối đến máy chủ');
                    }
                    return null;
                }
            }

            // Hàm tải danh sách đơn chưa phân công
            async function fetchUnassignedOrders() {
                $('#orderList').html(`
        <div class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500 mb-4"></div>
            <p class="text-gray-600">Đang tải danh sách đơn dịch vụ...</p>
        </div>
    `);
                $('#orderSummary').addClass('hidden').text('');

                const data = await callApi('http://localhost/WEB_ThueHoTroKhamBenh/api/dhCPC.php');

                if (!data) return;

                let html = '';
                let count = 0;

                if (data.success && data.data && data.data.lich_chua_pc) {
                    const unassignedOrders = data.data.lich_chua_pc;
                    count = unassignedOrders.length;

                    if (count > 0) {
                        // Hiển thị tổng số đơn
                        $('#orderSummary').removeClass('hidden').text(`Tổng số đơn chưa phân công: ${count}`);

                        unassignedOrders.forEach(item => {
                            html += `
                    <div class="order-card bg-white rounded-lg shadow-md p-4 border border-gray-200">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-base font-semibold">Mã đơn: ${item.id_don || 'N/A'}</h3>
                            <span class="status-badge status-pending">Chưa phân công</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <p class="text-sm"><i class="fas fa-hospital mr-2"></i><strong>Bệnh viện ID:</strong> ${item.id_benhvien || 'N/A'}</p>
                            <p class="text-sm"><i class="fas fa-calendar-alt mr-2"></i><strong>Ngày hẹn:</strong> ${item.ngayhen || 'N/A'} <strong>Giờ hẹn:</strong> ${item.giohen || 'N/A'}</p>
                                    <a href="xemCTCPC.php?id=${item.id_don || ''}" class="inline-block bg-blue-500 text-white px-3 py-1.5 rounded text-sm hover:bg-blue-600 w-[30%]">
                                        <i class="fas fa-eye mr-1"></i>Xem chi tiết
                                    </a>

                        </div>
                        
                    </div>`;
                        });
                    } else {
                        html = `
                <div class="text-center py-8 bg-white rounded-lg shadow">
                    <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600 font-medium">Không có đơn dịch vụ nào chưa phân công</p>
                    <p class="text-sm text-gray-500 mt-2">Tất cả các đơn trong 3 ngày tới đã được phân công</p>
                </div>`;
                    }
                } else if (data.error_code === 'FORBIDDEN') {
                    if (typeof showNotification === 'function') {
                        showNotification('Bạn không có quyền truy cập tính năng này.', 'danger');
                    }
                    html = `
                        <div class="text-center py-8 bg-white rounded-lg shadow">
                            <i class="fas fa-ban text-4xl text-red-300 mb-4"></i>
                            <p class="text-gray-600 font-medium">Không có quyền truy cập</p>
                            <p class="text-sm text-gray-500 mt-2">Vui lòng liên hệ quản trị hệ thống</p>
                        </div>`;
                } else {
                    if (typeof showNotification === 'function') {
                        showNotification(data.message || 'Lỗi khi tải danh sách đơn hàng', 'danger');
                    }
                    html = `
                        <div class="text-center py-8 bg-white rounded-lg shadow">
                            <i class="fas fa-exclamation-triangle text-4xl text-yellow-300 mb-4"></i>
                            <p class="text-gray-600 font-medium">Không thể tải danh sách đơn hàng</p>
                            <p class="text-sm text-gray-500 mt-2">${data.message || 'Lỗi không xác định'}</p>
                        </div>`;
                }

                $('#orderList').html(html);
            }

            // Hàm xếp lịch tự động
            async function autoSchedule() {
                const btn = $('#xepLichBtn');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Đang xếp lịch...');

                const data = await callApi('http://localhost/WEB_ThueHoTroKhamBenh/api/phancongDHAPI.php', 'POST');

                if (!data) {
                    btn.prop('disabled', false).html('<i class="fas fa-calendar-check mr-2"></i> Xếp lịch tự động');
                    return;
                }

                $('#resultList').removeClass('hidden');
                let successHtml = '';
                let failHtml = '';

                if (data.success && data.data) {
                    // Hiển thị các đơn đã phân công
                    if (data.data.lich_phan_cong && data.data.lich_phan_cong.length > 0) {
                        successHtml = '<h4 class="text-lg font-semibold text-green-600 mb-4"><i class="fas fa-check-circle mr-2"></i> Các đơn đã phân công thành công</h4>';

                        data.data.lich_phan_cong.forEach(item => {
                            successHtml += `
                                <div class="order-card bg-white rounded-lg shadow-md p-4 border border-green-100">
                                    <div class="flex justify-between items-center mb-2">
                                        <h3 class="text-base font-semibold">Mã đơn: ${item.id_don}</h3>
                                        <span class="status-badge status-confirmed">Đã phân công</span>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <p class="text-sm"><i class="fas fa-user mr-2"></i><strong>Nhân viên:</strong> ${item.ten_nhanvien} (ID: ${item.id_nhanvien})</p>
                                        <p class="text-sm"><i class="fas fa-hospital mr-2"></i><strong>Bệnh viện ID:</strong> ${item.id_benhvien}</p>
                                        <p class="text-sm"><i class="fas fa-calendar-alt mr-2"></i><strong>Ngày hẹn:</strong> ${item.ngayhen}</p>
                                        <p class="text-sm"><i class="fas fa-clock mr-2"></i><strong>Thời gian:</strong> ${item.giobatdau} - ${item.gioketthuc}</p>
                                    </div>
                                </div>`;
                        });
                    } else {
                        successHtml = '<p class="text-gray-500">Không có đơn nào được phân công</p>';
                    }

                    // Hiển thị các đơn không phân công
                    if (data.data.don_khong_phan_cong && data.data.don_khong_phan_cong.length > 0) {
                        failHtml = '<h4 class="text-lg font-semibold text-red-600 mb-4"><i class="fas fa-exclamation-triangle mr-2"></i> Các đơn không thể phân công</h4>';

                        data.data.don_khong_phan_cong.forEach(item => {
                            failHtml += `
                                <div class="order-card bg-white rounded-lg shadow-md p-4 border border-red-100">
                                    <div class="flex justify-between items-center mb-2">
                                        <h3 class="text-base font-semibold">Mã đơn: ${item.id_don}</h3>
                                        <span class="status-badge status-pending">Chưa phân công</span>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <p class="text-sm"><i class="fas fa-hospital mr-2"></i><strong>Bệnh viện ID:</strong> ${item.id_benhvien}</p>
                                        <p class="text-sm"><i class="fas fa-calendar-alt mr-2"></i><strong>Ngày hẹn:</strong> ${item.ngayhen}</p>
                                        <p class="text-sm"><i class="fas fa-clock mr-2"></i><strong>Thời gian:</strong> ${item.giobatdau} - ${item.gioketthuc}</p>
                                        <p class="text-sm text-red-500"><i class="fas fa-info-circle mr-2"></i>Tất cả nhân viên điều bận</p>
                                    </div>
                                </div>`;
                        });
                    } else if (data.data.lich_phan_cong && data.data.lich_phan_cong.length > 0) {
                        failHtml = '<p class="text-green-500"><i class="fas fa-check mr-2"></i>Tất cả các đơn đã được phân công thành công</p>';
                    }

                    if (typeof showNotification === 'function') {
                        showNotification('Xếp lịch tự động hoàn tất!', 'success');
                    }
                } else {
                    if (typeof showNotification === 'function') {
                        showNotification(data.message || 'Lỗi khi xếp lịch tự động', 'danger');
                    }
                }

                $('#phanCongThanhCong').html(successHtml);
                $('#phanCongThatBai').html(failHtml);
                btn.prop('disabled', false).html('<i class="fas fa-calendar-check mr-2"></i> Xếp lịch tự động');

                // Tải lại danh sách đơn chưa phân công
                fetchUnassignedOrders();
            }

            // Gắn sự kiện cho nút xếp lịch
            $('#xepLichBtn').on('click', autoSchedule);

            // Tải dữ liệu ban đầu
            fetchUnassignedOrders();
        });
    </script>
</body>

</html>