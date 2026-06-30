<?php
// Đảm bảo session đã được start (thường đã start ở header hoặc index)
$role = $_SESSION['user_role'] ?? 'nhan_vien';
?>

<nav class="sidebar">
    <div class="sidebar-header">
        <h3>HONDA THẮNG LỢI</h3>
    </div>
    
    <ul class="nav-menu">
        <li>
            <a href="<?= _WEB_ROOT ?>/SanPham" class="nav-item" id="menu-sanpham">
                <i class="fas fa-motorcycle"></i> Quản lý sản phẩm
            </a>
        </li>

        <li>
            <a href="<?= _WEB_ROOT ?>/Kho" class="nav-item">
                <i class="fas fa-warehouse"></i> Quản lý kho
            </a>
        </li>

        <?php if ($role === 'admin'): ?>
        <li>
            <a href="<?= _WEB_ROOT ?>/Ncc" class="nav-item">
                <i class="fas fa-truck"></i> Quản lý nhà cung cấp
            </a>
        </li>
        <?php endif; ?>

        <li>
            <a href="<?= _WEB_ROOT ?>/KhachHang" class="nav-item">
                <i class="fas fa-users"></i> Quản lý khách hàng
            </a>
        </li>

        <?php if ($role === 'admin'): ?>
        <li>
            <a href="<?= _WEB_ROOT ?>/NhanVien" class="nav-item">
                <i class="fas fa-user-tie"></i> Quản lý nhân viên
            </a>
        </li>

        <li>
            <a href="<?= _WEB_ROOT ?>/TaiKhoan" class="nav-item">
                <i class="fas fa-user-shield"></i> Quản lý tài khoản
            </a>
        </li>
        <?php endif; ?>

        <li class="has-submenu">
            <div class="nav-item">
                <i class="fas fa-shopping-cart"></i> Quản lý bán hàng
            </div>
            <ul class="submenu">
                <li><a href="<?= _WEB_ROOT ?>/HoaDon_Add" class="submenu-item">Bán hàng</a></li>
                <li><a href="<?= _WEB_ROOT ?>/HoaDon" class="submenu-item">Hóa đơn bán hàng</a></li>
            </ul>
        </li>

        <?php if ($role === 'admin'): ?>
        <li>
            <a href="<?= _WEB_ROOT ?>/ThongKe" class="nav-item">
                <i class="fas fa-chart-line"></i> Báo cáo thống kê
            </a>
        </li>
        <?php endif; ?>

        <li style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px;">
            <a href="javascript:void(0)" onclick="logout()" class="nav-item" style="color: #ff6b6b;">
                <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </a>
        </li>
    </ul>
</nav>

<script>
async function logout() {
    if (confirm("Bạn có chắc chắn muốn thoát hệ thống?")) {
        try {
            // Gọi đến API Logout
            const response = await fetch('<?= _WEB_ROOT ?>/api/user', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ _action: 'logout' })
            });

            const data = await response.json();

            if (data.status === 'success') {
                // Xóa thành công thì về trang đăng nhập
                window.location.href = '<?= _WEB_ROOT ?>';
            } else {
                alert("Lỗi khi đăng xuất!");
            }
        } catch (error) {
            console.error("Logout Error:", error);
            // Backup: Nếu API lỗi vẫn đẩy về trang login cho an toàn
            window.location.href = '<?= _WEB_ROOT ?>';
        }
    }
}
</script>