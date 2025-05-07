<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Xuất - Đi cùng tôi</title>
    <link rel="stylesheet" href="../Assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../Assets/scripts.js?v=2"></script>
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
        .green-label {
            color: #2563eb;
            text-align: center;
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 20px;
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
            margin-bottom: 20px;
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
            color: white;
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
    <?php
    include("../Assets/header.php");
    ?>
    <div class="ct">
        <div class="br p-6 text-white" id="ct" style="display: none;">
            <div class="form mb-4">
                <label><input type="radio" name="datdv" value="ban" checked> Đặt cho bạn</label>
                <label><input type="radio" name="datdv" value="nguoi_khac"> Đặt cho người khác</label>

            </div>
            <!-- Đây là nơi load form -->
            <div id="formContainer">
                <h2 id="formTitle" class="text-lg font-bold mb-2 green-label">Thông tin người đặt hộ</h2>
                <!-- Thêm trường thông tin người đặt hộ khi chọn "Đặt cho người khác" -->

                <label for="full_name" class="block text-lg font-medium ">Họ và Tên của bạn:</label>
                <input type="text" id="full_name" disabled placeholder="Họ tên của bạn"
                    class="p-2 rounded w-full text-black mb-2">
                <label for="phone"  class="block text-lg font-medium">Số điện thoại của bạn:</label>
                <input type="text" id="phone" placeholder="Số điện thoại" disabled class="p-2 rounded w-full text-black mb-2">
                <div id="guardianInfo" style="display: none;">
                    <h3 class="text-lg font-bold mb-2 green-label">Thông tin người được đặt hộ </h3>
                    <label for="guardian_full_name" class="block text-lg font-medium">Họ và Tên người được đặt hộ:</label>
                    <input type="text" id="guardian_full_name" placeholder="Họ tên người đặt hộ"
                        class="p-2 rounded w-full text-black mb-2">

                    <label for="guardian_phone" class="block text-lg font-medium">Số điện thoại người được đặt hộ:</label>
                    <input type="text" id="guardian_phone" placeholder="Số điện thoại người đặt hộ"
                        class="p-2 rounded w-full text-black mb-2">
                </div>
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
                            <textarea id="condition" name="condition" rows="4" cols="50"
                                placeholder="Mô tả tình trạng sức khỏe của bệnh nhân..." required></textarea>
                            <br><br>
                            <label for="gia"></label>
                            <input type="text" id="gia" name="gia" placeholder="Giá dịch vụ: 23.000VNĐ/Giờ" disabled>
                            <br><br>
                            <button type="submit">Đặt lịch</button>
                        </form>
                    </div>
                    <!-- Bên phải: Gợi ý nội dung thêm -->
                    <div class="booking-info-right"
                        style="flex: 1; background-color: #f7f7f7; padding: 2rem; border-radius: 10px;">
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
    <!-- ---- -->
    <!-- <script>
        localStorage.removeItem('token');
        localStorage.removeItem('full_name');
        showNotification('Đăng xuất thành công!', 'success', '../index.php');
    </script> -->
    <!-- ----- -->
    <?php
    include("../Assets/footer.php");
    ?>

    <!--  -->
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
                const mainContent = document.getElementById('ct');
                if (!token || role !== '0') {
                    showNotification('Vui lòng đăng nhập để đặt dịch vụ !', 'warning', '../login.php');
                    return false;
                }
                if (mainContent) mainContent.style.display = 'block';
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

                            showNotification('Không thể tải danh sách bệnh viện', 'danger ');
                        }
                    },
                    error: function () {
                        showNotification('Lỗi kết nối server khi tải bệnh viện', 'danger ');
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
                            showNotification(data.message, 'danger ');
                        }
                    },
                    error: function () {
                        showNotification('Lỗi kết nối server khi tải thông tin người dùng', 'danger ');
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
                    $('#guardianInfo').hide();
                } else {
                    // Reset input cho "Đặt cho người khác"
                    $('#formTitle').text('Thông tin đặt cho người khác');
                    $('#guardianInfo').show();
                    
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
                    showNotification('Vui lòng nhập họ và tên', 'danger ');
                    return false;
                }
                if (!phone) {
                    showNotification('Vui lòng nhập số điện thoại', 'danger ');
                    return false;
                }
                if (!hospital_id) {
                    showNotification('Vui lòng chọn bệnh viện', 'danger ');
                    return false;
                }
                if (!appointment_date || !appointment_time) {
                    showNotification('Vui lòng chọn ngày và giờ khám', 'danger ');
                    return false;
                }
                if (!condition) {
                    showNotification('Vui lòng mô tả tình trạng bệnh nhân', 'danger ');
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
                        showNotification(data.message, 'danger ');
                    }
                } catch (error) {
                    showNotification('Lỗi kết nối máy chủ', 'danger ');
                }

                return false;
            };
        });
    </script>

</body>

</html>