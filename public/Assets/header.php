<header class="header">
    <div class="logo-group">
        <a href="/WEB_ThueHoTroKhamBenh/public/index.php">
            <img src="/WEB_ThueHoTroKhamBenh/IMG/hia.jpg" alt="Logo" class="logo" />
        </a>
        <span class="site-name">Đi cùng tôi</span>
    </div>
    <nav class="nav">
        <a href="/WEB_ThueHoTroKhamBenh/index.php" class="nav-link">Trang chủ</a>
        <a href="#loiich" class="nav-link">Về chúng tôi</a>
        <a href="/WEB_ThueHoTroKhamBenh/public/datDV.php" class="nav-link customer-link">Đặt dịch vụ</a>
        <a href="/WEB_ThueHoTroKhamBenh/public/xemDV.php" class="nav-link customer-link">Dịch vụ của bạn</a>
        <a href="/WEB_ThueHoTroKhamBenh/public/xemDVHN.php" class="nav-link driver-link" style="display: none;">Đơn dịch vụ hôm nay nè</a>
        <a href="/WEB_ThueHoTroKhamBenh/public/xemDH.php" class="nav-link driver-link" style="display: none;">Danh sách đơn</a>
        <a href="/WEB_ThueHoTroKhamBenh/public/phancongDH.php" class="nav-link manager-link" style="display: none;">Phân công đơn dịch vụ</a>
        <a href="/WEB_ThueHoTroKhamBenh/public/lienhe.php" class="nav-link">Liên hệ</a>
        <span id="userName" class="nav-link" style="display: none;"></span>
        <a href="/WEB_ThueHoTroKhamBenh/public/login.php" class="nav-link login-link"><i class="fas fa-user"></i> Đăng nhập</a>
        <a href="/WEB_ThueHoTroKhamBenh/public/register.php" class="nav-link register-link"><i class="fas fa-user-plus"></i> Đăng ký</a>
        <a href="/WEB_ThueHoTroKhamBenh/public/logout.php" class="nav-link logout-link" style="display: none;"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
    </nav>
</header>

<!-- <div class="notification-modal" id="notificationModal">
    <div class="notification-content" id="notificationContent">
        <i id="notificationIcon"></i>
        <p id="notificationMessage"></p>
        <button onclick="closeNotification()">Đóng</button>
    </div>
</div> -->

<script>
    // Biến toàn cục lưu người dùng hiện tại
    let currentUser = null;

    function updateHeader(user) {
    const userNameElement = document.getElementById('userName');
    const loginLink = document.querySelector('.login-link');
    const registerLink = document.querySelector('.register-link');
    const logoutLink = document.querySelector('.logout-link');
    const customerLinks = document.querySelectorAll('.customer-link');
    const driverLinks = document.querySelectorAll('.driver-link');
    const managerLinks = document.querySelectorAll('.manager-link');

    if (user) {
        const safeName = (user.name || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        userNameElement.textContent = `Xin chào ${safeName}`;
        userNameElement.style.display = 'inline';
        loginLink.style.display = 'none';
        registerLink.style.display = 'none';
        logoutLink.style.display = 'inline';

        if (user.role === 0) {
            customerLinks.forEach(link => link.style.display = 'inline');
            driverLinks.forEach(link => link.style.display = 'none');
            managerLinks.forEach(link => link.style.display = 'none');
        } else if (user.role === 1) {
            customerLinks.forEach(link => link.style.display = 'none');
            driverLinks.forEach(link => link.style.display = 'inline');
            managerLinks.forEach(link => link.style.display = 'none');
        } else if (user.role === 2) {
            customerLinks.forEach(link => link.style.display = 'none');
            driverLinks.forEach(link => link.style.display = 'none');
            managerLinks.forEach(link => link.style.display = 'inline');
        }
    } else {
        userNameElement.style.display = 'none';
        loginLink.style.display = 'inline';
        registerLink.style.display = 'inline';
        logoutLink.style.display = 'none';
        customerLinks.forEach(link => link.style.display = 'inline');
        driverLinks.forEach(link => link.style.display = 'none');
    }
}



    function checkLoginStatus() {
        $.ajax({
            url: 'http://localhost/WEB_ThueHoTroKhamBenh/api/check_auth.php',
            method: 'GET',
            xhrFields: { withCredentials: true },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    currentUser = data.data;
                    updateHeader(currentUser);
                    window.currentUser = data.data;

                    // 🔔 Gửi sự kiện cho các script khác
                    const loginEvent = new CustomEvent("userChecked", { detail: currentUser });
                    window.dispatchEvent(loginEvent);
                } else {
                    updateHeader(null);
                    currentUser = null;
                    if (data.error_code === 'TOKEN_EXPIRED' || data.error_code === 'INVALID_REFRESH_TOKEN') {
                        showNotification(data.message, 'danger', '/WEB_ThueHoTroKhamBenh/public/login.php');
                    }

                    const loginEvent = new CustomEvent("userChecked", { detail: null });
                    window.dispatchEvent(loginEvent);
                }
            },
            error: function (xhr) {
                console.error('Check auth error:', xhr.responseText);
                updateHeader(null);
                currentUser = null;

                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error_code === 'TOKEN_EXPIRED' || response.error_code === 'INVALID_REFRESH_TOKEN') {
                        showNotification(response.message, 'danger', '/WEB_ThueHoTroKhamBenh/public/login.php');
                    }
                    // if (response.error_code === 'NOT_LOGGED_IN') {
                    //     showNotification(response.message, 'danger', '/WEB_ThueHoTroKhamBenh/public/login.php');
                    // }

                } catch (e) {
                    console.error('Không thể parse JSON:', xhr.responseText);
                }

                const loginEvent = new CustomEvent("userChecked", { detail: null });
                window.dispatchEvent(loginEvent);
            }
        });
    }

    window.addEventListener('load', checkLoginStatus);
    window.addEventListener('load', updateHeader);
</script>
