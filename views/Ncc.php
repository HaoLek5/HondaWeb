<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Nhà cung cấp | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/navi.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/nhacungcap.css">
</head>
<body>
    
    <?php 
        // Nhúng menu theo cách của PHP MVC
        $naviPath = _DIR_ROOT . '/views/navi.php';
        if (file_exists($naviPath)) { include_once $naviPath; }
    ?>

    <main class="main-content">
        <div class="page-header">
            <h2><i class="fas fa-truck-loading"></i> QUẢN LÝ NHÀ CUNG CẤP</h2>
            <div class="toolbar">
                <input type="text" id="searchNCC" class="search-input" placeholder="Tìm tên, SĐT hoặc Email...">
                <a href="<?= _WEB_ROOT ?>/Ncc_Add" class="btn-add">
                    <i class="fas fa-plus"></i> THÊM NCC MỚI
                </a>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="80">Mã</th>
                        <th>Tên nhà cung cấp</th>
                        <th>Địa chỉ</th>
                        <th>Liên hệ</th>
                        <th>Email</th>
                        <th>Tổng tiền nhập</th>
                        <th width="120">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="nccTableBody">
                    </tbody>
            </table>
        </div>
    </main>

    <script>
        // Cấu hình đường dẫn API đồng bộ với Controller NhaCungCap.php
        const API_URL = '<?= _WEB_ROOT ?>/api/nhacungcap';

        document.addEventListener("DOMContentLoaded", () => {
            loadNhaCungCap();

            // Xử lý tìm kiếm
            document.getElementById('searchNCC').addEventListener('input', function(e) {
                const keyword = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('#nccTableBody tr');
                
                rows.forEach(row => {
                    const text = row.innerText.toLowerCase();
                    row.style.display = text.includes(keyword) ? '' : 'none';
                });
            });
        });

        // Tải danh sách từ API list()
        async function loadNhaCungCap() {
            const tableBody = document.getElementById('nccTableBody');
            tableBody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Đang tải dữ liệu...</td></tr>';

            try {
                const response = await fetch(`${API_URL}`);
                const data = await response.json(); // API trả về trực tiếp mảng dữ liệu

                if (Array.isArray(data)) {
                    if (data.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Chưa có nhà cung cấp nào.</td></tr>';
                        return;
                    }
                    renderTable(data);
                } else {
                    tableBody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:red;">Lỗi định dạng dữ liệu!</td></tr>';
                }
            } catch (error) {
                console.error("Lỗi:", error);
                tableBody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:red;">Không thể kết nối API!</td></tr>';
            }
        }

        function renderTable(data) {
            const tableBody = document.getElementById('nccTableBody');
            tableBody.innerHTML = data.map(item => {
                return `
                    <tr>
                        <td><strong>#${item.id}</strong></td>
                        <td><strong>${item.ten_ncc}</strong></td>
                        <td>${item.dia_chi || '<i>Chưa cập nhật</i>'}</td>
                        <td>${item.sdt}</td>
                        <td>${item.email}</td>
                        <td class="price-text">${Number(item.tong_nhap).toLocaleString('vi-VN')} đ</td>
                        <td class="action-btns">
                            <i class="fas fa-edit btn-edit" title="Sửa" 
                               onclick="location.href='<?= _WEB_ROOT ?>/Ncc_Edit?id=${item.id}'"></i>
                            
                            <i class="fas fa-trash-alt" title="Xóa nhà cung cấp" 
                                style="cursor:pointer; color: #e74c3c; margin-left: 10px;"
                                onclick="deleteNCC(${item.id})"></i>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        async function deleteNCC(id) {
            if (!confirm(`CẢNH BÁO: Bạn có chắc chắn muốn xóa nhà cung cấp #${id}? \nHành động này không thể hoàn tác!`)) {
                return;
            }

            try {
                const response = await fetch(`${API_URL}/${id}`, { method: 'DELETE' });
                
                // Kiểm tra nếu Server trả về lỗi PHP (HTML thay vì JSON)
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    const text = await response.text();
                    console.error("Server Error:", text);
                    alert("Lỗi máy chủ! Vui lòng kiểm tra lại tên bảng trong Model.");
                    return;
                }

                const result = await response.json();

                if (result.success) {
                    alert(result.message || "Đã xóa nhà cung cấp thành công.");
                    loadNhaCungCap(); // Tải lại bảng
                } else {
                    // Hiển thị thông báo lỗi từ Server (Ví dụ: Đã có phiếu nhập)
                    alert("KHÔNG THỂ XÓA: " + result.message);
                }
            } catch (error) {
                console.error("Lỗi kết nối:", error);
                alert("Lỗi kết nối máy chủ!");
            }
        }
    </script>
</body>
</html>