<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Nhân viên | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/navi.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/nhacungcap.css"> </head>
<body>
    
    <?php 
        // Nhúng menu theo cách của PHP MVC
        $naviPath = _DIR_ROOT . '/views/navi.php';
        if (file_exists($naviPath)) { include_once $naviPath; }
    ?>

    <main class="main-content">
        <div class="page-header">
            <h2><i class="fas fa-user-tie"></i> QUẢN LÝ NHÂN VIÊN</h2>
            <div class="toolbar">
                <input type="text" id="searchNV" class="search-input" placeholder="Tìm tên hoặc số điện thoại nhân viên...">
                <a href="<?= _WEB_ROOT ?>/NhanVien_Add" class="btn-add">
                    <i class="fas fa-plus"></i> THÊM NHÂN VIÊN
                </a>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="80">Mã NV</th>
                        <th>Họ và tên</th>
                        <th>Số điện thoại</th>
                        <th>Địa chỉ</th>
                        <th width="120" style="text-align:center">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="nvTableBody">
                    </tbody>
            </table>
        </div>
    </main>

    <script>
        // Đường dẫn đến API Nhân viên bạn vừa tạo
        const API_URL = '<?= _WEB_ROOT ?>/api/nhanvien';

        document.addEventListener("DOMContentLoaded", () => {
            loadNhanVien();

            // Tìm kiếm nhanh tại chỗ
            document.getElementById('searchNV').addEventListener('input', function(e) {
                const keyword = e.target.value.toLowerCase().trim();
                const rows = document.querySelectorAll('#nvTableBody tr');
                
                rows.forEach(row => {
                    const text = row.innerText.toLowerCase();
                    row.style.display = text.includes(keyword) ? '' : 'none';
                });
            });
        });

        async function loadNhanVien() {
            const tableBody = document.getElementById('nvTableBody');
            tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Đang tải danh sách nhân viên...</td></tr>';

            try {
                const response = await fetch(`${API_URL}`);
                const data = await response.json(); 

                if (Array.isArray(data)) {
                    if (data.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Chưa có nhân viên nào.</td></tr>';
                        return;
                    }
                    renderTable(data);
                } else {
                    tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:red;">Lỗi định dạng dữ liệu!</td></tr>';
                }
            } catch (error) {
                console.error("Lỗi:", error);
                tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:red;">Không thể kết nối API Nhân viên!</td></tr>';
            }
        }

        function renderTable(data) {
            const tableBody = document.getElementById('nvTableBody');
            tableBody.innerHTML = data.map(item => `
                <tr>
                    <td><strong>#${item.id}</strong></td>
                    <td><strong>${item.ten_nv}</strong></td>
                    <td>${item.sdt}</td>
                    <td>${item.dia_chi || '<i>Chưa cập nhật</i>'}</td>
                    <td class="action-btns" style="text-align:center">
                        <i class="fas fa-edit btn-edit" title="Sửa thông tin" 
                           onclick="location.href='<?= _WEB_ROOT ?>/NhanVien_Edit?id=${item.id}'"></i>
                        
                        <i class="fas fa-trash-alt" title="Xóa nhân viên" 
                           style="cursor:pointer; color: #e74c3c; margin-left: 15px;"
                           onclick="deleteNhanVien(${item.id})"></i>
                    </td>
                </tr>
            `).join('');
        }

        async function deleteNhanVien(id) {
            if (!confirm(`CẢNH BÁO: Bạn có chắc muốn xóa nhân viên #${id}? \nLưu ý: Chỉ có thể xóa nhân viên chưa từng lập hóa đơn/phiếu nhập.`)) {
                return;
            }

            try {
                // Gọi API delete (Sử dụng GET hoặc POST tùy theo Route bạn thiết lập, thường là GET nếu truyền thẳng ID)
                const response = await fetch(`${API_URL}/${id}`, { method: 'DELETE' });
                
                // Kiểm tra định dạng JSON để tránh lỗi "<br />"
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    const errorText = await response.text();
                    console.error("Server Error:", errorText);
                    alert("Lỗi máy chủ! Vui lòng kiểm tra lại Model (Tên bảng/cột).");
                    return;
                }

                const result = await response.json();

                if (result.status === 'success') {
                    alert(result.message);
                    loadNhanVien(); // Tải lại danh sách
                } else {
                    // Hiển thị thông báo lỗi chi tiết từ Controller (ví dụ: nhân viên đã có giao dịch)
                    alert("KHÔNG THỂ XÓA: " + result.message);
                }
            } catch (error) {
                console.error("Lỗi kết nối:", error);
                alert("Lỗi kết nối máy chủ khi xóa!");
            }
        }
    </script>
</body>
</html>