<header class="header">
    <div class="logo-group">
        <a href="/WEB_ThueHoTroKhamBenh/public/index.php"><img src="/WEB_ThueHoTroKhamBenh/IMG/hia.jpg" alt="Logo" class="logo" /></a>
        <span class="site-name">Đi cùng tôi</span>
    </div>
    <nav class="nav">
        <a href="/WEB_ThueHoTroKhamBenh/public/index.php" class="nav-link">Trang chủ</a>
        <a href="#loiich" class="nav-link">Về chúng tôi</a>
        <a href="/WEB_ThueHoTroKhamBenh/public/datDV.php" class="nav-link customer-link">Đặt dịch vụ</a>
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
        const token = localStorage.getItem('token');
        const fullName = localStorage.getItem('full_name');
        const role = localStorage.getItem('role');
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