<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Tài Khoản | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/navi.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/nhacungcap.css"> 
    <style>
        .form-container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); max-width: 650px; margin: 30px auto; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50; font-size: 14px; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #dcdfe6; border-radius: 6px; box-sizing: border-box; transition: all 0.3s; }
        .form-control:focus { border-color: #3498db; outline: none; box-shadow: 0 0 5px rgba(52,152,219,0.2); }
        .form-control:disabled { background-color: #f5f7fa; color: #909399; cursor: not-allowed; }
        .btn-submit { background: #3498db; color: white; border: none; padding: 14px 25px; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; font-size: 16px; margin-top: 10px; transition: background 0.3s; }
        .btn-submit:hover { background: #2980b9; }
        .note { font-size: 12px; color: #e67e22; margin-top: 8px; display: flex; align-items: center; gap: 5px; }
        .loading-overlay { text-align: center; padding: 40px; color: #666; }
    </style>
</head>
<body>
    <?php 
        $naviPath = _DIR_ROOT . '/views/navi.php';
        if (file_exists($naviPath)) { include_once $naviPath; }
    ?>

    <main class="main-content">
        <div class="page-header">
            <h2><i class="fas fa-user-edit"></i> CHỈNH SỬA TÀI KHOẢN</h2>
        </div>

        <a href="<?= _WEB_ROOT ?>/TaiKhoan" class="btn-back" style="text-decoration: none; color: #666; margin-left: 20px; display: inline-block; margin-bottom: 15px;">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách
        </a>

        <div class="form-container" id="formWrapper">
            <div id="loading" class="loading-overlay">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Đang tải thông tin tài khoản...</p>
            </div>

            <form id="editUserForm" style="display: none;">
                <input type="hidden" name="id" id="userIdField">
                
                <div class="form-group">
                    <label><i class="fas fa-id-badge"></i> Nhân viên sở hữu</label>
                    <input type="text" id="display_name" class="form-control" disabled>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-user"></i> Tên đăng nhập (Username)</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-key"></i> Mật khẩu mới</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Để trống nếu giữ nguyên">
                    <p class="note"><i class="fas fa-info-circle"></i> Chỉ nhập khi cần đổi mật khẩu.</p>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-user-tag"></i> Quyền truy cập</label>
                    <select name="role" id="role" class="form-control">
                        <option value="nhan_vien">Nhân viên</option>
                        <option value="admin">Quản trị viên</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit" id="btnSubmit">
                    <i class="fas fa-save"></i> CẬP NHẬT TÀI KHOẢN
                </button>
            </form>
        </div>
    </main>

    <script>
    const API_ROOT = '<?= _WEB_ROOT ?>/api/user';

    // 1. Lấy ID từ URL (Xử lý cả link ?id=18 và link dạng /18)
    const urlParams = new URLSearchParams(window.location.search);
    let userId = urlParams.get('id'); 

    if (!userId) {
        userId = window.location.pathname.split('/').filter(s => s !== "").pop();
    }

    // 2. Khi trang load xong
    document.addEventListener('DOMContentLoaded', async () => {
        if(userId && !isNaN(userId)) {
            await loadUserDetail();
        } else {
            document.getElementById('loading').innerHTML = '<b style="color:red">Lỗi: Không tìm thấy ID tài khoản!</b>';
        }
    });

    // 3. Hàm tải dữ liệu chi tiết
    async function loadUserDetail() {
        try {
            // Gọi API lấy thông tin (sử dụng params để an toàn nhất)
            const res = await fetch(`${API_ROOT}/${userId}`);
            const u = await res.json();

            // Kiểm tra nếu API trả về đúng dữ liệu (u.id tồn tại)
            if (u && u.id) {
                document.getElementById('userIdField').value = u.id;
                document.getElementById('display_name').value = `${u.ten_nv} - SĐT: ${u.sdt || 'N/A'}`;
                document.getElementById('username').value = u.username;
                document.getElementById('role').value = u.role;

                // Ẩn loading, hiện form
                document.getElementById('loading').style.display = 'none';
                document.getElementById('editUserForm').style.display = 'block';
            } else {
                document.getElementById('loading').innerHTML = `<b style="color:red">Lỗi: ${u.message || 'Tài khoản không tồn tại!'}</b>`;
            }
        } catch (e) {
            console.error(e);
            document.getElementById('loading').innerHTML = '<b style="color:red">Lỗi: Không thể kết nối đến máy chủ!</b>';
        }
    }

    // 4. Xử lý lưu thay đổi
    document.getElementById('editUserForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = 'Đang xử lý...';

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch(`${API_ROOT}/${userId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            
            const res = await response.json();
            alert(res.message);
            
            if(res.status === 'success') {
                window.location.href = '<?= _WEB_ROOT ?>/TaiKhoan';
            }
        } catch (error) {
            alert("Lỗi kết nối khi cập nhật!");
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> CẬP NHẬT TÀI KHOẢN';
        }
    });
    </script>
</body>
</html>