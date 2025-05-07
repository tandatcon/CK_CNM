<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Đi cùng tôi</title>
    <link rel="stylesheet" href="Assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="Assets/scripts.js"></script>
    <style>
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
    <?php include_once(__DIR__ . '/Assets/header.php'); ?>
    <div class="login-container">
        <h2 class="text-2xl font-bold text-center">Đăng Nhập</h2>
        <form id="loginForm" onsubmit="return handleLogin(event)">
            <div class="form-group">
                <label for="phone">Số Điện Thoại:</label>
                <input type="tel" id="phone" name="phone" placeholder="Nhập số điện thoại" required>
                <span id="phoneError" class="error"></span>
            </div>
            <div class="form-group">
                <label for="password">Mật Khẩu:</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
                    <i class="fas fa-eye" id="togglePassword" onclick="togglePassword('password')"></i>
                </div>
                <span id="passwordError" class="error"></span>
            </div>
            <button type="submit">Đăng Nhập</button>
            <div class="links">
                <a href="register.php">Tạo tài khoản</a>
            </div>
        </form>
    </div>
    <div class="notification-modal" id="notificationModal">
        <div class="notification-content" id="notificationContent">
            <i id="notificationIcon"></i>
            <p id="notificationMessage"></p>
            <button onclick="closeNotification()">Đóng</button>
        </div>
    </div>
    <?php include_once(__DIR__ . '/Assets/footer.php'); ?>
    <script>
        

        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById('toggle' + inputId.charAt(0).toUpperCase() + inputId.slice(1));
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        async function handleLogin(event) {
            event.preventDefault();
            console.log('Login form submitted'); // Debug

            const phone = document.getElementById('phone').value;
            const password = document.getElementById('password').value;
            const phoneError = document.getElementById('phoneError');
            const passwordError = document.getElementById('passwordError');

            // Reset lỗi
            phoneError.classList.remove('visible');
            passwordError.classList.remove('visible');
            phoneError.textContent = '';
            passwordError.textContent = '';

            let isValid = true;
            const phoneRegex = /^0\d{9}$/;
            if (!phoneRegex.test(phone)) {
                phoneError.textContent = "Số điện thoại không hợp lệ! Phải có 10 số, bắt đầu bằng 0.";
                phoneError.classList.add('visible');
                isValid = false;
            }

            if (password.length < 6) {
                passwordError.textContent = "Mật khẩu phải có ít nhất 6 ký tự!";
                passwordError.classList.add('visible');
                isValid = false;
            }

            if (!isValid) {
                console.log('Validation failed'); // Debug
                return false;
            }

            try {
                console.log('Sending API request'); // Debug
                const response = await fetch('http://localhost/WEB_ThueHoTroKhamBenh/api/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ phone, password })
                });

                console.log('API response status:', response.status); // Debug
                const data = await response.json();
                console.log('API response data:', data); // Debug

                if (data.success) {
                    localStorage.setItem('token', data.token);
                    localStorage.setItem('full_name', data.data.full_name);
                    localStorage.setItem('role', data.data.role);
                    showNotification(`Đăng nhập thành công! Chào ${data.data.full_name}`, 'success', data.data.role === '1' ? 'driver/orders.php' : '../index.php');
                } else {
                    showNotification(data.message, 'danger ');
                }
            } catch (error) {
                console.error('Lỗi kết nối API:', error); // Debug
                showNotification('Lỗi kết nối máy chủ. Vui lòng thử lại sau.', 'danger ');
            }

            return false;
        }
    </script>
</body>
</html>