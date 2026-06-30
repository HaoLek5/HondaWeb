<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Khách hàng | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/navi.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/khachhang.css">
</head>
<body>
    
    <?php 
        // Nhúng menu bằng PHP (thay cho fetch JS để ổn định hơn)
        $naviPath = _DIR_ROOT . '/views/navi.php';
        if (file_exists($naviPath)) { include_once $naviPath; }
    ?>

    <main class="main-content">
        <div class="page-header">
            <h2><i class="fas fa-users"></i> QUẢN LÝ KHÁCH HÀNG</h2>
            <div class="toolbar">
                <input type="text" id="searchKH" class="search-input" placeholder="Tìm tên, SĐT...">
                <a href="<?= _WEB_ROOT ?>/KhachHang_Add" class="btn-add">
                    <i class="fas fa-user-plus"></i> THÊM KHÁCH HÀNG
                </a>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="80">Mã KH</th>
                        <th>Họ và tên</th>
                        <th>Số điện thoại</th>
                        <th>Địa chỉ thường trú</th>
                        <th width="100" style="text-align:center">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="khTableBody">
                    </tbody>
            </table>
        </div>
    </main>

    <script>
        let allCustomers = [];
        // Đường dẫn API chuẩn theo Controller KhachHang
        const API_URL = '<?= _WEB_ROOT ?>/api/khachhang';

        document.addEventListener("DOMContentLoaded", () => {
            loadKhachHang();

            // Xử lý tìm kiếm giữ nguyên logic cũ
            const searchInput = document.getElementById('searchKH');
            searchInput.addEventListener('input', function(e) {
                const keyword = e.target.value.toLowerCase().trim();
                
                const filteredData = allCustomers.filter(item => {
                    return (
                        item.ten_kh.toLowerCase().includes(keyword) || 
                        item.sdt.includes(keyword)
                    );
                });

                renderTable(filteredData);
            });
        });

        async function loadKhachHang() {
            const tableBody = document.getElementById('khTableBody');
            tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Đang tải dữ liệu...</td></tr>';

            try {
                // Gọi API list của KhachHang Controller
                const response = await fetch(`${API_URL}`);
                const data = await response.json();

                allCustomers = data; 
                
                if (allCustomers.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Hệ thống chưa có khách hàng nào.</td></tr>';
                    return;
                }
                renderTable(allCustomers);

            } catch (error) {
                console.error("Lỗi kết nối API:", error);
                tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:red;">Không thể kết nối với máy chủ API!</td></tr>';
            }
        }

        function renderTable(data) {
            const tableBody = document.getElementById('khTableBody');
            
            if (data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Không tìm thấy khách hàng nào khớp với từ khóa.</td></tr>';
                return;
            }

            tableBody.innerHTML = data.map(item => `
                <tr>
                    <td><strong>#${item.id}</strong></td>
                    <td><strong>${item.ten_kh}</strong></td>
                    <td>${item.sdt}</td>
                    <td>${item.dia_chi ? item.dia_chi : '<i style="color:#bbb">Chưa cập nhật</i>'}</td>
                    <td style="text-align:center">
                        <i class="fas fa-user-edit btn-edit" title="Chỉnh sửa thông tin" 
                           onclick="location.href='<?= _WEB_ROOT ?>/KhachHang_Edit?id=${item.id}'"></i>

                        <i class="fas fa-user-times" title="Xóa khách hàng" 
                            style="cursor:pointer; color: #e74c3c; margin-left: 12px;"
                            onclick="deleteCustomer(${item.id})"></i>   
                    </td>
                </tr>
            `).join('');
        }

        async function deleteCustomer(id) {
            if (!confirm(`Bạn có chắc chắn muốn xóa khách hàng #${id} khỏi hệ thống?`)) {
                return;
            }

            try {
                const response = await fetch(`${API_URL}/${id}`, { method: 'DELETE' });
                
                // Kiểm tra nếu server trả về lỗi không phải JSON (lỗi PHP)
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    console.error("Lỗi Server:", await response.text());
                    alert("Lỗi máy chủ! Vui lòng kiểm tra lại tên bảng trong Model.");
                    return;
                }

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    loadKhachHang(); // Tải lại danh sách
                } else {
                    // Thông báo lỗi nếu khách hàng đã có hóa đơn
                    alert("THÔNG BÁO: " + result.message);
                }
            } catch (error) {
                console.error("Error:", error);
                alert("Không thể kết nối đến máy chủ.");
            }
        }
    </script>
</body>
</html>