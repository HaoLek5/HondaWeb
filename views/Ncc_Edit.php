<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa nhà cung cấp | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/navi.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/them-sanpham.css"> 
    <style>
        .ncc-form-grid { 
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
            line-height: normal;
        }
        input:disabled { background-color: #f4f4f4; cursor: not-allowed; }
        .loading-overlay { text-align: center; padding: 50px; font-style: italic; color: #666; }
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
            <h2 class="form-title"><i class="fas fa-edit"></i> CẬP NHẬT NHÀ CUNG CẤP</h2>
            
            <div id="loading" class="loading-overlay">
                <i class="fas fa-spinner fa-spin"></i> Đang tải thông tin nhà cung cấp...
            </div>

            <form id="editNCCForm" style="display: none;">
                <input type="hidden" id="ncc_id"> 
                
                <div class="ncc-form-grid">
                    <div class="form-item">
                        <label>Tên nhà cung cấp <span style="color:red">*</span></label>
                        <input type="text" id="ten_ncc" required placeholder="Nhập tên nhà cung cấp">
                    </div>

                    <div class="form-item">
                        <label>Số điện thoại <span style="color:red">*</span></label>
                        <input type="text" id="sdt" required placeholder="Số điện thoại liên hệ">
                    </div>

                    <div class="form-item">
                        <label>Email</label>
                        <input type="email" id="email" placeholder="example@honda.com">
                    </div>

                    <div class="form-item full-width">
                        <label>Địa chỉ trụ sở</label>
                        <input type="text" id="dia_chi" placeholder="Địa chỉ đầy đủ của nhà cung cấp">
                    </div>

                    <div class="btn-group">
                        <a href="<?= _WEB_ROOT ?>/Ncc" class="btn-back">QUAY LẠI</a>
                        <button type="submit" class="btn-save" id="btnSubmit">
                            <i class="fas fa-save"></i> LƯU THAY ĐỔI
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        const API_URL = '<?= _WEB_ROOT ?>/api/nhacungcap';

        document.addEventListener("DOMContentLoaded", async () => {
            // Lấy ID từ URL
            const urlParams = new URLSearchParams(window.location.search);
            const id = urlParams.get('id');

            if (!id) {
                alert("Mã nhà cung cấp không hợp lệ!");
                window.location.href = '<?= _WEB_ROOT ?>/Ncc';
                return;
            }

            try {
                // Gọi API detail($id) trong Controller
                const res = await fetch(`${API_URL}/${id}`);
                const data = await res.json();

                if (data && data.id) {
                    // Đổ dữ liệu vào form
                    document.getElementById('ncc_id').value = data.id;
                    document.getElementById('ten_ncc').value = data.ten_ncc;
                    document.getElementById('sdt').value = data.sdt;
                    document.getElementById('email').value = data.email || "";
                    document.getElementById('dia_chi').value = data.dia_chi || "";

                    // Hiện form, ẩn loading
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('editNCCForm').style.display = 'block';
                } else {
                    alert("Lỗi: " + (data.message || "Không tìm thấy dữ liệu"));
                    window.location.href = '<?= _WEB_ROOT ?>/Ncc';
                }
            } catch (error) {
                console.error("Lỗi tải dữ liệu:", error);
                alert("Không thể kết nối API để lấy thông tin!");
            }
        });

        // Xử lý gửi dữ liệu cập nhật
        document.getElementById('editNCCForm').onsubmit = async (e) => {
            e.preventDefault();

            const btnSubmit = document.getElementById('btnSubmit');
            const originalText = btnSubmit.innerHTML;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ĐANG LƯU...';
            btnSubmit.disabled = true;

            const payload = {
                id: document.getElementById('ncc_id').value,
                ten_ncc: document.getElementById('ten_ncc').value.trim(),
                sdt: document.getElementById('sdt').value.trim(),
                email: document.getElementById('email').value.trim(),
                dia_chi: document.getElementById('dia_chi').value.trim()
            };

            try {
                // Gọi API update() trong Controller (Phương thức POST)
                const res = await fetch(`${API_URL}/${payload.id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await res.json();

                if (result.status === 'success') {
                    alert("Cập nhật thông tin thành công!");
                    window.location.href = '<?= _WEB_ROOT ?>/Ncc';
                } else {
                    alert("Lỗi: " + result.message);
                }
            } catch (error) {
                console.error("Lỗi cập nhật:", error);
                alert("Lỗi kết nối máy chủ khi lưu dữ liệu.");
            } finally {
                btnSubmit.innerHTML = originalText;
                btnSubmit.disabled = false;
            }
        };
    </script>
</body>
</html>