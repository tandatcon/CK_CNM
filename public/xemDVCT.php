<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Đơn Hàng - Đi cùng tôi</title>
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

        /* Chờ xác nhận */
        .status-confirmed {
            background: #3b82f6;
            color: white;
        }

        /* Đã xác nhận */
        .status-in-progress {
            background: #f97316;
            color: white;
        }

        /* Đang thực hiện */
        .status-completed {
            background: #4ade80;
            color: white;
        }

        /* Đã hoàn tất */
        .status-cancelled {
            background: #ef4444;
            color: white;
        }

        /* Đã từ chối */
    </style>
</head>

<body class="bg-white text-gray-800">
    <?php include_once(__DIR__ . '/Assets/header.php'); ?>

    <div class="container mx-auto my-6 px-4">
        <h2 class="text-xl font-bold mb-4">Chi tiết đơn hàng</h2>
        <div id="orderDetails" class="space-y-4">
            <p class="text-sm text-gray-500">Đang tải chi tiết đơn hàng...</p>
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
        const urlParams = new URLSearchParams(window.location.search);
        const orderId = urlParams.get('id');
        console.log('Order ID:', orderId);

        if (!token) {
            console.log('No token found, showing login notification');
            showNotification('Vui lòng đăng nhập để tiếp tục.', 'danger', 'login.php');
            $('#orderDetails').html('<p class="text-sm text-gray-600">Vui lòng đăng nhập để xem chi tiết đơn hàng.</p>');
            return;
        }

        if (!orderId || isNaN(orderId)) {
            console.log('Invalid or missing order ID');
            showNotification('Mã đơn hàng không hợp lệ.', 'danger');
            $('#orderDetails').html('<p class="text-sm text-gray-600">Mã đơn hàng không hợp lệ.</p>');
            return;
        }

        console.log('Making AJAX request to xemDVCTAPI.php');
        $.ajax({
            url: 'http://localhost/WEB_ThueHoTroKhamBenh/api/xemDVCTAPI.php',
            method: 'GET',
            data: { token: token, id: orderId },
            dataType: 'json',
            success: function (data) {
                console.log('API Response:', data);
                let html = '';
                if (data.success && data.data) {
                    console.log('Rendering order details');
                    const item = data.data;
                    if (item.loai === 0) {
                        html = `
                    <div class="order-card bg-white rounded-lg shadow-md p-6 border border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Mã đơn: ${item.id || 'N/A'}</h3>
                            <span class="status-badge ${item.trangthai === 1 ? 'status-pending' :
                                item.trangthai === 2 ? 'status-confirmed' :
                                    item.trangthai === 3 ? 'status-in-progress' :
                                        item.trangthai === 4 ? 'status-completed' :
                                            'status-cancelled'
                            }">
                                ${item.trangthai === 1 ? 'Chờ xác nhận' :
                                item.trangthai === 2 ? 'Đã xác nhận' :
                                    item.trangthai === 3 ? 'Đang thực hiện' :
                                        item.trangthai === 4 ? 'Đã hoàn tất' :
                                            'Đã từ chối'
                            }
                            </span>
                        </div>
                        <div class="">
                            <p class="text-sm"><i class="fas fa-pen mr-2"></i><strong>Loại:</strong> ${item.loai === 0 ? '<b>Đặt cho bạn</b>' : item.loai === 1 ? '<b>Đặt hộ người khác</b>' : 'N/A'} </p><br>
                            <p class="text-sm"<i class="fas fa-comment mr-2"></i><strong>Trạng thái:</strong> ${
                                    item.trangthai === 1 ? '<b>Chờ xác nhận</b>' :
                                    item.trangthai === 2 ? '<b>Đã xác nhận</b>' :
                                    item.trangthai === 3 ? '<b>Đang thực hiện</b>' :
                                    item.trangthai === 4 ? '<b>Đã hoàn tất</b>' :
                                    item.trangthai === 0 ? `<b>Đã bị từ chối</b> <br> <br><b> <i class="fas fa-xmark mr-2"></i><span class="text-sm text-red-500">Lý do: ${item.lydo_tuchoi || 'N/A'}</span></b> ` :
                                    'Không xác định'
                            }
</p>
                            <br><hr>
                            <h5  align="center"><strong>THÔNG TIN DỊCH VỤ</strong> </h5><br>
                            <p class="text-sm"><i class="fas fa-hospital mr-2"></i><strong>Bệnh viện đặt dịch vụ:</strong> ${item.ten_benhvien || 'N/A'}</p> <br>
                            <p class="text-sm"><i class="fas fa-map-marker-alt mr-2"></i><strong>Địa điểm hẹn tại bệnh viện:</strong> ${item.diemhen || 'N/A'}</p><br>
                            <p class="text-sm"><i class="fas fa-calendar-alt mr-2"></i><strong>Ngày đặt lịch:</strong> ${item.ngayhen || 'N/A'} <i class="fas fa-clock mr-2"></i><strong>Giờ:</strong> ${item.giohen || 'N/A'}</p><br>
        
                            <hr><br>
                            <h5 align="center"><strong>THÔNG TIN NGƯỜI KHÁM</strong> </h5><br>
                            <p class="text-sm"><i class="fas fa-user mr-2"></i><strong>Tên người khám:</strong> ${item.name || 'N/A'}</p><br>
                            <p class="text-sm"><i class="fas fa-phone mr-2"></i><strong>SDT người khám:</strong> ${item.sdt || 'N/A'}</p><br>
                            <p class="text-sm"><i class="fas fa-birthday-cake mr-2"></i><strong>Năm sinh:</strong> ${item.namsinh || 'N/A'}</p><br>
                            <p class="text-sm"><i class="fas fa-venus-mars mr-2"></i><strong>Giới tính:</strong> ${item.gt === 0 ? 'Nam' : item.gt === 1 ? 'Nữ' : 'N/A'}</p>         <br>                  
                            <p class="text-sm"><i class="fas fa-stethoscope mr-2"></i><strong>Tình trạng:</strong> ${item.tinhtrang_nguoikham || 'N/A'}</p><br>
                            
                            <hr><br>
                            <h5 align="center"><strong>THÔNG TIN NHÂN VIÊN</strong> </h5><br>
                            <p class="text-sm"><i class="fas fa-user mr-2"></i><strong>Nhân viên thực hiện:</strong> ${item.id_nhanvien || 'N/A'}</p><br>
                            
                                    
                            <hr><br>
                            <h5 align="center"><strong>THÔNG TIN THANH TOÁN</strong> </h5><br>
                            <i class="fas fa-clock mr-2"></i><strong>Số giờ thực hiện:</strong> ${item.giodichvu || 'N/A'}</p><br>
                            <p class="text-sm"><i class="fas fa-wallet mr-2"></i><strong>Chi phí:</strong> ${item.tongchiphi ? parseInt(item.tongchiphi).toLocaleString('vi-VN') + ' VND' : 'N/A'}</p><br>
                            <hr><br>
                            
                        </div>
                        <div class="mt-4">
                            <a href="xemDV.php" class="inline-block bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                                <i class="fas fa-arrow-left mr-2"></i>Quay lại danh sách
                            </a>
                        </div>
                    </div>`;
                    } else {
                        html = `
                    <div class="order-card bg-white rounded-lg shadow-md p-6 border border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Mã đơn: ${item.id || 'N/A'}</h3>
                            <span class="status-badge ${item.trangthai === 1 ? 'status-pending' :
                                item.trangthai === 2 ? 'status-confirmed' :
                                    item.trangthai === 3 ? 'status-in-progress' :
                                        item.trangthai === 4 ? 'status-completed' :
                                            'status-cancelled'
                            }">
                                ${item.trangthai === 1 ? 'Chờ xác nhận' :
                                item.trangthai === 2 ? 'Đã xác nhận' :
                                    item.trangthai === 3 ? 'Đang thực hiện' :
                                        item.trangthai === 4 ? 'Đã hoàn tất' :
                                            'Đã từ chối'
                            }
                            </span>
                        </div>
                        <div class="">
                            <p class="text-sm"><i class="fas fa-pen mr-2"></i><strong>Loại:</strong> ${item.loai === 0 ? '<b>Đặt cho bạn</b>' : item.loai === 1 ? '<b>Đặt hộ người khác</b>' : 'N/A'} </p><br>
                            
                            <p class="text-sm"<i class="fas fa-comment mr-2"></i><strong>Trạng thái:</strong> ${
                                    item.trangthai === 1 ? '<b>Chờ xác nhận</b>' :
                                    item.trangthai === 2 ? '<b>Đã xác nhận</b>' :
                                    item.trangthai === 3 ? '<b>Đang thực hiện</b>' :
                                    item.trangthai === 4 ? '<b>Đã hoàn tất</b>' :
                                    item.trangthai === 0 ? `<b>Đã bị từ chối</b> <br> <br><b> <i class="fas fa-xmark mr-2"></i><span class="text-sm text-red-500">Lý do: ${item.lydo_tuchoi || 'N/A'}</span></b> ` :
                                    'Không xác định'
                            }
                            <br>

                            <br><hr><br>
                            <h5  align="center"><strong>THÔNG TIN DỊCH VỤ</strong> </h5><br>
                            <p class="text-sm"><i class="fas fa-hospital mr-2"></i><strong>Bệnh viện đặt dịch vụ:</strong> ${item.ten_benhvien || 'N/A'}</p> <br>
                            <p class="text-sm"><i class="fas fa-map-marker-alt mr-2"></i><strong>Địa điểm hẹn tại bệnh viện:</strong> ${item.diemhen || 'N/A'}</p><br>
                            <p class="text-sm"><i class="fas fa-calendar-alt mr-2"></i><strong>Ngày đặt lịch:</strong> ${item.ngayhen || 'N/A'} <i class="fas fa-clock mr-2"></i><strong>Giờ:</strong> ${item.giohen || 'N/A'}</p><br>
                            <hr><br>
                            <h5  align="center" ></i><strong>THÔNG TIN NGƯỜI ĐẶT HỘ</strong> </h5><br>
                            <p class="text-sm"><i class="fas fa-user mr-2"></i><strong>Họ và tên Người đặt hộ:</strong> ${item.name || 'N/A'}</p><br>
                            <p class="text-sm"><i class="fas fa-phone mr-2"></i><strong>SDT người đặt hộ:</strong> ${item.sdt || 'N/A'}</p><br>
                            <hr><br>
                            <h5 align="center"><strong>THÔNG TIN NGƯỜI KHÁM</strong> </h5><br>
                            <p class="text-sm"><i class="fas fa-user mr-2"></i><strong>Họ và tên Người khám:</strong> ${item.ten_ho || 'N/A'}</p><br>
                            <p class="text-sm"><i class="fas fa-birthday-cake mr-2"></i><strong>Năm sinh:</strong> ${item.namsinh || 'N/A'}</p><br>
                            <p class="text-sm"><i class="fas fa-venus-mars mr-2"></i><strong>Giới tính:</strong> ${item.gt === 0 ? 'Nam' : item.gt === 1 ? 'Nữ' : 'N/A'}</p>         <br>                  
                            <p class="text-sm"><i class="fas fa-phone mr-2"></i><strong>SDT người khám:</strong> ${item.sdt_ho || 'N/A'}</p><br>
                            <p class="text-sm"><i class="fas fa-stethoscope mr-2"></i><strong>Tình trạng:</strong> ${item.tinhtrang_nguoikham || 'N/A'}</p><br>
                            <hr><br>
                            <h5 align="center"><strong>THÔNG TIN NHÂN VIÊN</strong> </h5><br>
                            <p class="text-sm"><i class="fas fa-user mr-2"></i><strong>Nhân viên thực hiện:</strong> ${item.id_nhanvien || 'N/A'}</p><br>
                            
                                    
                            <hr><br>
                            <h5 align="center"><strong>THÔNG TIN THANH TOÁN</strong> </h5><br>
                            <i class="fas fa-clock mr-2"></i><strong>Số giờ thực hiện:</strong> ${item.giodichvu || 'N/A'}</p><br>
                            <p class="text-sm"><i class="fas fa-wallet mr-2"></i><strong>Chi phí:</strong> ${item.tongchiphi ? parseInt(item.tongchiphi).toLocaleString('vi-VN') + ' VND' : 'N/A'}</p><br>
                            <hr><br>
                            
                        </div>
                        <div class="mt-4">
                            <a href="xemDV.php" class="inline-block bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                                <i class="fas fa-arrow-left mr-2"></i>Quay lại danh sách
                            </a>
                        </div>
                    </div>`;
                    }
                } else {
                    console.log('No order details or API error:', data.message || 'No data');
                    html = `
                    <div class="text-center py-6">
                        <i class="fas fa-exclamation-circle text-3xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-600">${data.message || 'Không tìm thấy chi tiết đơn hàng.'}</p>
                    </div>`;
                    showNotification(data.message || 'Không tìm thấy chi tiết đơn hàng.', 'danger');
                }
                $('#orderDetails').html(html);
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
                        $('#orderDetails').html('<p class="text-sm text-gray-600">Phiên đăng nhập hết hạn. Đang chuyển hướng...</p>');
                        return;
                    }
                } catch (e) {
                    console.error('Failed to parse JSON:', xhr.responseText);
                }
                showNotification(message, 'error');
                $('#orderDetails').html('<p class="text-sm text-gray-600">Lỗi tải chi tiết đơn hàng. Vui lòng thử lại.</p>');
            }
        });
    });
</script>