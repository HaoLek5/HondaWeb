<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý hóa đơn | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/navi.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/nhacungcap.css"> 
    <style>
        .container { padding: 20px; }
        .table-card { 
            background: white; 
            padding: 25px; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
        }
        .action-group {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        .btn-view {
            color: #3182ce;
            background: #ebf8ff;
            padding: 6px 10px;
            border-radius: 4px;
            transition: 0.2s;
            cursor: pointer;
        }
        .btn-view:hover { background: #bee3f8; }
        
        .btn-print-icon {
            color: #e53e3e;
            background: #fff5f5;
            padding: 6px 10px;
            border-radius: 4px;
            transition: 0.2s;
            cursor: pointer;
        }
        .btn-print-icon:hover { background: #fed7d7; }
        
        table b { color: #2d3748; }
        .total-money { color: #cc0000; font-weight: bold; }
        .status-badge {
            background: #f0fff4;
            color: #2f855a;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    
    <?php 
        $naviPath = _DIR_ROOT . '/views/navi.php';
        if (file_exists($naviPath)) { include_once $naviPath; }
    ?>

    <main class="main-content">
        <div class="page-header">
            <h2><i class="fas fa-file-invoice-dollar"></i> DANH SÁCH HÓA ĐƠN BÁN HÀNG</h2>
            <button onclick="location.href='<?= _WEB_ROOT ?>/HoaDon_Add'" class="btn-add">
                <i class="fas fa-plus"></i> LẬP HÓA ĐƠN MỚI
            </button>
        </div>

        <div class="container">
            <div class="table-card">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="100">Mã HD</th>
                            <th width="180">Ngày lập</th>
                            <th>Khách hàng</th>
                            <th>Nhân viên lập</th>
                            <th style="text-align: right;">Tổng tiền</th>
                            <th width="120" style="text-align: center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="invoiceList">
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 20px;">Đang tải dữ liệu...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        const API_URL = '<?= _WEB_ROOT ?>/api/hoadon';

        document.addEventListener("DOMContentLoaded", () => {
            loadInvoices();
        });

        async function loadInvoices() {
            try {
                const res = await fetch(`${API_URL}`);
                const data = await res.json();
                
                const tableBody = document.getElementById('invoiceList');
                
                if(Array.isArray(data)) {
                    if(data.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="6" align="center">Chưa có hóa đơn nào được lập.</td></tr>';
                        return;
                    }

                    tableBody.innerHTML = data.map(hd => `
                        <tr>
                            <td><b>#HD${hd.id}</b></td>
                            <td>${formatDateTime(hd.ngay_lap)}</td>
                            <td><strong>${hd.ten_kh}</strong></td>
                            <td><small>${hd.ten_nv}</small></td>
                            <td align="right" class="total-money">${Number(hd.tong_tien).toLocaleString('vi-VN')} đ</td>
                            <td>
                                <div class="action-group">
                                    <a href="<?= _WEB_ROOT ?>/HoaDon_Print?id=${hd.id}" class="btn-view" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                </div>
                            </td>
                        </tr>
                    `).join('');
                } else {
                    tableBody.innerHTML = '<tr><td colspan="6" align="center" style="color:red">Lỗi: ' + (data.message || "Không thể tải dữ liệu") + '</td></tr>';
                }
            } catch (err) {
                console.error(err);
                document.getElementById('invoiceList').innerHTML = '<tr><td colspan="6" align="center" style="color:red">Lỗi kết nối máy chủ API!</td></tr>';
            }
        }

        function formatDateTime(string) {
            const date = new Date(string);
            return date.toLocaleString('vi-VN');
        }

        function printInvoice(id) {
            // Mở trang in hóa đơn trong tab mới
            window.open(`<?= _WEB_ROOT ?>/HoaDon/inHoaDon?id=${id}`, '_blank');
        }
    </script>
</body>
</html>