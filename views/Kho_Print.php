<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết phiếu nhập | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/navi.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/nhacungcap.css">
    <style>
        .detail-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .info-header { display: flex; justify-content: space-between; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .info-col p { margin: 8px 0; font-size: 15px; }
        .info-col strong { color: #333; }
        .total-highlight { font-size: 22px; color: #cc0000; font-weight: bold; text-align: right; margin-top: 20px; }
        
        /* CSS dành riêng cho việc IN ẤN */
        @media print {
            .main-content { margin: 0; padding: 0; }
            .navi, .btn-add, .no-print, .page-header { display: none !important; }
            .detail-card { box-shadow: none; border: none; padding: 0; }
            body { background: white; }
            .total-highlight { color: black; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <?php 
            $naviPath = _DIR_ROOT . '/views/navi.php';
            if (file_exists($naviPath)) { include_once $naviPath; }
        ?>
    </div>

    <main class="main-content">
        <div class="page-header no-print">
            <h2><i class="fas fa-file-invoice"></i> CHI TIẾT PHIẾU NHẬP</h2>
            <div style="display: flex; gap: 10px;">
                <button onclick="window.print()" class="btn-add" style="background: #2196F3;">
                    <i class="fas fa-print"></i> IN PHIẾU
                </button>
                <a href="<?= _WEB_ROOT ?>/Kho" class="btn-add" style="background:#666">
                    <i class="fas fa-arrow-left"></i> QUAY LẠI
                </a>
            </div>
        </div>

        <div class="detail-card">
            <div style="text-align: center; margin-bottom: 20px;">
                <h3 style="margin: 0; color: #9c1414;">HONDA THẮNG LỢI</h3>
                <p style="margin: 5px 0; font-size: 13px;">Địa chỉ: Số 25 Nguyễn Hoàng, Phường Mỹ Đình 2, Quận Nam Từ Liêm, Hà Nội</p>
                <h2 style="text-transform: uppercase; margin-top: 15px;">PHIẾU NHẬP KHO XE MÁY</h2>
            </div>

            <div class="info-header" id="phieuInfo">
                <div class="info-col">
                    <p>Mã phiếu: <strong id="txtMaPhieu">...</strong></p>
                    <p>Nhà cung cấp: <strong id="txtNCC">...</strong></p>
                </div>
                <div class="info-col" style="text-align: right;">
                    <p>Ngày nhập: <strong id="txtNgayNhap">...</strong></p>
                    <p>Nhân viên thực hiện: <strong id="txtNhanVien">...</strong></p>
                </div>
            </div>

            <table class="table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="border: 1px solid #ddd; padding: 12px;">STT</th>
                        <th style="border: 1px solid #ddd; padding: 12px; text-align: left;">Tên xe máy</th>
                        <th style="border: 1px solid #ddd; padding: 12px; text-align: center;">Số lượng</th>
                        <th style="border: 1px solid #ddd; padding: 12px; text-align: right;">Giá nhập</th>
                        <th style="border: 1px solid #ddd; padding: 12px; text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody id="detailTableBody">
                    </tbody>
            </table>

            <div class="total-highlight">
                TỔNG TIỀN: <span id="txtTongTien">0</span> VNĐ
            </div>
            
            <div style="margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr; text-align: center;">
                <div>
                    <strong>Người lập phiếu</strong><br>
                    <span style="font-size: 12px;">(Ký và ghi rõ họ tên)</span>
                </div>
                <div>
                    <strong>Nhà cung cấp</strong><br>
                    <span style="font-size: 12px;">(Ký và ghi rõ họ tên)</span>
                </div>
            </div>

            <div style="margin-top: 50px; color: #666; font-style: italic; font-size: 13px;" class="no-print">
                Ghi chú: <span id="txtGhiChu">---</span>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const urlParams = new URLSearchParams(window.location.search);
            const id = urlParams.get('id');
            if (id) {
                loadDetail(id);
            }
        });

        async function loadDetail(id) {
            try {
                // Thay đổi URL API khớp với Controller PhieuNhap của bạn
                const response = await fetch(`<?= _WEB_ROOT ?>/api/phieunhap/${id}`);
                const result = await response.json();

                if (result.status === 'success') {
                    const info = result.info;
                    const details = result.details;

                    // Hiển thị thông tin chung
                    document.getElementById('txtMaPhieu').innerText = '#' + info.id;
                    document.getElementById('txtNCC').innerText = info.ten_ncc;
                    document.getElementById('txtNgayNhap').innerText = info.ngay_nhap; // API nên format sẵn d/m/Y H:i
                    document.getElementById('txtNhanVien').innerText = info.ten_nhan_vien;
                    document.getElementById('txtTongTien').innerText = Number(info.tong_tien).toLocaleString('vi-VN');
                    document.getElementById('txtGhiChu').innerText = info.ghi_chu || 'Không có';

                    // Hiển thị danh sách hàng
                    const tableBody = document.getElementById('detailTableBody');
                    tableBody.innerHTML = details.map((item, index) => `
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 10px; text-align: center;">${index + 1}</td>
                            <td style="border: 1px solid #ddd; padding: 10px;"><strong>${item.ten_xe}</strong></td>
                            <td style="border: 1px solid #ddd; padding: 10px; text-align: center;">${item.so_luong}</td>
                            <td style="border: 1px solid #ddd; padding: 10px; text-align: right;">${Number(item.gia_nhap).toLocaleString('vi-VN')} đ</td>
                            <td style="border: 1px solid #ddd; padding: 10px; text-align: right; font-weight:bold;">${Number(item.thanh_tien).toLocaleString('vi-VN')} đ</td>
                        </tr>
                    `).join('');
                } else {
                    alert("Lỗi: " + result.message);
                }
            } catch (error) {
                console.error("Lỗi:", error);
                alert("Không thể kết nối máy chủ.");
            }
        }
    </script>
</body>
</html>