<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa thông tin nhân viên | Honda Thắng Lợi</title>
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
        input:disabled { background-color: #f5f5f5; cursor: not-allowed; color: #888; }
    </style>
</head>
<body>
    
    <?php 
        $naviPath = _DIR_ROOT . '/views/navi.php';
        if (file_exists($naviPath)) { include_once $naviPath; }
    ?>

    <main class="main-content">
        <div class="form-container">
            <h2 class="form-title"><i class="fas fa-user-edit"></i> CẬP NHẬT THÔNG TIN NHÂN VIÊN</h2>
            
            <form id="editNVForm">
                <input type="hidden" id="nv_id">
                
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
                        <a href="<?= _WEB_ROOT ?>/NhanVien" class="btn-back">QUAY LẠI</a>
                        <button type="submit" class="btn-save" id="btnSubmit">
                            <i class="fas fa-check-circle"></i> XÁC NHẬN CẬP NHẬT
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        const API_URL = '<?= _WEB_ROOT ?>/api/nhanvien';

        document.addEventListener("DOMContentLoaded", async () => {
            const urlParams = new URLSearchParams(window.location.search);
            const id = urlParams.get('id');

            if (!id) {
                alert("Không tìm thấy mã nhân viên!");
                window.location.href = '<?= _WEB_ROOT ?>/NhanVien';
                return;
            }

            try {
                // Gọi endpoint view($id) của NhanVien API
                const res = await fetch(`${API_URL}/${id}`);
                const result = await res.json();

                if (result.status === 'success') {
                    const data = result.data;
                    document.getElementById('nv_id').value = data.id;
                    document.getElementById('ten_nv').value = data.ten_nv;
                    document.getElementById('sdt').value = data.sdt;
                    document.getElementById('dia_chi').value = data.dia_chi || "";
                } else {
                    alert("Lỗi: " + result.message);
                    window.location.href = '<?= _WEB_ROOT ?>/NhanVien';
                }
            } catch (error) {
                console.error("Lỗi kết nối:", error);
                alert("Không thể tải thông tin nhân viên.");
            }
        });

        document.getElementById('editNVForm').onsubmit = async (e) => {
            e.preventDefault();

            const btnSubmit = document.getElementById('btnSubmit');
            const originalText = btnSubmit.innerHTML;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ĐANG LƯU...';
            btnSubmit.disabled = true;

            const id = document.getElementById('nv_id').value;
            const payload = {
                ten_nv: document.getElementById('ten_nv').value.trim(),
                sdt: document.getElementById('sdt').value.trim(),
                dia_chi: document.getElementById('dia_chi').value.trim()
            };

            try {
                // Gọi endpoint update($id) của NhanVien API
                const res = await fetch(`${API_URL}/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await res.json();

                if (result.status === 'success') {
                    alert(result.message);
                    window.location.href = '<?= _WEB_ROOT ?>/NhanVien';
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