<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đi cùng tôi</title>
    <link rel="stylesheet" href="../Assets/styles.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../Assets/scripts.js"></script>
    <style>
        
        .ct {
            background-image: url('/WEB_ThueHoTroKhamBenh/IMG/cauhoi.jpg');
            height: 950px;
            width: 100%;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            background-size: cover;
            display: flex;
            justify-content: center;
        }
        .br {
            width: 80%;
            margin-top: 14px;
            background-color: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            height: 900px;
            background-position: center;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .header { position: sticky; top: 0; z-index: 50; background-color: white; }
                .login-container { max-width: 400px; margin: 20px auto; padding: 20px; background: #f7f7f7; border-radius: 8px; }
                .form-group { margin-bottom: 15px; }
                .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
                .form-group input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
                .error { color: red; font-size: 0.875rem; margin-top: 5px; display: none; }
                .error.visible { display: block; }
                .password-container { position: relative; }
                .password-container i { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; }
                button { width: 100%; padding: 10px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; }
                button:hover { background: #2563eb; }
                .links { text-align: center; margin-top: 10px; }
                .links a { color: #3b82f6; text-decoration: none; }
                .links a:hover { text-decoration: underline; }
                .notification-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
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
<body class="bg-white text-gray-800">
    <?php
    include("../Assets/header.php");
    ?>
    <div class="ct">
        <div class="br p-6 text-white">
        <div class="form mb-4">
            <label><input type="radio" name="datdv" value="ban" checked > Đặt cho bạn</label>
            <label><input type="radio" name="datdv" value="nguoi_khac"> Đặt cho người khác</label>
            
        </div>
            <!-- Đây là nơi load form -->
            <div id="formContainer">
            <h2 class="text-xl font-bold mb-2">Thông tin đặt cho bạn</h2>
            <label for="full_name" class="block text-lg font-medium">Họ và Tên:</label>
            <input type="text" id="full_name" placeholder="Họ tên của bạn" disabled class="p-2 rounded w-full text-black mb-2" >
            <label for="phone" class="block text-lg font-medium">Số điện thoại:</label>
            <input type="text" id="phone" placeholder="Số điện thoại" disabled class="p-2 rounded w-full text-black mb-2">
            <section class="booking-section" style="display: flex; gap: 2rem; padding: 2rem;">
                <!-- Bên trái: Form đặt lịch -->
                <div class="booking-form-container" style="flex: 1;">
                    <form id="bookingForm" onsubmit="return handleBooking(event)">
                        <label for="hospital">Chọn bệnh viện:</label>
                        <select id="hospital" name="hospital" required>
                            <option value="">-- Chọn bệnh viện --</option>
                        </select>
                        <br><br>
                        <label for="date">Chọn ngày khám:</label>
                        <input type="date" id="date" name="date" required>
                        <br><br>
                        <label for="time">Chọn giờ hẹn:</label>
                        <input type="time" id="time" name="time" required>
                        <br><br>
                        <label for="condition">Tình trạng bệnh nhân:</label><br>
                        <textarea id="condition" name="condition" rows="4" cols="50" placeholder="Mô tả tình trạng sức khỏe của bệnh nhân..." required></textarea>
                        <br><br>
                        <button type="submit">Đặt lịch</button>
                    </form>
                </div>
                <!-- Bên phải: Gợi ý nội dung thêm -->
                <div class="booking-info-right" style="flex: 1; background-color: #f7f7f7; padding: 2rem; border-radius: 10px;">
                    hinhanh
                </div>
            </section>
        </div>
    </div>
    </div>
    <div class="notification-modal" id="notificationModal">
        <div class="notification-content" id="notificationContent">
            <i id="notificationIcon"></i>
            <p id="notificationMessage"></p>
            <button onclick="closeNotification()">Đóng</button>
        </div>
    </div>
    <?php
        include("../Assets/footer.php");
    ?>
</body>
</html>
<script>
    
    $(document).ready(function () {
    // Kiểm tra đăng nhập ngay khi trang tải
    if (!checkLogin()) {
        return; // Dừng thực thi các hàm khác nếu chưa đăng nhập
    }

    

    // Kiểm tra đăng nhập
    function checkLogin() {
        const token = localStorage.getItem('token');
        const role = localStorage.getItem('role');
        if (!token || role !== '0') {
            showNotification('Vui lòng đăng nhập với vai trò khách hàng để đặt dịch vụ', 'error', 'login.php');
            return false;
        }
        return true;
    }

    // Load danh sách bệnh viện
    function loadHospitals() {
        $.ajax({
            url: 'http://localhost/WEB_ThueHoTroKhamBenh/api/get_hospitals.php',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    const hospitalSelect = $('#hospital');
                    hospitalSelect.empty();
                    hospitalSelect.append('<option value="">-- Chọn bệnh viện --</option>');
                    data.data.forEach(hospital => {
                        hospitalSelect.append(`<option value="${hospital.id}">${hospital.name}</option>`);
                    });
                } else {
                    showNotification('Không thể tải danh sách bệnh viện', 'error');
                }
            },
            error: function () {
                showNotification('Lỗi kết nối server khi tải bệnh viện', 'error');
            }
        });
    }

    // Lấy thông tin người dùng
    function loadUserInfo() {
        const token = localStorage.getItem('token');
        if (!token) return;

        $.ajax({
            url: 'http://localhost/WEB_ThueHoTroKhamBenh/api/get_user_info.php',
            method: 'GET',
            data: { token: token },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    $('#full_name').val(data.data.name || '');
                    $('#phone').val(data.data.sdt || '');
                } else {
                    showNotification(data.message, 'error');
                }
            },
            error: function () {
                showNotification('Lỗi kết nối server khi tải thông tin người dùng', 'error');
            }
        });
    }

    // Load bệnh viện và thông tin người dùng
    loadHospitals();
    if ($('input[name="datdv"]:checked').val() === 'ban') {
        loadUserInfo();
    }

    // Xử lý radio button
    $('input[name="datdv"]').change(function () {
        const selected = $(this).val();
        if (selected === 'ban') {
            loadUserInfo(); // Tải thông tin người dùng cho "Đặt cho bạn"
        } else {
            // Reset input cho "Đặt cho người khác"
            $('#full_name').val('');
            $('#phone').val('');
        }
    });

    // Xử lý form đặt lịch
    window.handleBooking = async function (event) {
        event.preventDefault();
        if (!checkLogin()) return false;

        const full_name = $('#full_name').val();
        const phone = $('#phone').val();
        const hospital_id = $('#hospital').val();
        const appointment_date = $('#date').val();
        const appointment_time = $('#time').val();
        const condition = $('#condition').val();
        const token = localStorage.getItem('token');

        // Giả lập dữ liệu khác
        const pickup_location = '123 Đường Láng'; // Có thể thêm input
        const price = 50000; // Có thể thêm input
        const pickup_lat = 21.0285;
        const pickup_lng = 105.8542;

        // Validate
        if (!full_name) {
            showNotification('Vui lòng nhập họ và tên', 'error');
            return false;
        }
        if (!phone) {
            showNotification('Vui lòng nhập số điện thoại', 'error');
            return false;
        }
        if (!hospital_id) {
            showNotification('Vui lòng chọn bệnh viện', 'error');
            return false;
        }
        if (!appointment_date || !appointment_time) {
            showNotification('Vui lòng chọn ngày và giờ khám', 'error');
            return false;
        }
        if (!condition) {
            showNotification('Vui lòng mô tả tình trạng bệnh nhân', 'error');
            return false;
        }

        try {
            const response = await fetch('http://localhost/WEB_ThueHoTroKhamBenh/api/place_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    full_name,
                    phone,
                    pickup_location,
                    dropoff_location: '',
                    price,
                    pickup_lat,
                    pickup_lng,
                    hospital_id,
                    appointment_date,
                    appointment_time,
                    condition,
                    token
                })
            });
            const data = await response.json();

            if (data.success) {
                showNotification('Đặt dịch vụ thành công! Vui lòng chờ tài xế chấp nhận.', 'success', 'index.php');
            } else {
                showNotification(data.message, 'error');
            }
        } catch (error) {
            showNotification('Lỗi kết nối máy chủ', 'error');
        }

        return false;
    };
});
</script>