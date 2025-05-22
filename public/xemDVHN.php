<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Đơn Đặt Hàng - Đi cùng tôi</title>
    <link rel="stylesheet" href="/WEB_ThueHoTroKhamBenh/public/Assets/styles.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/WEB_ThueHoTroKhamBenh/public/Assets/scripts.js?v=1"></script>
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

        select {
            appearance: none;
            background-image: url("data:image/svg+xml;utf8,<svg fill='black' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/><path d='M0 0h24v24H0z' fill='none'/></svg>");
            background-repeat: no-repeat;
            background-position-x: 100%;
            background-position-y: 5px;
        }
        #xemDV{
            /* height: 350px; */
            /* overflow-y: auto; */
            
        }
        .text-sm{
            margin-bottom: 10px;
        }
    </style>
</head>

<body class="bg-white text-gray-800" id="xemDV">
    <?php include_once(__DIR__ . '/Assets/header.php'); ?>
    <div class="container mx-auto my-6 px-4" id="xemDV">
        <h2 class="text-xl font-bold mb-4">Danh sách đơn hôm nay</h2>
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
    <script>
        function showNotification(message, type, redirectUrl = null) {
            console.log('showNotification:', { message, type, redirectUrl });
            const modal = $('#notificationModal');
            const content = $('#notificationContent');
            const icon = $('#notificationIcon');
            const messageEl = $('#notificationMessage');

            content.removeClass('success danger warning').addClass(type);
            messageEl.text(message);

            if (type === 'success') {
                icon.removeClass().addClass('fas fa-check-circle');
            } else if (type === 'danger') {
                icon.removeClass().addClass('fas fa-exclamation-circle');
            } else {
                icon.removeClass().addClass('fas fa-exclamation-triangle');
            }

            modal.fadeIn(300);
            if (redirectUrl) {
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 2000);
            }
        }

        function closeNotification() {
            $('#notificationModal').fadeOut(300);
        }

        function batDauDichVu(orderId, button) {
            console.log('batDauDichVu called with orderId:', orderId);
            const $button = $(button);
            $button.prop('disabled', true);
            fetch('http://localhost/WEB_ThueHoTroKhamBenh/api/batdauDVAPI.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id: orderId })
            })
                .then(response => response.json())
                .then(data => {
                    console.log('API Response:', data);
                    if (data.success) {
                        showNotification(data.message, 'success');
                        fetchOrders('xemDVHNAPI.php');
                    } else {
                        showNotification(data.message, 'danger');
                        $button.prop('disabled', false);
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    showNotification('Lỗi gọi API bắt đầu dịch vụ.', 'danger');
                    $button.prop('disabled', false);
                });
        }

        function ketThucDichVu(orderId, button) {
            console.log('ketThucDichVu called with orderId:', orderId);
            const $button = $(button);
            $button.prop('disabled', true);
            fetch('http://localhost/WEB_ThueHoTroKhamBenh/api/ketthucDVAPI.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id: orderId })
            })
                .then(response => response.json())
                .then(data => {
                    console.log('API Response:', data);
                    if (data.success) {
                        showNotification(data.message, 'success');
                        fetchOrders('xemDVHNAPI.php');
                    } else {
                        showNotification(data.message, 'danger');
                        $button.prop('disabled', false);
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    showNotification('Lỗi gọi API kết thúc dịch vụ.', 'danger');
                    $button.prop('disabled', false);
                });
        }

        // Quản lý đồng hồ thời gian thực hiện
        const timers = {};

        function startTimer(orderId, startTimeStr) {
            // Dừng timer cũ nếu có
            if (timers[orderId]) {
                clearInterval(timers[orderId]);
            }

            // Chuẩn hóa startTimeStr về dạng HH:mm:ss
            const parts = startTimeStr.split(':');
            const hours = parseInt(parts[0], 10);
            const minutes = parseInt(parts[1], 10);
            const seconds = parts.length === 3 ? parseInt(parts[2], 10) : 0;

            const now = new Date();
            const startTime = new Date(
                now.getFullYear(),
                now.getMonth(),
                now.getDate(),
                hours,
                minutes,
                seconds
            );

            // Bắt đầu đếm thời gian
            timers[orderId] = setInterval(() => {
                const currentTime = new Date();
                const diffMs = currentTime - startTime;

                // Nếu thời gian bị âm (lỗi do server), dừng
                if (diffMs < 0) return;

                const totalSeconds = Math.floor(diffMs / 1000);
                const h = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
                const m = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
                const s = String(totalSeconds % 60).padStart(2, '0');

                const timerElement = document.getElementById(`timer-${orderId}`);
                if (timerElement) {
                    timerElement.textContent = `${h}:${m}:${s}`;
                } else {
                    // Không còn phần tử => dừng đếm
                    clearInterval(timers[orderId]);
                    delete timers[orderId];
                }
            }, 1000);
        }


        function fetchOrders(apiEndpoint) {
            console.log('Fetching orders from', apiEndpoint);
            fetch(`http://localhost/WEB_ThueHoTroKhamBenh/api/${apiEndpoint}`, {
                method: 'GET',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            })
                .then(response => response.json())
                .then(data => {
                    console.log('API Response:', data);
                    const now = new Date();
                    const time = now.toTimeString().slice(0, 5);
                    let html = '';
                    if (data.success) {
                        if (data.data && data.data.length > 0) {
                            console.log('Rendering', data.data.length, 'orders');
                            data.data.forEach(item => {
                                console.log('giobatdau:', item.giobatdau, 'trangthai:', item.trangthai);
                                const [gioHienTai, phutHienTai] = time.split(':').map(Number);
                                const [gioHen, phutHen] = item.giohen.split(':').map(Number);
                                const currentMinutes = gioHienTai * 60 + phutHienTai;
                                const henMinutes = gioHen * 60 + phutHen;
                                const conLai = henMinutes - currentMinutes;

                                let trangThai = '';
                                if (conLai > 0) {
                                    trangThai = `Chưa tới giờ hẹn: còn ${conLai} phút`;
                                } else if (conLai < 0) {
                                    trangThai = `Đã quá giờ hẹn: trễ ${Math.abs(conLai)} phút`;
                                } else {
                                    trangThai = `Đúng giờ hẹn`;
                                }

                                html += `
                                <div class="order-card bg-white rounded-lg shadow-md p-4 border border-gray-200 flex flex-col">
                                    <div class="flex-1" style="margin:10px;">
                                        <div class="flex justify-between items-center mb-2">
                                            <h3 class="text-base font-semibold">Mã đơn: ${item.id || 'N/A'}</h3>
                                            ${item.giobatdau === '' ? `
                                                                <h3 class="text-base font-semibold"> Thời gian: 
                                                                    <span class="status-badge 
                                                                        ${conLai > 0 ? 'status-completed' :
                                            conLai < 0 ? 'status-cancelled' :
                                                'status-on-time'}">${trangThai}</span>
                                                                </h3>
                                                                ` : ''}

                                        </div>
                                        <h5 align="center"><strong>THÔNG TIN ĐƠN DỊCH VỤ</strong></h5><br>
                                        <p class="text-sm"><i class="fas fa-hospital mr-2"></i><strong>Loại:</strong> ${item.loai == 0 ? 'Cá nhân' : item.loai == 1 ? 'Đặt hộ' : 'N/A'}</p>
                                        <p class="text-sm"><i class="fas fa-hospital mr-2"></i><strong>Bệnh viện:</strong> ${item.ten_benhvien || 'N/A'}</p>
                                        <p class="text-sm"><i class="fas fa-map-marker-alt mr-2"></i><strong>Địa điểm:</strong> ${item.diemhen || 'N/A'}</p>
                                        <p class="text-sm"><i class="fas fa-calendar-alt mr-2"></i><strong>Ngày:</strong> ${item.ngayhen || 'N/A'} <strong>Giờ:</strong> ${item.giohen || 'N/A'}</p>
                                        <p class="text-sm"><i class="fas fa-user mr-2"></i><strong>Tên người khám:</strong> ${item.name || 'N/A'}</p>
                                        <p class="text-sm"><i class="fas fa-phone mr-2"></i><strong>SDT người khám:</strong> ${item.sdt || 'N/A'}</p>
                                        <p class="text-sm"><i class="fas fa-birthday-cake mr-2"></i><strong>Năm sinh:</strong> ${item.namsinh || 'N/A'}</p>
                                        <p class="text-sm"><i class="fas fa-venus-mars mr-2"></i><strong>Giới tính:</strong> ${item.gt == 0 ? 'Nam' : item.gt == 1 ? 'Nữ' : 'N/A'}</p>
                                        <p class="text-sm"><i class="fas fa-stethoscope mr-2"></i><strong>Tình trạng:</strong> ${item.tinhtrang_nguoikham || 'N/A'}</p>
                                        <div class="mt-3 flex flex-row justify-center space-x-2">
                                        <a href="xemDHCT.php?id=${item.id || ''}" class="inline-block bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                            <i class="fas fa-eye mr-1"></i>Xem chi tiết
                                        </a>
                                        </div>
                                        <br>
                                        <hr>
                                        <br>
                                        <h5 align="center"><strong>THÔNG TIN THỰC HIỆN</strong></h5><br>
                                        <p class="text-sm"><i class="fas fa-clock mr-2"></i><strong>Giờ bắt đầu:</strong> ${item.giobatdau || 'N/A'}</p>
                                        ${item.giobatdau && item.giobatdau !== '00:00:00' && parseInt(item.trangthai, 10) !== 3 ?
                                        `<p class="text-sm"><i class="fas fa-stopwatch mr-2"></i><strong>Thời gian thực hiện:</strong> <span id="timer-${item.id}" class="status-completed">00:00:00</span></p>` : ''}
                                        <p class="text-sm"><i class="fas fa-clock mr-2"></i><strong>Giờ kết thúc:</strong> ${item.gioketthuc || 'N/A'}</p>
                                        
                                        <p class="text-sm"><i class="fas fa-clock mr-2"></i><strong>Trạng thái:</strong> 
                                            ${
                                                item.trangthai == 0 ? 'Chờ xác nhận' :
                                                item.trangthai == 1 ? 'Đã xác nhận' :
                                                item.trangthai == 2 ? 'Đang thực hiện' :
                                                item.trangthai == 3 ? 'Đã hoàn tất' :
                                                'Đã từ chối'
                                            }</p>
                                            <p class="text-sm"><i class="fas fa-wallet mr-2"></i><strong>Tổng tiền dịch vụ:</strong> ${item.tongchiphi ? parseInt(item.tongchiphi).toLocaleString('vi-VN') + ' VND' : 'N/A'}</p>
                                    </div>
                                    <div class="mt-3 flex flex-row justify-center space-x-2">
                                        
                                        ${conLai <= 30 && item.giobatdau === '' ?
                                        `<button class="btnTHDV inline-block bg-yellow-500 text-white px-3 py-1 rounded text-sm hover:bg-yellow-600" onclick="if(confirm('Bạn có chắc muốn bắt đầu dịch vụ không?')) batDauDichVu('${item.id}', this)">
                                                <i class="fas fa-play mr-1"></i>Thực hiện dịch vụ
                                            </button>` :
                                        `<button class="inline-block bg-gray-400 text-white px-3 py-1 rounded text-sm" disabled>
                                                <i class="fas fa-play mr-1"></i>Thực hiện dịch vụ
                                            </button>`}
                                        ${item.giobatdau && item.giobatdau !== '' && item.gioketthuc == '' ?
                                        `<button class="btnKTDV inline-block bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600" onclick="if(confirm('Bạn có chắc muốn kết thúc dịch vụ không?')) ketThucDichVu('${item.id}', this)">
                                                <i class="fas fa-stop mr-1"></i>Kết thúc dịch vụ
                                            </button>` :
                                        `<button class="inline-block bg-gray-400 text-white px-3 py-1 rounded text-sm" disabled>
                                                <i class="fas fa-stop mr-1"></i>Kết thúc dịch vụ
                                            </button>`}
                                    </div>
                                </div>`;

                                // Khởi động đồng hồ nếu đơn hàng đang thực hiện
                                if (item.giobatdau && item.giobatdau !== '' && parseInt(item.trangthai, 10) !== 3) {
                                    startTimer(item.id, item.giobatdau);
                                }
                            });
                        } else {
                            console.log('No orders found');
                            html = `
                            <div class="text-center py-6">
                                <i class="fas fa-box-open text-3xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-600">Không có đơn đặt dịch vụ nào nè</p>
                            </div>`;
                        }

                    } else {
                        console.log('API Error:', data.message || 'No message provided');
                        if (data.error_code === 'FORBIDDEN') {
                            showNotification('Không có quyền truy cập. Vui lòng kiểm tra lại tài khoản.', 'danger', '../index.php');
                            html = '<p class="text-sm text-gray-600">Không có quyền truy cập dữ liệu đơn hàng.</p>';
                        } else if (data.error_code === 'INVALID_REFRESH_TOKEN') {
                            showNotification('Phiên đăng nhập hết hạn. Đang chuyển hướng...', 'danger', '/WEB_ThueHoTroKhamBenh/public/login.php');
                            html = '<p class="text-sm text-gray-600">Phiên đăng nhập hết hạn. Đang chuyển hướng...</p>';
                        } else if (data.error_code === 'NO-ORDER') {
                            html = '<p class="text-sm text-gray-600">Không có đơn hàng!</p>';
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

        window.addEventListener('userChecked', (event) => {
            const userData = event.detail;
            console.log('userChecked event received:', userData);
            if (!userData) {
                showNotification('Vui lòng đăng nhập để tiếp tục.', 'danger', '/WEB_ThueHoTroKhamBenh/public/login.php');
                $('#orderList').html('<p class="text-sm text-gray-600">Vui lòng đăng nhập để xem đơn hàng.</p>');
                return;
            }
            fetchOrders('xemDVHNAPI.php');
        });
    </script>
</body>

</html>