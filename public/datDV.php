<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Dịch Vụ - Đi cùng tôi</title>
    <link rel="stylesheet" href="Assets/styles.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="Assets/scripts.js?v=3"></script>
    <style>
        .ct {
            background-image: url('/WEB_ThueHoTroKhamBenh/IMG/cauhoi.jpg');
            height: auto;
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
            margin: 14px auto 20px;
            background-color: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            padding: 24px;
            height: auto;
        }

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

        .booking-info-card {
            flex: 1;
            background-color: #f9f5ff;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(107, 70, 193, 0.15);
            color: #4a5568;
            font-family: 'Segoe UI', Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            transition: box-shadow 0.3s ease;
        }

        .booking-info-card:hover {
            box-shadow: 0 12px 28px rgba(107, 70, 193, 0.25);
        }

        .section-title {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            margin: 1.5rem 0 1rem;
            color: black;
        }

        .info-list {
            font-size: 17px;
            line-height: 1.8;
            padding-left: 1.5rem;
            list-style-position: inside;
        }

        .info-list li {
            margin-bottom: 0.75rem;
        }

        #formTitle {
            font-size: 28px;
            font-weight: 600;
            color: #FFFFFF;
            text-align: center;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.15);
            margin-bottom: 24px;
            letter-spacing: 0.8px;
            font-family: 'Playfair Display', 'Georgia', serif;
            width: 80%;
            margin: 0 auto;
            padding: 12px 24px;
            border-radius: 10px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        #formTitle:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            color: #333;
        }

        .error-message {
            color: red;
            font-size: 0.875rem;
            margin-top: 5px;
            display: none;
        }

        .error-message.visible {
            display: block;
        }

        .input-invalid {
            border-color: red;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background: #cccccc;
        }

        button:hover:not(:disabled) {
            background: #2563eb;
        }

        #gia::placeholder {
            color: black;
            opacity: 1;
        }

        .radio-group {
            margin-bottom: 15px;
        }

        .radio-group label {
            margin-right: 1rem;
            font-weight: normal;
            color: #333;
        }
    </style>
</head>
<body class="bg-white text-gray-800">
    <?php include("Assets/header.php"); ?>
    <div class="ct">
        <div class="br" id="ct" style="display: none;">
            <!-- Radio buttons chọn loại đặt dịch vụ -->
            <div class="radio-group">
                <label><input type="radio" name="datdv" value="ban" checked> Đặt cho bạn</label>
                <label><input type="radio" name="datdv" value="nguoi_khac"> Đặt hộ người khác</label>
            </div>
            <div id="formContainer">
                <h2 id="formTitle"><b>ĐẶT DỊCH VỤ CHO BẠN</b></h2>
                <form id="bookingForm" onsubmit="return handleBooking(event)">
                    <!-- Thông tin người đặt -->
                    <div class="form-group">
                        <label for="full_name">Họ và Tên của bạn:</label>
                        <input type="text" id="full_name" placeholder="Họ tên của bạn" disabled>
                        <div id="full_name-error" class="error-message">Không để trống!</div>
                    </div>
                    <div class="form-group">
                        <label for="phone">Số điện thoại của bạn:</label>
                        <input type="text" id="phone" placeholder="Số điện thoại" disabled>
                        <div id="phone-error" class="error-message">Số điện thoại phải gồm 10 số, bắt đầu bằng "0"!</div>
                    </div>
                    <!-- Giới tính và năm sinh cho "Đặt cho bạn" -->
                    <div class="radio-group" id="an">
                        <label style="font-weight: bold; color: #333;">Giới tính:</label>
                        <label><input type="radio" name="gt" value="0" checked> Nam</label>
                        <label><input type="radio" name="gt" value="1"> Nữ</label>
                    </div>
                    <div class="form-group" id="namsinh-container">
                        <label for="namsinh">Năm sinh:</label>
                        <input type="text" id="namsinh" placeholder="Ví dụ: 2000">
                        <div id="namsinh-error" class="error-message">Năm sinh phải là số 4 chữ số từ 1900 đến hiện tại!</div>
                    </div>
                    <!-- Thông tin người được đặt hộ -->
                    <div id="guardianInfo" style="display: none;">
                        <div class="form-group">
                            <label for="quanhe">Quan hệ với người được đặt hộ:</label>
                            <input type="text" id="quanhe" placeholder="Ví dụ: Con, Anh/Chị">
                            <div id="quanhe-error" class="error-message">Không để trống!</div>
                        </div>
                        <div class="form-group">
                            <label for="ten">Họ và Tên người được đặt hộ:</label>
                            <input type="text" id="ten" placeholder="Họ tên người được đặt hộ">
                            <div id="ten-error" class="error-message">Không để trống!</div>
                        </div>
                        <div class="radio-group">
                            <label style="font-weight: bold; color: #333;">Giới tính:</label>
                            <label><input type="radio" name="gt" value="0" checked> Nam</label>
                            <label><input type="radio" name="gt" value="1"> Nữ</label>
                        </div>
                        <div class="form-group">
                            <label for="namsinh_guardian">Năm sinh:</label>
                            <input type="text" id="namsinh_guardian" placeholder="Ví dụ: 2000">
                            <div id="namsinh_guardian-error" class="error-message">Năm sinh phải là số 4 chữ số từ 1900 đến hiện tại!</div>
                        </div>
                        <div class="form-group">
                            <label for="sdt">Số điện thoại người được đặt hộ:</label>
                            <input type="text" id="sdt" placeholder="Số điện thoại">
                            <div id="sdt-error" class="error-message">Số điện thoại phải gồm 10 số, bắt đầu bằng "0"!</div>
                        </div>
                    </div>
                    <!-- Thông tin đặt lịch -->
                    <section class="booking-section" style="display: flex; gap: 2rem; padding: 2rem;">
                        <div class="booking-form-container" style="flex: 1;">
                            <div class="form-group">
                                <label for="hospital">Chọn bệnh viện:</label>
                                <select id="hospital" name="hospital" required>
                                    <option value="">-- Chọn bệnh viện --</option>
                                </select>
                                <div id="hospital-error" class="error-message">Không để trống!</div>
                            </div>
                            <div class="form-group">
                                <label for="diemhen">Điểm hẹn:</label>
                                <input type="text" id="diemhen" name="diemhen" placeholder="Điểm hẹn tại khu vực bệnh viện">
                                <div id="diemhen-error" class="error-message">Không để trống!</div>
                            </div>
                            <div class="form-group">
                                <label for="date">Chọn ngày khám:</label>
                                <input type="date" id="date" name="date" required>
                                <div id="date-error" class="error-message">Ngày khám phải sau ngày hôm nay!</div>
                            </div>
                            <div class="form-group">
                                <label for="time">Chọn giờ hẹn:</label>
                                <input type="time" id="time" name="time" required>
                                <div id="time-error" class="error-message">Không để trống!</div>
                            </div>
                            <div class="form-group">
                                <label for="condition">Tình trạng sức khỏe:</label>
                                <textarea id="condition" name="condition" rows="4" placeholder="Mô tả tình trạng sức khỏe của bệnh nhân..." required></textarea>
                                <div id="condition-error" class="error-message">Không để trống!</div>
                            </div>
                            <div class="form-group">
                                <label for="gia"></label>
                                <input type="text" id="gia" name="gia" placeholder="Giá dịch vụ: từ 23.000VNĐ/Giờ" disabled>
                            </div>
                            <button type="submit" id="submitButton">Đặt lịch</button>
                        </div>
                        <!-- Thông tin hướng dẫn -->
                        <div class="booking-info-card">
                            <p class="section-title">Lưu ý quan trọng!</p>
                            <ol class="info-list">
                                <li>Dịch vụ chỉ áp dụng tại bệnh viện. Bạn và nhân viên sẽ gặp nhau tại bệnh viện theo giờ đã đặt.</li>
                            </ol>
                            <p class="section-title">Hướng dẫn đặt dịch vụ</p>
                            <ol class="info-list">
                                <li>Khi chọn <strong>"Đặt hộ người khác"</strong>, cần điền đúng thông tin <strong>"Người được đặt hộ"</strong>.</li>
                                <li>Chọn đúng bệnh viện bạn muốn đến khám.</li>
                                <li>Địa điểm hẹn là khu vực cụ thể tại bệnh viện (ví dụ: cổng 1, cổng 2...).</li>
                                <li>Ngày khám phải sau ngày hôm nay ít nhất <strong>1 ngày</strong>.</li>
                                <li>Giờ hẹn là thời điểm gặp nhân viên và bắt đầu dịch vụ.</li>
                                <li>Ghi rõ tình trạng sức khỏe như: di chuyển khó khăn, khiếm thị, v.v.</li>
                            </ol>
                            <p class="section-title">Chi phí dịch vụ</p>
                            <ol class="info-list">
                                <li><strong>23,000 VNĐ/giờ</strong> cho 5 giờ đầu tiên.</li>
                                <li><strong>18,000 VNĐ/giờ</strong> từ giờ thứ 6 trở đi.</li>
                            </ol>
                        </div>
                    </section>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal thông báo -->
    <div class="notification-modal" id="notificationModal">
        <div class="notification-content" id="notificationContent">
            <i id="notificationIcon"></i>
            <p id="notificationMessage"></p>
            <button onclick="closeNotification()">Đóng</button>
        </div>
    </div>
    <?php include("Assets/footer.php"); ?>
    <script>
        

        $(document).ready(function () {
            // Kiểm tra đăng nhập
            function checkLogin() {
                const token = sessionStorage.getItem('token');
                const role = sessionStorage.getItem('role');
                const mainContent = document.getElementById('ct');
                if (!token || role !== '0') {
                    showNotification('Vui lòng đăng nhập để đặt dịch vụ!', 'warning', 'login.php');
                    return false;
                }
                if (mainContent) mainContent.style.display = 'block';
                return true;
            }

            // Tải danh sách bệnh viện
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
                                hospitalSelect.append(`<option value="${hospital.id_benhvien}">${hospital.ten_benhvien}</option>`);
                            });
                        } else {
                            showNotification('Không thể tải danh sách bệnh viện: ' + (data.message || 'Lỗi không xác định'), 'danger');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.log('Lỗi tải bệnh viện:', xhr.status, error);
                        showNotification('Lỗi kết nối server khi tải bệnh viện', 'danger');
                    }
                });
            }

            // Lấy thông tin người dùng
            function loadUserInfo() {
    const token = sessionStorage.getItem('token');  // Hoặc sử dụng localStorage.getItem('token')
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
            updateButtonState();
        } else if (data.error_code === 'TOKEN_EXPIRED') {
            // Trường hợp này bạn có thể để trống vì đã xử lý trong error chung
        } else {
            showNotification(data.message || 'Không thể tải thông tin người dùng', 'danger');
        }
    },
    error: handleAjaxError
});

}

function handleAjaxError(xhr, status, error) {
    let message = 'Lỗi kết nối server. Vui lòng thử lại sau.';
    
    try {
        const response = JSON.parse(xhr.responseText);
        message = response.message || message;

        // Nếu token hết hạn
        if (response.error_code === 'TOKEN_EXPIRED') {
            sessionStorage.removeItem('token');
            showNotification(message, 'danger', 'login.php');
            return;
        }
    } catch (e) {
        console.error("Không thể parse JSON từ server:", xhr.responseText);
    }

    showNotification(message, 'error');
    console.error('Lỗi kết nối server:', xhr.status, error, xhr.responseText);
}

            // Kiểm tra tính hợp lệ của form
            function isFormValid() {
                const full_name = $('#full_name').val().trim();
                const phone = $('#phone').val().trim();
                const hospital = $('#hospital').val();
                const diemhen = $('#diemhen').val().trim();
                const date = $('#date').val();
                const time = $('#time').val();
                const condition = $('#condition').val().trim();
                const datdv = $('input[name="datdv"]:checked').val();
                const quanhe = $('#quanhe').val().trim();
                const ten = $('#ten').val().trim();
                const sdt = $('#sdt').val().trim();
                const namsinh = datdv === 'ban' ? $('#namsinh').val().trim() : $('#namsinh_guardian').val().trim();
                const gt = $('input[name="gt"]:checked').val();

                const phoneRegex = /^0\d{9}$/;
                const yearRegex = /^\d{4}$/;
                const currentYear = new Date().getFullYear();
                let isValid = true;

                // Reset lỗi
                $('.error-message').removeClass('visible');
                $('.form-group input, .form-group select, .form-group textarea').removeClass('input-invalid');

                // Kiểm tra các trường bắt buộc
                if (!full_name) {
                    $('#full_name-error').addClass('visible');
                    $('#full_name').addClass('input-invalid');
                    isValid = false;
                }
                if (!phoneRegex.test(phone)) {
                    $('#phone-error').addClass('visible');
                    $('#phone').addClass('input-invalid');
                    isValid = false;
                }
                if (!hospital) {
                    $('#hospital-error').addClass('visible');
                    $('#hospital').addClass('input-invalid');
                    isValid = false;
                }
                if (!diemhen) {
                    $('#diemhen-error').addClass('visible');
                    $('#diemhen').addClass('input-invalid');
                    isValid = false;
                }
                if (!date || new Date(date) <= new Date()) {
                    $('#date-error').addClass('visible');
                    $('#date').addClass('input-invalid');
                    isValid = false;
                }
                if (!time) {
                    $('#time-error').addClass('visible');
                    $('#time').addClass('input-invalid');
                    isValid = false;
                }
                if (!condition) {
                    $('#condition-error').addClass('visible');
                    $('#condition').addClass('input-invalid');
                    isValid = false;
                }
                if (!namsinh || !yearRegex.test(namsinh) || namsinh < 1900 || namsinh > currentYear) {
                    if (datdv === 'ban') {
                        $('#namsinh-error').addClass('visible');
                        $('#namsinh').addClass('input-invalid');
                    } else {
                        $('#namsinh_guardian-error').addClass('visible');
                        $('#namsinh_guardian').addClass('input-invalid');
                    }
                    isValid = false;
                }

                // Kiểm tra khi chọn "Đặt hộ người khác"
                if (datdv === 'nguoi_khac') {
                    if (!quanhe) {
                        $('#quanhe-error').addClass('visible');
                        $('#quanhe').addClass('input-invalid');
                        isValid = false;
                    }
                    if (!ten) {
                        $('#ten-error').addClass('visible');
                        $('#ten').addClass('input-invalid');
                        isValid = false;
                    }
                    if (!phoneRegex.test(sdt)) {
                        $('#sdt-error').addClass('visible');
                        $('#sdt').addClass('input-invalid');
                        isValid = false;
                    }
                }

                return isValid;
            }

            // Cập nhật trạng thái nút
            function updateButtonState() {
                $('#submitButton').prop('disabled', !isFormValid());
            }
            // Xử lý radio button
            $('input[name="datdv"]').change(function () {
                const selected = $(this).val();
                if (selected === 'ban') {
                    $('#formTitle').text('ĐẶT DỊCH VỤ CHO BẠN');
                    $('#guardianInfo').hide();
                    $('#an').show();
                    $('#namsinh-container').show();
                    $('#full_name, #phone').prop('disabled', true);
                    loadUserInfo();
                } else {
                    $('#formTitle').text('ĐẶT DỊCH VỤ CHO NGƯỜI KHÁC');
                    $('#guardianInfo').show();
                    $('#an').hide();
                    $('#namsinh-container').hide();
                    $('#full_name, #phone').prop('disabled', true);
                    loadUserInfo();
                }
                updateButtonState();
            });

            // Gắn sự kiện input để cập nhật trạng thái nút
            $('#full_name, #phone, #hospital, #diemhen, #date, #time, #condition, #quanhe, #ten, #sdt, #namsinh, #namsinh_guardian').on('input change', updateButtonState);

            // Xử lý form đặt lịch
            window.handleBooking = async function (event) {
                event.preventDefault();
                if (!checkLogin()) return false;

                if (!isFormValid()) {
                    showNotification('Vui lòng điền đầy đủ và đúng thông tin!', 'danger');
                    return false;
                }

                const button = $('#submitButton');
                button.prop('disabled', true).text('Đang xử lý...');

                const full_name = $('#full_name').val().trim();
                const phone = $('#phone').val().trim();
                const hospital_id = $('#hospital').val();
                const diemhen = $('#diemhen').val().trim();
                const appointment_date = $('#date').val();
                const appointment_time = $('#time').val();
                const condition = $('#condition').val().trim();
                const token = localStorage.getItem('token');
                const datdv = $('input[name="datdv"]:checked').val();
                const guardian_relation = $('#quanhe').val().trim();
                const guardian_name = $('#ten').val().trim();
                const guardian_phone = $('#sdt').val().trim();
                const namsinh = datdv === 'ban' ? $('#namsinh').val().trim() : $('#namsinh_guardian').val().trim();
                const gt = $('input[name="gt"]:checked').val();

                if (!token) {
                    showNotification('Vui lòng đăng nhập để đặt dịch vụ!', 'warning', 'login.php');
                    button.prop('disabled', !isFormValid()).text('Đặt lịch');
                    return false;
                }

                const payload = {
                    full_name,
                    phone,
                    diemhen,
                    namsinh,
                    gt,
                    hospital_id,
                    appointment_date,
                    appointment_time,
                    condition,
                    token,
                    guardian_relation: datdv === 'nguoi_khac' ? guardian_relation : '',
                    guardian_name: datdv === 'nguoi_khac' ? guardian_name : '',
                    guardian_phone: datdv === 'nguoi_khac' ? guardian_phone : ''
                };
                console.log('Payload gửi đi:', payload);

                try {
                    const response = await fetch('http://localhost/WEB_ThueHoTroKhamBenh/api/place_order.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    console.log('Phản hồi từ server:', response.status, response.statusText);
                    if (!response.ok) {
                        throw new Error(`Lỗi HTTP: ${response.status} ${response.statusText}`);
                    }
                    const data = await response.json();
                    console.log('Dữ liệu JSON:', data);

                    if (data.success) {
                        showNotification('Đặt dịch vụ thành công! Vui lòng chờ tài xế chấp nhận.', 'success', '../index.php');
                    } else {
                        showNotification(data.message || 'Lỗi không xác định từ server', 'danger');
                    }
                } catch (error) {
                    console.log('Lỗi chi tiết:', error);
                    showNotification(`Lỗi kết nối máy chủ: ${error.message}`, 'danger');
                } finally {
                    button.prop('disabled', !isFormValid()).text('Đặt lịch');
                }

                return false;
            };

            // Khởi tạo
            if (checkLogin()) {
                loadHospitals();
                if ($('input[name="datdv"]:checked').val() === 'ban') {
                    loadUserInfo();
                }
            }
        });
    </script>
</body>
</html>