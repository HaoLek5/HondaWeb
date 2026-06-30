<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm nhà cung cấp mới | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/navi.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/them-sanpham.css"> 
    <style>
        /* Ghi đè một chút để phù hợp với NCC (không có ảnh) */
        .ncc-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px 40px;
        }
        .full-width { grid-column: span 2; }
        .input-error {
    border: 1px solid red !important;
    background-color: #fff0f0 !important;
}
    </style>
</head>
<body>

    <?php 
        // Nhúng menu bằng PHP chuẩn MVC
        $naviPath = _DIR_ROOT . '/views/navi.php';
        if (file_exists($naviPath)) { include_once $naviPath; }
    ?>

    <main class="main-content">
        <div class="form-container">
            <h2 class="form-title">THÊM NHÀ CUNG CẤP MỚI</h2>
            
            <form id="addNCCForm">
                <div class="ncc-form-grid">
                    <div class="form-item">
                        <label>Tên nhà cung cấp</label>
                        <input type="text" id="ten_ncc" required placeholder="Ví dụ: Công ty Honda Việt Nam">
                    </div>

                    <div class="form-item">
                        <label>Số điện thoại</label>
                        <input type="text" id="sdt" required placeholder="0243 xxxx xxxx" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" maxlength="10">
                    </div>

                    <div class="form-item">
                        <label>Email</label>
                        <input type="email" id="email" required placeholder="contact@honda.com.vn">
                    </div>

                    <div class="form-item full-width">
                        <label>Địa chỉ trụ sở</label>
                        <input type="text" id="dia_chi" required placeholder="Số 1, đường ABC, quận XYZ...">
                    </div>

                    <div class="form-item" style="margin-top: 20px;">
                        <a href="<?= _WEB_ROOT ?>/Ncc" class="btn-back" style="text-decoration: none; display: inline-block; text-align: center; line-height: 40px;">QUAY LẠI</a>
                    </div>
                    <div class="form-item" style="margin-top: 20px;">
                        <button type="submit" class="btn-save">LƯU NHÀ CUNG CẤP</button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        const API_URL = '<?= _WEB_ROOT ?>/api/nhacungcap';

        document.getElementById('addNCCForm').onsubmit = async (e) => {
            e.preventDefault();

            // Reset trạng thái lỗi trước đó
            document.getElementById('sdt').classList.remove('input-error');
            document.getElementById('email').classList.remove('input-error');

            const payload = {
                ten_ncc: document.getElementById('ten_ncc').value.trim(),
                sdt: document.getElementById('sdt').value.trim(),
                email: document.getElementById('email').value.trim(),
                dia_chi: document.getElementById('dia_chi').value.trim()
            };

            const btnSave = document.querySelector('.btn-save');
            const originalText = btnSave.innerHTML;
            btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ĐANG LƯU...';
            btnSave.disabled = true;

            try {
                // Gọi đến hàm add() trong Controller NhaCungCap.php
                const res = await fetch(`${API_URL}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await res.json();

                if (result.status === 'success') {
                    alert("Chúc mừng! Thêm nhà cung cấp mới thành công.");
                    window.location.href = '<?= _WEB_ROOT ?>/Ncc';
                // ... trong phần else của kết quả fetch ...
                } else {
                    // Thông báo lỗi tổng quát bằng Alert hoặc Toast
                    alert("Thanh toán thất bại: " + result.message);
                    
                    // Xử lý focus và đánh dấu ô lỗi
                    if (result.message.toLowerCase().includes("số điện thoại")) {
                        const sdtInput = document.getElementById('sdt');
                        sdtInput.classList.add('input-error');
                        sdtInput.focus();
                    } 
                    
                    if (result.message.toLowerCase().includes("email")) {
                        const emailInput = document.getElementById('email');
                        emailInput.classList.add('input-error');
                        // Nếu sdt không lỗi thì mới focus vào email
                        if (!result.message.toLowerCase().includes("số điện thoại")) {
                            emailInput.focus();
                        }
                    }
                }
            } catch (error) {
                console.error("Lỗi kết nối:", error);
                alert("Không thể kết nối đến hệ thống API!");
            } finally {
                btnSave.innerHTML = originalText;
                btnSave.disabled = false;
            }
        };
    </script>
</body>
</html>