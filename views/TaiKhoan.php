<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Tài khoản | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/navi.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/nhacungcap.css">
    <style>
        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: bold;
        }
        .role-admin { background: #ffeaa7; color: #d63031; }
        .role-staff { background: #dfe6e9; color: #2d3436; }
        
        .action-btns i {
            cursor: pointer;
            font-size: 18px;
            margin: 0 8px;
            transition: transform 0.2s;
        }
        .action-btns i:hover { transform: scale(1.2); }
        .btn-edit { color: #3498db; }
        .btn-delete { color: #e74c3c; }
        
        /* Hiệu ứng khi dòng bị ẩn lúc tìm kiếm */
        tr { transition: all 0.3s ease; }
    </style>
</head>
<body>
    
    <?php 
        $naviPath = _DIR_ROOT . '/views/navi.php';
        if (file_exists($naviPath)) { include_once $naviPath; }
    ?>

    <main class="main-content">
        <div class="page-header">
            <h2><i class="fas fa-user-shield"></i> QUẢN LÝ TÀI KHOẢN HỆ THỐNG</h2>
            <div class="toolbar">
                <input type="text" id="searchUser" class="search-input" placeholder="Tìm tên đăng nhập hoặc họ tên...">
                <a href="<?= _WEB_ROOT ?>/TaiKhoan_Add" class="btn-add">
                    <i class="fas fa-user-plus"></i> THÊM TÀI KHOẢN
                </a>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>Tên đăng nhập</th>
                        <th>Họ và tên</th>
                        <th>Mã nhân viên</th>
                        <th>Quyền hạn</th>
                        <th width="150" style="text-align: center;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    </tbody>
            </table>
        </div>
    </main>

    <script>
        // Thống nhất biến API_URL để dùng cho toàn bộ script
        const API_URL = '<?= _WEB_ROOT ?>/api/user'; 

        document.addEventListener("DOMContentLoaded", () => {
            loadUsers();

            // Xử lý tìm kiếm tại chỗ (Instant Search)
            document.getElementById('searchUser').addEventListener('input', function(e) {
                const keyword = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('#userTableBody tr');
                
                rows.forEach(row => {
                    const text = row.innerText.toLowerCase();
                    row.style.display = text.includes(keyword) ? '' : 'none';
                });
            });
        });

        // Hàm lấy danh sách từ server
        async function loadUsers() {
            const tableBody = document.getElementById('userTableBody');
            tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px;">' +
                                  '<i class="fas fa-spinner fa-spin"></i> Đang tải dữ liệu...</td></tr>';

            try {
                const response = await fetch(`${API_URL}/list`);
                const json = await response.json();
                const data = Array.isArray(json) ? json : (json.data || []);

                if (data.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px;">Chưa có tài khoản nào hoạt động.</td></tr>';
                    return;
                }
                renderTable(data);
            } catch (error) {
                console.error("Lỗi:", error);
                tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:red; padding: 20px;">' +
                                      'Không thể kết nối đến máy chủ!</td></tr>';
            }
        }

        // Hàm render dữ liệu ra bảng
        function renderTable(data) {
            const tableBody = document.getElementById('userTableBody');
            tableBody.innerHTML = data.map(item => {
                const isAdmin = item.role === 'admin';
                const roleClass = isAdmin ? 'role-admin' : 'role-staff';
                const roleName = isAdmin ? 'Quản trị viên' : 'Nhân viên';

                return `
                    <tr>
                        <td>#${item.id}</td>
                        <td><strong>${item.username}</strong></td>
                        <td>${item.fullname}</td>
                        <td>${item.id_nhanvien || '<span style="color:#ccc">N/A</span>'}</td>
                        <td><span class="role-badge ${roleClass}">${roleName}</span></td>
                        <td class="action-btns" style="text-align: center;">       
                            <i class="fas fa-edit btn-edit" title="Sửa thông tin" 
                               onclick="location.href='<?= _WEB_ROOT ?>/TaiKhoan_Edit?id=${item.id}'"></i>

                            <i class="fas fa-trash-alt btn-delete" title="Xóa tài khoản" 
                               onclick="deleteUser(${item.id}, '${item.username}')"></i>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Hàm xử lý Xóa (Đổi trạng thái về 0)
        async function deleteUser(id, username) {
            if (confirm(`Bạn có chắc chắn muốn xóa tài khoản "${username}" không?\nTài khoản này sẽ không thể đăng nhập vào hệ thống.`)) {
                try {
                    const response = await fetch(`${API_URL}/${id}`, {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id })
                    });
                    
                    const res = await response.json();
                    
                    if (res.status === 'success') {
                        // Thay vì load lại trang, ta gọi lại hàm loadUsers() để bảng cập nhật tức thì
                        alert('Xóa thành công!');
                        loadUsers(); 
                    } else {
                        alert('Lỗi: ' + res.message);
                    }
                } catch (error) {
                    console.error("Lỗi kết nối:", error);
                    alert("Không thể thực hiện thao tác xóa vào lúc này!");
                }
            }
        }
    </script>
</body>
</html>