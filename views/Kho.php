<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Nhập kho | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/navi.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/nhacungcap.css"> 
</head>
<body>
    
    <?php 
        // Nhúng menu bằng PHP chuẩn MVC
        $naviPath = _DIR_ROOT . '/views/navi.php';
        if (file_exists($naviPath)) { include_once $naviPath; }
    ?>

    <main class="main-content">
        <div class="page-header">
            <h2><i class="fas fa-file-import"></i> QUẢN LÝ NHẬP KHO</h2>
            <div class="toolbar">
                <input type="text" id="searchPN" class="search-input" placeholder="Tìm mã phiếu, nhà cung cấp...">
                <a href="<?= _WEB_ROOT ?>/Kho_Add" class="btn-add">
                    <i class="fas fa-plus"></i> TẠO PHIẾU NHẬP MỚI
                </a>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="80">Mã</th>
                        <th>Nhà cung cấp</th>
                        <th>Ngày nhập</th>
                        <th>Tổng tiền</th>
                        <th>Người thực hiện</th>
                        <th width="100" style="text-align:center">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="pnTableBody">
                    </tbody>
            </table>
        </div>
    </main>

    <script>
    let allReceipts = []; 
    const API_URL = '<?= _WEB_ROOT ?>/api/PhieuNhap';

    document.addEventListener("DOMContentLoaded", () => {
        loadPhieuNhap();

        document.getElementById('searchPN').addEventListener('input', function(e) {
            const keyword = e.target.value.toLowerCase().trim();
            // Thêm kiểm tra allReceipts là mảng trước khi filter
            if (!Array.isArray(allReceipts)) return;
            
            const filtered = allReceipts.filter(item => 
                (item.id && item.id.toString().includes(keyword)) || 
                (item.ten_ncc && item.ten_ncc.toLowerCase().includes(keyword))
            );
            renderTable(filtered);
        });
    });

    async function loadPhieuNhap() {
        const tableBody = document.getElementById('pnTableBody');
        tableBody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Đang tải danh sách phiếu nhập...</td></tr>';

        try {
            const response = await fetch(`${API_URL}`);
            
            // Kiểm tra nếu response không ok (lỗi 404, 500...)
            if (!response.ok) {
                throw new Error(`Server trả về lỗi: ${response.status}`);
            }

            const data = await response.json();

            // QUAN TRỌNG: Kiểm tra nếu data là mảng
            if (Array.isArray(data)) {
                allReceipts = data;
            } else if (data && data.status === 'error') {
                // Nếu API trả về dạng {"status": "error", "message": "..."}
                throw new Error(data.message || "Lỗi không xác định từ API");
            } else {
                // Trường hợp nhận được Object không mong muốn
                allReceipts = [];
                console.error("Dữ liệu không phải mảng:", data);
            }

            renderTable(allReceipts);

        } catch (error) {
            console.error("Lỗi:", error);
            tableBody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:red;">
                Lỗi: ${error.message}
            </td></tr>`;
        }
    }

    function renderTable(data) {
        const tableBody = document.getElementById('pnTableBody');
        
        if (!Array.isArray(data) || data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Không có dữ liệu hiển thị.</td></tr>';
            return;
        }

        tableBody.innerHTML = data.map(item => `
            <tr>
                <td><strong>#${item.id}</strong></td>
                <td>${item.ten_ncc || 'N/A'}</td>
                <td>${item.ngay_nhap ? new Date(item.ngay_nhap).toLocaleString('vi-VN') : '---'}</td>
                <td class="price-text" style="color: #d32f2f; font-weight: bold;">
                    ${item.tong_tien ? Number(item.tong_tien).toLocaleString('vi-VN') : 0} đ
                </td>
                <td><small>${item.ten_nhan_vien || 'Hào Lê'}</small></td>
                <td style="text-align:center">
                    <div style="display: flex; justify-content: center; gap: 15px;">
                        <i class="fas fa-file-alt" title="Xem chi tiết" 
                           style="cursor:pointer; color:#2980b9; font-size: 18px;"
                           onclick="location.href='<?= _WEB_ROOT ?>/Kho_Detail?id=${item.id}'"></i>
                        
                        <i class="fas fa-print" title="In phiếu" 
                           style="cursor:pointer; color:#27ae60; font-size: 18px;"
                           onclick="printQuick('${item.id}')"></i>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    // Hàm hỗ trợ in nhanh mà không cần mở trang chi tiết trước
    function printQuick(id) {
        // Chuyển hướng đến trang chi tiết kèm theo lệnh in tự động
        // Hoặc đơn giản là mở trang chi tiết, người dùng sẽ bấm in sau
        location.href = `<?= _WEB_ROOT ?>/Kho_Print?id=${id}&action=print`;
    }
</script>
</body>
</html>