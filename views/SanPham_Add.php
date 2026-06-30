<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm sản phẩm mới | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/navi.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/them-sanpham.css">
</head>
<body>

    <?php 
        $naviPath = _DIR_ROOT . '/views/navi.php';
        if (file_exists($naviPath)) {
            include_once $naviPath;
        }
    ?>

    <main class="main-content">
        <div class="form-container">
            <h2 class="form-title">THÊM SẢN PHẨM MỚI</h2>
            
            <form id="addProductForm" enctype="multipart/form-data">
                <div class="product-form-grid">
                    <div class="form-item item-1-1">
                        <label>Tên sản phẩm</label>
                        <input type="text" id="ten_xe" name="ten_xe" required placeholder="Ví dụ: Honda SH 125i ABS">
                    </div>
                    <div class="form-item item-1-2">
                        <label>Giá bán (VNĐ)</label>
                        <input type="number" id="gia_ban" name="gia_ban" required placeholder="82000000">
                    </div>
                    <div class="form-item item-1-3">
                        <label>Loại xe</label>
                        <select id="id_loai_xe" name="id_loai_xe"></select>
                    </div>
                    <div class="form-item item-1-4">
                        <label>Hệ thống phanh</label>
                        <select id="id_phanh" name="id_phanh"></select>
                    </div>
                    <div class="form-item item-1-5">
                        <label>Động cơ</label>
                        <select id="id_dong_co" name="id_dong_co"></select>
                    </div>

                    <div class="form-item img-upload-area">
                        <label>Hình ảnh sản phẩm</label>
                        <div class="upload-box" onclick="document.getElementById('hinh_anh_file').click()">
                            <input type="file" id="hinh_anh_file" name="img" hidden accept="image/*">
                            <div id="previewContainer" style="text-align: center; color: #aaa;">
                                <i class="fas fa-camera fa-3x"></i>
                                <p style="margin-top: 10px;">Click để chọn ảnh xe</p>
                            </div>
                        </div>
                    </div>
                    <div class="form-item item-2-4">
                        <label>Màu sắc</label>
                        <select id="id_mau" name="id_mau"></select>
                    </div>
                    <div class="form-item item-2-5">
                        <label>Trạng thái kinh doanh</label>
                        <select id="trang_thai" name="trang_thai">
                            <option value="1">Đang kinh doanh</option>
                            <option value="0">Ngừng kinh doanh</option>
                        </select>
                    </div>

                    <div class="form-item item-1-6">
                        <a href="<?= _WEB_ROOT ?>/SanPham" class="btn-back">QUAY LẠI</a>
                    </div>
                    <div class="form-item item-2-6">
                        <button type="submit" class="btn-save">LƯU SẢN PHẨM</button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        const API_ROOT = '<?= _WEB_ROOT ?>/api/xemay';
        let base64Image = ""; // Biến lưu chuỗi ảnh

        // 1. Xử lý preview ảnh và chuyển sang Base64
        document.getElementById('hinh_anh_file').onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    base64Image = event.target.result; // Lưu chuỗi Base64
                    document.getElementById('previewContainer').innerHTML = 
                        `<img src="${base64Image}" style="max-height: 100%; max-width: 100%;">`;
                };
                reader.readAsDataURL(file);
            }
        };

        // 2. Load danh mục (Giữ nguyên logic của bạn)
        async function loadCategories() {
            try {
                const res = await fetch(`${API_ROOT}/cats`);
                const cats = await res.json();
                const fillSelect = (id, data, textKey) => {
                    const select = document.getElementById(id);
                    if(!select) return;
                    select.innerHTML = data.map(item => 
                        `<option value="${item.id}">${item[textKey]}</option>`
                    ).join('');
                };
                fillSelect('id_loai_xe', cats.loaixe, 'ten_loai');
                fillSelect('id_phanh', cats.phanh, 'ten_phanh');
                fillSelect('id_dong_co', cats.dongco, 'ten_dongco');
                fillSelect('id_mau', cats.mau, 'ten_mau');
            } catch (err) { console.error("Lỗi load danh mục:", err); }
        }

        // 3. Gửi dữ liệu
        document.getElementById('addProductForm').onsubmit = async (e) => {
            e.preventDefault();
            
            // Tạo object dữ liệu thay vì FormData để dễ kiểm soát
            const payload = {
                ten_xe: document.getElementById('ten_xe').value,
                gia_ban: document.getElementById('gia_ban').value,
                id_loai_xe: document.getElementById('id_loai_xe').value,
                id_phanh: document.getElementById('id_phanh').value,
                id_dong_co: document.getElementById('id_dong_co').value,
                id_mau: document.getElementById('id_mau').value,
                trang_thai: document.getElementById('trang_thai').value,
                hinh_anh: base64Image // Gửi chuỗi ảnh ở đây
            };

            try {
                const res = await fetch(`${API_ROOT}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await res.json();
                if(result.status === 'success') {
                    alert("Thêm sản phẩm thành công!");
                    window.location.href = '<?= _WEB_ROOT ?>/SanPham';
                } else {
                    alert("Lỗi: " + result.message);
                }
            } catch (error) {
                alert("Lỗi kết nối Server! Kiểm tra file XeMay api.");
            }
        };

        loadCategories();
    </script>
</body>
</html>