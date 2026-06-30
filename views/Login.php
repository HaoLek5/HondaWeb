<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/login.css">
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <h2>HONDA THẮNG LỢI</h2>
        <p>Hệ thống Quản lý Showroom</p>
    </div>

    <form id="loginForm">
        <div class="form-group">
            <label for="username">Tài khoản</label>
            <input type="text" id="username" class="input-control" placeholder="Tên đăng nhập" required>
        </div>

        <div class="form-group">
            <label for="password">Mật khẩu</label>
            <div class="password-container">
                <input type="password" id="password" class="input-control" placeholder="••••••••" required>
                <i class="fas fa-eye" id="togglePassword"></i>
            </div>
        </div>

        <button type="submit" class="btn-login" id="btnLogin">ĐĂNG NHẬP</button>
    </form>
    
    <div id="message"></div>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const passwordField = document.querySelector('#password');

    togglePassword.addEventListener('click', function () {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });

    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const msg = document.getElementById('message');
        const btnLogin = document.getElementById('btnLogin');

        btnLogin.disabled = true;
        btnLogin.innerText = "ĐANG KIỂM TRA...";

        try {
            const response = await fetch('<?= _WEB_ROOT ?>/api/user', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    _action: 'login',
                    username: document.getElementById('username').value,
                    password: passwordField.value
                })
            });

            const data = await response.json();

            msg.innerText = data.message;
            msg.style.color = data.status === 'success' ? '#28a745' : '#dc3545';

            if (data.status === 'success') {
                setTimeout(() => {
                    window.location.href = '<?= _WEB_ROOT ?>/SanPham';
                }, 800);
            } else {
                btnLogin.disabled = false;
                btnLogin.innerText = "ĐĂNG NHẬP";
            }

        } catch (error) {
            msg.innerText = "Lỗi kết nối server!";
            msg.style.color = '#dc3545';
            btnLogin.disabled = false;
            btnLogin.innerText = "ĐĂNG NHẬP";
        }
    });
</script>


</body>
</html>


