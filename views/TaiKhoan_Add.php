<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Tài Khoản | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/navi.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/nhacungcap.css"> 
    <style>
        .form-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 600px; margin: 20px auto; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #333; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn-submit { background: #27ae60; color: white; border: none; padding: 12px 25px; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%; }
        .btn-submit:hover { background: #219150; }
        .btn-back { display: inline-block; margin-bottom: 15px; color: #666; text-decoration: none; }
        
        /* Style cho ô tìm kiếm nhân viên */
        .search-box { margin-bottom: 5px; border-color: #3498db; }
    </style>
</head>
<body>
    
    <?php 
        $naviPath = _DIR_ROOT . '/views/navi.php';
        if (file_exists($naviPath)) { include_once $naviPath; }
    ?>

    <main class="main-content">
        <div class="page-header">
            <h2><i class="fas fa-user-plus"></i> CẤP TÀI KHOẢN NHÂN VIÊN</h2>
        </div>

        <a href="<?= _WEB_ROOT ?>/TaiKhoan" class="btn-back">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách
        </a>

        <div class="form-container">
            <form id="addUserForm">
                <div class="form-group">
                    <label>Chọn nhân viên</label>
                    <input type="text" id="filterNhanVien" class="form-control search-box" placeholder="Gõ tên hoặc SĐT để lọc nhanh...">
                    <select name="id_nhanvien" id="id_nhanvien" class="form-control" required>
                        <option value="">-- Chọn nhân viên từ danh sách --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tên đăng nhập (Username)</label>
                    <input type="text" name="username" class="form-control" placeholder="Ví dụ: haole99" required>
                </div>

                <div class="form-group" style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Mật khẩu</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Xác nhận mật khẩu</label>
                        <input type="password" id="confirm_password" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Phân quyền truy cập</label>
                    <select name="role" class="form-control">
                        <option value="nhan_vien">Nhân viên (Chỉ xem Sản phẩm, Kho, Bán hàng)</option>
                        <option value="admin">Quản trị viên (Toàn quyền)</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-check-circle"></i> XÁC NHẬN TẠO TÀI KHOẢN
                </button>
            </form>
        </div>
    </main>

    <script>
        const API_URL = '<?= _WEB_ROOT ?>/api/user';
        let allNhanVien = [];

        // 1. Tải danh sách nhân viên khi trang load
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                const response = await fetch(`${API_URL}/nhanvien`);
                allNhanVien = await response.json();
                renderSelect(allNhanVien);
            } catch (e) { alert("Không thể tải danh sách nhân viên!"); }
        });

        // 2. Hàm hiển thị danh sách vào Select
        function renderSelect(data) {
            const select = document.getElementById('id_nhanvien');
            select.innerHTML = '<option value="">-- Chọn nhân viên từ danh sách --</option>' + 
                data.map(nv => `<option value="${nv.id}">${nv.ten_nv} - ${nv.sdt}</option>`).join('');
        }

        // 3. Logic lọc nhân viên theo tên hoặc SĐT
        document.getElementById('filterNhanVien').addEventListener('input', function(e) {
            const val = e.target.value.toLowerCase();
            const filtered = allNhanVien.filter(nv => 
                nv.ten_nv.toLowerCase().includes(val) || nv.sdt.includes(val)
            );
            renderSelect(filtered);
        });

        // 4. Xử lý Submit Form
        document.getElementById('addUserForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            if (document.getElementById('password').value !== document.getElementById('confirm_password').value) {
                alert("Mật khẩu xác nhận không khớp!");
                return;
            }

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(`${API_URL}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (result.status === 'success') {
                    alert(result.message);
                    window.location.href = '<?= _WEB_ROOT ?>/TaiKhoan';
                } else {
                    alert("Lỗi: " + result.message);
                }
            } catch (error) { alert("Lỗi kết nối máy chủ!"); }
        });
    </script>
</body>
</html>