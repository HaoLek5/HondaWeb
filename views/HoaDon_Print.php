<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>In Hóa Đơn #<?= $_GET['id'] ?? '' ?> | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: "Times New Roman", Times, serif; background: #f0f0f0; margin: 0; padding: 20px; }
        .invoice-card { background: white; width: 210mm; min-height: 297mm; padding: 20px 40px; margin: 0 auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); box-sizing: border-box; position: relative; }
        
        /* Header */
        .header-table { width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .logo-area h2 { color: #cc0000; margin: 0; font-size: 24px; text-transform: uppercase; }
        .logo-area p { margin: 2px 0; font-size: 14px; }
        
        .invoice-title { text-align: center; text-transform: uppercase; margin: 25px 0; }
        .invoice-title h1 { margin: 0; font-size: 26px; letter-spacing: 1px; }
        .invoice-title p { margin: 5px 0; font-style: italic; }

        /* Info Section */
        .info-grid { display: flex; justify-content: space-between; margin-bottom: 20px; line-height: 1.6; }
        .info-col { width: 48%; }
        .info-col strong { display: inline-block; width: 110px; }

        /* Table */
        .table-items { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table-items th { border: 1px solid #000; padding: 10px; background: #eee; text-transform: uppercase; font-size: 14px; }
        .table-items td { border: 1px solid #000; padding: 10px; font-size: 15px; }
        
        .total-section { text-align: right; margin-top: 10px; font-size: 18px; }
        .total-section b { color: #cc0000; font-size: 22px; }

        /* Footer Signature */
        .signature-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; text-align: center; margin-top: 40px; }
        .signature-item { height: 120px; }

        /* Control Bar */
        .no-print-bar { background: #333; padding: 15px; text-align: center; position: sticky; top: 0; z-index: 999; margin-bottom: 20px; border-radius: 4px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; }
        .btn-print { background: #28a745; color: white; margin-right: 10px; }
        .btn-back { background: #666; color: white; }

        @media print {
            body { background: white; padding: 0; }
            .no-print-bar { display: none !important; }
            .invoice-card { box-shadow: none; margin: 0; width: 100%; }
            .total-section b { color: black; }
        }
    </style>
</head>
<body>

<div class="no-print-bar">
    <button onclick="window.print()" class="btn btn-print"><i class="fas fa-print"></i> IN HÓA ĐƠN (Ctrl + P)</button>
    <a href="<?= _WEB_ROOT ?>/HoaDon/list" class="btn btn-back"><i class="fas fa-arrow-left"></i> QUAY LẠI</a>
</div>

<div class="invoice-card">
    <table class="header-table">
        <tr>
            <td class="logo-area">
                <h2>HONDA THẮNG LỢI</h2>
                <p>Địa chỉ: Số 25 Nguyễn Hoàng, Phường Mỹ Đình 2, Quận Nam Từ Liêm, Hà Nội</p>
                <p>Điện thoại: 024.37858888</p>
            </td>
            <td style="text-align: right; vertical-align: top;">
                <p>Mã HĐ: <b id="lbId">...</b></p>
                <p>Ngày lập: <span id="lbNgay">...</span></p>
            </td>
        </tr>
    </table>

    <div class="invoice-title">
        <h1>HÓA ĐƠN BÁN LẺ XE MÁY</h1>
        <p>(Liên giao cho khách hàng)</p>
    </div>

    <div class="info-grid">
        <div class="info-col">
            <p><strong>Khách hàng:</strong> <span id="lbKhachHang">...</span></p>
            <p><strong>Điện thoại:</strong> <span id="lbSdt">...</span></p>
            <p><strong>Địa chỉ:</strong> <span id="lbDiaChi">...</span></p>
        </div>
        <div class="info-col" style="text-align: right;">
            <p><strong>Nhân viên:</strong> <span id="lbNhanVien">...</span></p>
            <p><strong>Trạng thái:</strong> Đã thanh toán</p>
        </div>
    </div>

    <table class="table-items">
        <thead>
            <tr>
                <th width="50">STT</th>
                <th>Tên xe / Mẫu mã</th>
                <th width="80">SL</th>
                <th width="150" style="text-align: right;">Đơn giá</th>
                <th width="180" style="text-align: right;">Thành tiền</th>
            </tr>
        </thead>
        <tbody id="lbTableBody">
            </tbody>
    </table>

    <div class="total-section">
        Tổng cộng thanh toán: <b id="lbTongTien">0</b> <b>VNĐ</b>
    </div>
    
    <p style="font-style: italic; margin-top: 10px;">
        Ghi chú: <span id="lbGhiChu">...</span>
    </p>

    <div class="signature-grid">
        <div class="signature-item">
            <strong>KẾ TOÁN</strong><br>(Ký, họ tên)
        </div>
        <div class="signature-item">
            <strong>NGƯỜI LẬP PHIẾU</strong><br>(Ký, họ tên)
        </div>
        <div class="signature-item">
            <strong>KHÁCH HÀNG</strong><br>(Ký, họ tên)
        </div>
    </div>

    <div style="margin-top: 60px; text-align: center; border-top: 1px dashed #ccc; padding-top: 20px; font-size: 13px;">
        Cảm ơn Quý khách đã tin tưởng lựa chọn Honda Thắng Lợi!<br>
        <i>Vui lòng giữ hóa đơn để làm thủ tục đăng ký xe và bảo hành.</i>
    </div>
</div>

<script>
    const BASE_URL = '<?= _WEB_ROOT ?>';
    
    document.addEventListener("DOMContentLoaded", () => {
        const urlParams = new URLSearchParams(window.location.search);
        const id = urlParams.get('id');
        if (id) {
            fetchInvoiceData(id);
        } else {
            alert("Không tìm thấy mã hóa đơn!");
        }
    });

    async function fetchInvoiceData(id) {
        try {
            // Gọi đúng API bạn đã cung cấp: api/HoaDon/view?id=...
            const response = await fetch(`${BASE_URL}/api/hoadon/${id}`);
            const res = await response.json();

            if (res.status === 'success') {
                const info = res.info;
                const details = res.details;

                // Đổ dữ liệu Header
                document.getElementById('lbId').innerText = '#' + info.id;
                document.getElementById('lbNgay').innerText = info.ngay_lap;
                document.getElementById('lbKhachHang').innerText = info.ten_kh;
                document.getElementById('lbNhanVien').innerText = info.ten_nv;
                document.getElementById('lbGhiChu').innerText = info.ghi_chu || 'Không';
                document.getElementById('lbTongTien').innerText = Number(info.tong_tien).toLocaleString('vi-VN');
                document.getElementById('lbSdt').innerText = info.sdt || 'Chưa cập nhật';
                document.getElementById('lbDiaChi').innerText = info.dia_chi || 'Chưa cập nhật';

                // Dữ liệu bảng chi tiết
                const tbody = document.getElementById('lbTableBody');
                tbody.innerHTML = details.map((item, index) => `
                    <tr>
                        <td align="center">${index + 1}</td>
                        <td><b>${item.ten_xe}</b></td>
                        <td align="center">${item.so_luong}</td>
                        <td align="right">${Number(item.gia_ban).toLocaleString('vi-VN')}</td>
                        <td align="right"><b>${Number(item.thanh_tien).toLocaleString('vi-VN')}</b></td>
                    </tr>
                `).join('');

            } else {
                alert("Lỗi: " + res.message);
            }
        } catch (error) {
            console.error("Fetch Error:", error);
            alert("Không thể kết nối máy chủ để lấy dữ liệu in!");
        }
    }
</script>

</body>
</html>