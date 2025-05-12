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
        const token = sessionStorage.getItem('token');
        const fullName = sessionStorage.getItem('full_name');
        const role = sessionStorage.getItem('role');
        const userNameElement = document.getElementById('userName');
        const loginLink = document.querySelector('.login-link');
        const registerLink = document.querySelector('.register-link');
        const logoutLink = document.querySelector('.logout-link');
        const customerLink = document.querySelector('.customer-link');
        const driverLink = document.querySelector('.driver-link');

        if (token && fullName) {
            userNameElement.textContent = `Xin chào ${fullName}`;
            userNameElement.style.display = 'inline';
            loginLink.style.display = 'none';
            registerLink.style.display = 'none';
            logoutLink.style.display = 'inline';
            if (role === '0') {
                customerLink.style.display = 'inline';
                driverLink.style.display = 'none';
            } else if (role === '1') {
                customerLink.style.display = 'none';
                driverLink.style.display = 'inline';
            }
        } else {
            userNameElement.style.display = 'none';
            loginLink.style.display = 'inline';
            registerLink.style.display = 'inline';
            logoutLink.style.display = 'none';
            customerLink.style.display = 'inline';
            driverLink.style.display = 'none';
        }
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
