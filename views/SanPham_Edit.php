<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa sản phẩm | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/navi.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/them-sanpham.css">
</head>
<body>

    <?php 
        $naviPath = _DIR_ROOT . '/views/navi.php';
        if (file_exists($naviPath)) { include_once $naviPath; }
    ?>

    <main class="main-content">
        <div class="form-container">
            <h2 class="form-title">CẬP NHẬT THÔNG TIN SẢN PHẨM</h2>
            
            <form id="editProductForm">
                <input type="hidden" id="product_id">

                <div class="product-form-grid">
                    <div class="form-item item-1-1">
                        <label>Tên sản phẩm</label>
                        <input type="text" id="ten_xe" required>
                    </div>
                    <div class="form-item item-1-2">
                        <label>Giá bán (VNĐ)</label>
                        <input type="number" id="gia_ban" required>
                    </div>
                    <div class="form-item item-1-3">
                        <label>Loại xe</label>
                        <select id="id_loai_xe"></select>
                    </div>
                    <div class="form-item item-1-4">
                        <label>Hệ thống phanh</label>
                        <select id="id_phanh"></select>
                    </div>
                    <div class="form-item item-1-5">
                        <label>Động cơ</label>
                        <select id="id_dong_co"></select>
                    </div>

                    <div class="form-item img-upload-area">
                        <label>Hình ảnh sản phẩm (Click để thay đổi)</label>
                        <div class="upload-box" onclick="document.getElementById('hinh_anh_file').click()">
                            <input type="file" id="hinh_anh_file" hidden accept="image/*">
                            <div id="previewContainer" style="text-align: center;">
                                </div>
                        </div>
                    </div>

                    <div class="form-item item-2-4">
                        <label>Màu sắc</label>
                        <select id="id_mau"></select>
                    </div>
                    <div class="form-item item-2-5">
                        <label>Trạng thái kinh doanh</label>
                        <select id="trang_thai">
                            <option value="1">Đang kinh doanh</option>
                            <option value="0">Ngừng kinh doanh</option>
                        </select>
                    </div>

                    <div class="form-item item-1-6">
                        <a href="<?= _WEB_ROOT ?>/SanPham" class="btn-back">HỦY BỎ</a>
                    </div>
                    <div class="form-item item-2-6">
                        <button type="submit" class="btn-save">LƯU THAY ĐỔI</button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        const API_ROOT = '<?= _WEB_ROOT ?>/api/xemay';
        const urlParams = new URLSearchParams(window.location.search);
        const productId = urlParams.get('id');
        let base64Image = ""; 

        // 1. Khởi tạo trang
        async function initPage() {
            if (!productId) {
                alert("Không tìm thấy ID sản phẩm");
                window.location.href = '<?= _WEB_ROOT ?>/SanPham';
                return;
            }

            try {
                // Bước A: Load danh mục
                const resCat = await fetch(`${API_ROOT}/cats`);
                const cats = await resCat.json();

                const fillSelect = (id, data, textKey) => {
                    const select = document.getElementById(id);
                    if(!select) return;
                    select.innerHTML = data.map(item => `<option value="${item.id}">${item[textKey]}</option>`).join('');
                };

                fillSelect('id_loai_xe', cats.loaixe, 'ten_loai');
                fillSelect('id_phanh', cats.phanh, 'ten_phanh');
                fillSelect('id_dong_co', cats.dongco, 'ten_dongco');
                fillSelect('id_mau', cats.mau, 'ten_mau');

                // Bước B: Load chi tiết sản phẩm
                await loadProductDetail();

            } catch (err) { console.error("Lỗi khởi tạo:", err); }
        }

        async function loadProductDetail() {
            const res = await fetch(`${API_ROOT}/${productId}`);
            const p = await res.json();

            if (p) {
                document.getElementById('product_id').value = p.id;
                document.getElementById('ten_xe').value = p.ten_xe;
                document.getElementById('gia_ban').value = p.gia_ban;
                document.getElementById('id_loai_xe').value = p.id_loai_xe;
                document.getElementById('id_phanh').value = p.id_phanh;
                document.getElementById('id_dong_co').value = p.id_dong_co;
                document.getElementById('id_mau').value = p.id_mau;
                document.getElementById('trang_thai').value = p.trang_thai;

                if (p.hinh_anh) {
                    base64Image = p.hinh_anh;
                    document.getElementById('previewContainer').innerHTML = 
                        `<img src="${p.hinh_anh}" style="max-width:100%; max-height:200px; object-fit:contain;">`;
                }
            }
        }

        // 2. Xử lý ảnh mới
        document.getElementById('hinh_anh_file').onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    base64Image = event.target.result;
                    document.getElementById('previewContainer').innerHTML = 
                        `<img src="${base64Image}" style="max-width:100%; max-height:200px; object-fit:contain;">`;
                };
                reader.readAsDataURL(file);
            }
        };

        // 3. Gửi Update
        document.getElementById('editProductForm').onsubmit = async (e) => {
            e.preventDefault();

            const payload = {
                id: document.getElementById('product_id').value,
                ten_xe: document.getElementById('ten_xe').value,
                gia_ban: document.getElementById('gia_ban').value,
                hinh_anh: base64Image,
                id_phanh: document.getElementById('id_phanh').value,
                id_dong_co: document.getElementById('id_dong_co').value,
                id_mau: document.getElementById('id_mau').value,
                id_loai_xe: document.getElementById('id_loai_xe').value,
                trang_thai: document.getElementById('trang_thai').value
            };

            try {
                const res = await fetch(`${API_ROOT}/${payload.id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await res.json();
                if(result.status === 'success') {
                    alert("Cập nhật thành công!");
                    window.location.href = '<?= _WEB_ROOT ?>/SanPham';
                } else {
                    alert("Lỗi: " + result.message);
                }
            } catch (error) {
                alert("Lỗi kết nối Server!");
            }
        };

        initPage();
    </script>
</body>
</html>