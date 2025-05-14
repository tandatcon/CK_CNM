<header class="header">
    <div class="logo-group">
        <a href="/WEB_ThueHoTroKhamBenh/public/index.php"><img src="/WEB_ThueHoTroKhamBenh/IMG/hia.jpg" alt="Logo" class="logo" /></a>
        <span class="site-name">Đi cùng tôi</span>
    </div>
    <nav class="nav">
        <a href="/WEB_ThueHoTroKhamBenh/index.php" class="nav-link">Trang chủ</a>
        <a href="#loiich" class="nav-link">Về chúng tôi</a>
        <a href="/WEB_ThueHoTroKhamBenh/public/datDV.php" class="nav-link customer-link">Đặt dịch vụ</a>
        <a href="/WEB_ThueHoTroKhamBenh/public/xemDV.php" class="nav-link customer-link">Dịch vụ của bạn</a>
        <a href="/WEB_ThueHoTroKhamBenh/public/driver/orders.php" class="nav-link driver-link" style="display: none;">Danh sách đơn</a>
        <a href="#dat" class="nav-link">Liên hệ</a>
        <span id="userName" class="nav-link" style="display: none;"></span>
        <a href="/WEB_ThueHoTroKhamBenh/public/login.php" class="nav-link login-link"><i class="fas fa-user"></i> Đăng nhập</a>
        <a href="/WEB_ThueHoTroKhamBenh/public/register.php" class="nav-link register-link"><i class="fas fa-user-plus"></i> Đăng ký</a>
        <a href="/WEB_ThueHoTroKhamBenh/public/logout.php" class="nav-link logout-link" style="display: none;"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
    </nav>
</header>
<script>
    function checkLoginStatus() {
    const userNameElement = document.getElementById('userName');
    const loginLink = document.querySelector('.login-link');
    const registerLink = document.querySelector('.register-link');
    const logoutLink = document.querySelector('.logout-link');
    const customerLinks = document.querySelectorAll('.customer-link'); // Chọn tất cả customer-link
    const driverLink = document.querySelector('.driver-link');

    // Hàm cập nhật giao diện header
    function updateHeader(user) {
        if (user) {
            // Thoát ký tự tên để chống XSS
            const safeName = user.name.replace(/</g, '&lt;').replace(/>/g, '&gt;');
            userNameElement.textContent = `Xin chào ${safeName}`;
            userNameElement.style.display = 'inline';
            loginLink.style.display = 'none';
            registerLink.style.display = 'none';
            logoutLink.style.display = 'inline';
            if (user.role === 0) {
                customerLinks.forEach(link => link.style.display = 'inline');
                driverLink.style.display = 'none';
            } else if (user.role === 1) {
                customerLinks.forEach(link => link.style.display = 'none');
                driverLink.style.display = 'inline';
            }
        } else {
            userNameElement.style.display = 'none';
            loginLink.style.display = 'inline';
            registerLink.style.display = 'inline';
            logoutLink.style.display = 'none';
            customerLinks.forEach(link => link.style.display = 'inline');
            driverLink.style.display = 'none';
        }
    }

    // Gọi API để kiểm tra đăng nhập
    $.ajax({
        url: 'http://localhost/WEB_ThueHoTroKhamBenh/api/get_user_info.php',
        method: 'GET',
        dataType: 'json',
        xhrFields: { withCredentials: true },
        success: function (data) {
            if (data.success) {
                updateHeader(data.data);
            } else {
                updateHeader(null);
            }
        },
        error: function (xhr) {
            let message = 'Lỗi kết nối server';
            try {
                const response = JSON.parse(xhr.responseText);
                message = response.message || message;
                if (response.error_code === 'TOKEN_EXPIRED') {
                    // Làm mới token
                    $.ajax({
                        url: 'http://localhost/WEB_ThueHoTroKhamBenh/api/refresh_token.php',
                        method: 'POST',
                        dataType: 'json',
                        xhrFields: { withCredentials: true },
                        success: function (refreshData) {
                            if (refreshData.success) {
                                // Thử lại get_user_info
                                $.ajax({
                                    url: 'http://localhost/WEB_ThueHoTroKhamBenh/api/get_user_info.php',
                                    method: 'GET',
                                    dataType: 'json',
                                    xhrFields: { withCredentials: true },
                                    success: function (retryData) {
                                        if (retryData.success) {
                                            updateHeader(retryData.data);
                                        } else {
                                            updateHeader(null);
                                        }
                                    },
                                    error: function () {
                                        updateHeader(null);
                                    }
                                });
                            } else {
                                updateHeader(null);
                            }
                        },
                        error: function () {
                            updateHeader(null);
                        }
                    });
                } else {
                    updateHeader(null);
                }
            } catch (e) {
                console.error('Không thể parse JSON:', xhr.responseText);
                updateHeader(null);
            }
        }
    });
}

window.addEventListener('load', checkLoginStatus);
</script>




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
</style>
