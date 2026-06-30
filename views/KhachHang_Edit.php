<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa thông tin khách hàng | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/navi.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/them-sanpham.css"> 
    <style>
        /* Giữ nguyên style bạn đã cung cấp */
        .kh-form-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 20px 40px; 
        }
        .full-width { grid-column: span 2; }
        .btn-group { 
            display: flex; 
            gap: 15px; 
            margin-top: 30px; 
            grid-column: span 2; 
        }
        .btn-save { 
            background: #cc0000; 
            color: white; 
            border: none; 
            padding: 12px 25px; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-save:hover { background: #a30000; }
        .btn-back { 
            background: #666; 
            color: white; 
            text-decoration: none; 
            padding: 12px 25px; 
            border-radius: 8px; 
            font-weight: 600; 
            text-align: center;
        }
        input:disabled { background-color: #f5f5f5; cursor: not-allowed; color: #888; }
    </style>
</head>
<body>
    
    <?php 
        // Nhúng menu bằng PHP
        $naviPath = _DIR_ROOT . '/views/navi.php';
        if (file_exists($naviPath)) { include_once $naviPath; }
    ?>

    <main class="main-content">
        <div class="form-container">
            <h2 class="form-title"><i class="fas fa-user-edit"></i> CẬP NHẬT THÔNG TIN KHÁCH HÀNG</h2>
            
            <form id="editKHForm">
                <input type="hidden" id="kh_id">
                
                <div class="kh-form-grid">
                    <div class="form-item">
                        <label>Họ và tên <span style="color:red">*</span></label>
                        <input type="text" id="ten_kh" required placeholder="Nhập tên khách hàng">
                    </div>

                    <div class="form-item">
                        <label>Số điện thoại <span style="color:red">*</span></label>
                        <input type="text" id="sdt" required  placeholder="Nhập số điện thoại">
                    </div>

                    <div class="form-item full-width">
                        <label>Địa chỉ thường trú</label>
                        <input type="text" id="dia_chi" require placeholder="Số nhà, tên đường, phường/xã...">
                    </div>

                    <div class="btn-group">
                        <a href="<?= _WEB_ROOT ?>/KhachHang" class="btn-back">QUAY LẠI</a>
                        <button type="submit" class="btn-save" id="btnSubmit">
                            <i class="fas fa-check-circle"></i> XÁC NHẬN CẬP NHẬT
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        const API_URL = '<?= _WEB_ROOT ?>/api/khachhang';

        document.addEventListener("DOMContentLoaded", async () => {
            // 1. Lấy ID từ URL
            const urlParams = new URLSearchParams(window.location.search);
            const id = urlParams.get('id');

            if (!id) {
                alert("Không tìm thấy mã khách hàng!");
                window.location.href = '<?= _WEB_ROOT ?>/KhachHang';
                return;
            }

            // 2. Gọi API lấy dữ liệu chi tiết
            try {
                const res = await fetch(`${API_URL}/${id}`);
                const result = await res.json();

                // Kiểm tra nếu API không trả về lỗi status (vì hàm detail trả về dữ liệu trực tiếp hoặc mảng lỗi)
                if (result && !result.message) {
                    document.getElementById('kh_id').value = result.id;
                    document.getElementById('ten_kh').value = result.ten_kh;
                    document.getElementById('sdt').value = result.sdt;
                    document.getElementById('dia_chi').value = result.dia_chi || "";
                } else {
                    alert("Lỗi: " + (result.message || "Không thể tải dữ liệu"));
                    window.location.href = '<?= _WEB_ROOT ?>/KhachHang';
                }
            } catch (error) {
                console.error("Lỗi kết nối:", error);
                alert("Không thể tải thông tin khách hàng.");
            }
        });

        // 3. Xử lý gửi dữ liệu cập nhật
        document.getElementById('editKHForm').onsubmit = async (e) => {
            e.preventDefault();

            const btnSubmit = document.getElementById('btnSubmit');
            const originalText = btnSubmit.innerHTML;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ĐANG LƯU...';
            btnSubmit.disabled = true;

            const payload = {
                id: document.getElementById('kh_id').value,
                ten_kh: document.getElementById('ten_kh').value.trim(),
                sdt: document.getElementById('sdt').value.trim(),
                dia_chi: document.getElementById('dia_chi').value.trim()
            };

            try {
                const res = await fetch(`${API_URL}/${payload.id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await res.json();

                if (result.status === 'success') {
                    alert(result.message);
                    window.location.href = '<?= _WEB_ROOT ?>/KhachHang';
                } else {
                    alert("Lỗi: " + result.message);
                }
            } catch (error) {
                console.error("Lỗi:", error);
                alert("Lỗi kết nối máy chủ.");
            } finally {
                btnSubmit.innerHTML = originalText;
                btnSubmit.disabled = false;
            }
        };
    </script>
</body>
</html>