<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/navi.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/sanpham.css">
</head>
<body>

    <?php 
        // Giữ nguyên _DIR_ROOT cho PHP include
        $naviPath = _DIR_ROOT . '/views/navi.php';
        if (file_exists($naviPath)) {
            include_once $naviPath;
        } else {
            echo "<div style='color:red; padding:20px;'>Cảnh báo: Không tìm thấy file views/navi.php</div>";
        }
    ?>

    <main class="main-content">
        <div class="page-header">
            <h2>DANH SÁCH XE MÁY</h2>
            <div class="toolbar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Tìm tên xe...">
                </div>
                <div class="filter-box">
                    <select id="statusFilter" class="filter-select">
                        <option value="1">Đang kinh doanh</option>
                        <option value="0">Ngừng kinh doanh</option>
                        <option value="all">Tất cả sản phẩm</option>
                    </select>
                </div>
                <button class="btn-add" onclick="window.location.href='<?= _WEB_ROOT ?>/SanPham_Add'">
                    <i class="fas fa-plus"></i> Thêm xe mới
                </button>
            </div>
        </div>

        <div class="product-grid" id="productGrid">
            <p style="text-align: center; padding: 50px;">Đang tải dữ liệu...</p>
        </div>
    </main>

    <script>
        // 1. Cấu hình hằng số (Dùng _WEB_ROOT)
        const API_URL = '<?= _WEB_ROOT ?>/api/xemay';
        let allProducts = [];

        document.addEventListener('DOMContentLoaded', () => {
            const activeMenu = document.getElementById('menu-sanpham');
            if(activeMenu) activeMenu.classList.add('active');
            fetchProducts();
        });

        async function fetchProducts() {
            try {
                // Gọi tới api/xemay
                const response = await fetch(`${API_URL}`);
                if (!response.ok) throw new Error('404 hoặc 500');
                
                const result = await response.json();
                allProducts = Array.isArray(result) ? result : (result.data || []);
                applyFilters();
            } catch (error) {
                console.error("Error:", error);
                document.getElementById('productGrid').innerHTML = 
                    "<p class='error-msg' style='grid-column: 1/-1; text-align: center; color: red;'>Lỗi kết nối API: " + API_URL + "/list</p>";
            }
        }

        function applyFilters() {
            const keyword = document.getElementById('searchInput').value.toLowerCase().trim();
            const statusVal = document.getElementById('statusFilter').value;

            const filtered = allProducts.filter(p => {
                const matchName = p.ten_xe.toLowerCase().includes(keyword);
                const matchStatus = (statusVal === 'all') || (p.trang_thai.toString() === statusVal);
                return matchName && matchStatus;
            });
            renderProducts(filtered);
        }

        function renderProducts(data) {
            const grid = document.getElementById('productGrid');
            if (!data || data.length === 0) {
                grid.innerHTML = "<p style='grid-column: 1/-1; text-align: center; padding: 50px; color: #888;'>Không có sản phẩm.</p>";
                return;
            }

            grid.innerHTML = data.map(item => {
                const isVisible = parseInt(item.trang_thai) !== 0; 
                const price = Number(item.gia_ban).toLocaleString('vi-VN');
                
                return `
                    <div class="product-card ${isVisible ? '' : 'inactive'}">
                        <div class="img-container">
                            <img src="${item.hinh_anh}" 
                     class="product-img" 
                     onerror="this.src='https://via.placeholder.com/240x180?text=Honda+Bike'">
            </div>
                        <div class="product-info">
                            <h3>${item.ten_xe}</h3>
                            <p class="product-price">${price} đ</p>
                            <p>Kho: <strong>${item.so_luong}</strong> chiếc</p>
                        </div>
                        <div class="card-footer">
                            <span class="status-badge ${parseInt(item.so_luong) > 0 ? 'status-instock' : 'status-outstock'}">
                                ${parseInt(item.so_luong) > 0 ? 'Sẵn hàng' : 'Hết hàng'}
                            </span>
                            <div class="action-icons">
                                <i class="fas fa-edit" 
                                   onclick="window.location.href='<?= _WEB_ROOT ?>/SanPham_Edit?id=${item.id}'"></i>
                               
                                <i class="fas ${isVisible ? 'fa-eye' : 'fa-eye-slash'}" 
                                title="${isVisible ? 'Ẩn sản phẩm' : 'Hiện sản phẩm'}"
                                onclick="toggleStatus(${item.id}, ${item.trang_thai})">
                                </i>
                                <i class="fas fa-trash-alt" 
                                   onclick="deleteProduct(${item.id})"></i>
                            </div>
                        </div>
                    </div>`;
            }).join('');
        }

        async function deleteProduct(id) {
        if (!confirm("CẢNH BÁO: Bạn có chắc chắn muốn xóa vĩnh viễn sản phẩm này không? Hành động này không thể hoàn tác!")) {
            return;
        }

        try {
            const response = await fetch(`${API_URL}/${id}`, { method: 'DELETE' });
            const result = await response.json();

            if (result.status === 'success') {
                alert(result.message);
                fetchProducts(); // Tải lại danh sách sau khi xóa
            } else {
                alert("Lỗi: " + result.message);
            }
        } catch (error) {
            console.error("Error:", error);
            alert("Không thể kết nối đến máy chủ để xóa!");
        }
    }

        async function toggleStatus(id, currentStatus) {
            if (!confirm("Xác nhận đổi trạng thái?")) return;
            try {
                const response = await fetch(`${API_URL}/${id}/toggle`, { method: 'PATCH', headers: {'Content-Type':'application/json'}, body: JSON.stringify({trang_thai: currentStatus}) });
                const result = await response.json();
                if(result.success || result.status === "success") fetchProducts();
            } catch (error) { alert("Lỗi kết nối API!"); }
        }

        document.getElementById('searchInput').addEventListener('input', applyFilters);
        document.getElementById('statusFilter').addEventListener('change', applyFilters);
    </script>
</body>
</html>