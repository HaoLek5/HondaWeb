<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm nhân viên mới | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/navi.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/them-sanpham.css"> 
    <style>
        .nv-form-grid { 
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
            <h2 class="form-title"><i class="fas fa-user-plus"></i> THÊM NHÂN VIÊN MỚI</h2>
            
            <form id="addNVForm">
                <div class="nv-form-grid">
                    <div class="form-item">
                        <label>Họ và tên nhân viên <span style="color:red">*</span></label>
                        <input type="text" id="ten_nv" required placeholder="Nhập tên nhân viên">
                    </div>

                    <div class="form-item">
                        <label>Số điện thoại <span style="color:red">*</span></label>
                        <input type="text" id="sdt" required placeholder="Nhập số điện thoại">
                    </div>

                    <div class="form-item full-width">
                        <label>Địa chỉ thường trú</label>
                        <input type="text" id="dia_chi" required placeholder="Số nhà, tên đường, quận/huyện...">
                    </div>

                    <div class="btn-group">
                        <a href="<?= _WEB_ROOT ?>/NhanVien" class="btn-back">HỦY BỎ</a>
                        <button type="submit" class="btn-save" id="btnSubmit">
                            <i class="fas fa-save"></i> LƯU NHÂN VIÊN
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        const API_URL = '<?= _WEB_ROOT ?>/api/nhanvien';

        // Xử lý gửi form
        document.getElementById('addNVForm').onsubmit = async (e) => {
            e.preventDefault();

            const btnSubmit = document.getElementById('btnSubmit');
            const originalText = btnSubmit.innerHTML;
            
            // Hiệu ứng Loading
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ĐANG LƯU...';
            btnSubmit.disabled = true;

            const payload = {
                ten_nv: document.getElementById('ten_nv').value.trim(),
                sdt: document.getElementById('sdt').value.trim(),
                dia_chi: document.getElementById('dia_chi').value.trim()
            };

            try {
                const res = await fetch(`${API_URL}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await res.json();

                if (result.status === 'success') {
                    alert("Thành công: " + result.message);
                    window.location.href = '<?= _WEB_ROOT ?>/NhanVien'; // Quay lại trang danh sách
                } else {
                    alert("Lỗi: " + result.message);
                }
            } catch (error) {
                console.error("Lỗi:", error);
                alert("Không thể kết nối với máy chủ API.");
            } finally {
                btnSubmit.innerHTML = originalText;
                btnSubmit.disabled = false;
            }
        };
    </script>
</body>
</html>