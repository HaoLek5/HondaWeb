<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo phiếu nhập kho | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/navi.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/nhacungcap.css">
    <style>
        .import-container { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .product-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .product-table th { background: #f8f9fa; padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6; }
        .product-table td { padding: 12px; border-bottom: 1px solid #eee; }
        .btn-remove { color: #cc0000; cursor: pointer; }
        .total-section { text-align: right; margin-top: 20px; font-size: 1.2rem; font-weight: bold; color: #cc0000; }
        select, input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }
        .btn-save { background: #28a745; border: none; cursor: pointer; color: white; padding: 12px 25px; border-radius: 6px; font-weight: bold; }
        .btn-cancel { background: #666; text-decoration: none; color: white; padding: 12px 25px; border-radius: 6px; display: flex; align-items: center; }
    </style>
</head>
<body>
    
    <?php 
        $naviPath = _DIR_ROOT . '/views/navi.php';
        if (file_exists($naviPath)) { include_once $naviPath; }
    ?>

    <main class="main-content">
        <div class="page-header">
            <h2><i class="fas fa-plus-circle"></i> TẠO PHIẾU NHẬP MỚI</h2>
        </div>

        <div class="import-container">
            <div class="info-grid">
                <div>
                    <label>Nhà cung cấp</label>
                    <select id="selectNCC"><option value="">-- Chọn nhà cung cấp --</option></select>
                </div>
                <div>
                    <label>Ghi chú</label>
                    <input type="text" id="ghi_chu" placeholder="Nhập ghi chú nếu có...">
                </div>
            
            </div>

            <hr>
            
            <div style="margin-top: 20px;">
                <h4><i class="fas fa-motorcycle"></i> Danh sách xe nhập</h4>
                <div style="display:flex; gap: 10px; margin-top: 10px;">
                    <select id="selectXe" style="flex: 2;"><option value="">-- Chọn xe máy để thêm --</option></select>
                    <button type="button" onclick="addItem()" class="btn-add" style="flex: 1; justify-content: center; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-plus"></i> THÊM VÀO PHIẾU
                    </button>
                </div>
            </div>

            <table class="product-table">
                <thead>
                    <tr>
                        <th>Tên xe máy</th>
                        <th width="150">Số lượng</th>
                        <th width="200">Giá nhập (VNĐ)</th>
                        <th width="200">Thành tiền</th>
                        <th width="50"></th>
                    </tr>
                </thead>
                <tbody id="importList"></tbody>
            </table>

            <div class="total-section">
                Tổng cộng: <span id="grandTotal">0</span> đ
            </div>

            <div style="margin-top: 30px; display: flex; gap: 10px;">
                <button class="btn-save" onclick="savePhieuNhap()">
                    <i class="fas fa-save"></i> HOÀN TẤT NHẬP KHO
                </button>
                <a href="<?= _WEB_ROOT ?>/Kho" class="btn-cancel">HỦY BỎ</a>
            </div>
        </div>
    </main>

    <script>
    let items = [];
    const API_NCC = '<?= _WEB_ROOT ?>/api/nhacungcap';
    const API_XE = '<?= _WEB_ROOT ?>/api/xemay';
    const API_SAVE = '<?= _WEB_ROOT ?>/api/phieunhap';

    document.addEventListener("DOMContentLoaded", () => {
        loadInitData();
    });

    async function loadInitData() {
        try {
            const [resNCC, resXe] = await Promise.all([
                fetch(API_NCC),
                fetch(API_XE)
            ]);

            const dataNCC = await resNCC.json();
            const dataXe = await resXe.json();

            const nccSelect = document.getElementById('selectNCC');
            dataNCC.forEach(n => {
                nccSelect.innerHTML += `<option value="${n.id}">${n.ten_ncc}</option>`;
            });

            const xeSelect = document.getElementById('selectXe');
            dataXe.forEach(x => {
                if(parseInt(x.trang_thai) === 1) {
                    xeSelect.innerHTML += `<option value="${x.id}" data-name="${x.ten_xe}">${x.ten_xe}</option>`;
                }
            });
        } catch (error) {
            console.error("Lỗi tải dữ liệu:", error);
        }
    }

    function addItem() {
        const select = document.getElementById('selectXe');
        const id = select.value;
        const name = select.options[select.selectedIndex].getAttribute('data-name');

        if(!id) return alert("Vui lòng chọn xe!");
        if(items.find(i => i.id_xe === id)) return alert("Xe này đã có trong danh sách!");

        items.push({ id_xe: id, ten_xe: name, so_luong: 1, gia_nhap: 0 });
        renderItems();
    }

    // Hàm này chỉ chạy khi Thêm/Xóa để vẽ lại cấu trúc bảng
    function renderItems() {
        const tableBody = document.getElementById('importList');
        
        if(items.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#999;">Chưa có sản phẩm nào được thêm.</td></tr>';
            updateGrandTotal();
            return;
        }

        tableBody.innerHTML = items.map((item, index) => {
            const subtotal = item.so_luong * item.gia_nhap;
            return `
                <tr data-index="${index}">
                    <td><strong>${item.ten_xe}</strong></td>
                    <td>
                        <input type="number" class="inp-qty" value="${item.so_luong}" min="1" 
                               oninput="updateRowData(${index})">
                    </td>
                    <td>
                        <input type="number" class="inp-price" value="${item.gia_nhap}" min="0" step="100000" 
                               oninput="updateRowData(${index})">
                    </td>
                    <td><strong class="row-subtotal">${subtotal.toLocaleString('vi-VN')} đ</strong></td>
                    <td><i class="fas fa-trash btn-remove" onclick="removeItem(${index})"></i></td>
                </tr>
            `;
        }).join('');
        
        updateGrandTotal();
    }

    // Hàm quan trọng: Cập nhật dữ liệu mà KHÔNG vẽ lại bảng (giữ focus)
    function updateRowData(index) {
        const row = document.querySelector(`tr[data-index="${index}"]`);
        const qty = parseInt(row.querySelector('.inp-qty').value) || 0;
        const price = parseInt(row.querySelector('.inp-price').value) || 0;

        // Cập nhật vào mảng ngầm
        items[index].so_luong = qty;
        items[index].gia_nhap = price;

        // Tính lại thành tiền của dòng và hiển thị
        const subtotal = qty * price;
        row.querySelector('.row-subtotal').innerText = subtotal.toLocaleString('vi-VN') + ' đ';

        // Tính lại tổng cộng toàn phiếu
        updateGrandTotal();
    }

    function updateGrandTotal() {
        const total = items.reduce((sum, item) => sum + (item.so_luong * item.gia_nhap), 0);
        document.getElementById('grandTotal').innerText = total.toLocaleString('vi-VN');
    }

    function removeItem(index) { 
        items.splice(index, 1); 
        renderItems(); 
    }

    async function savePhieuNhap() {
        const id_ncc = document.getElementById('selectNCC').value;
        const ghi_chu = document.getElementById('ghi_chu').value;

        if(!id_ncc) return alert("Vui lòng chọn Nhà cung cấp!");
        if(items.length === 0) return alert("Vui lòng thêm ít nhất 1 loại xe!");

        if(items.some(i => i.gia_nhap <= 0)) {
            if(!confirm("Có xe đang để giá nhập bằng 0 đ. Bạn có chắc muốn tiếp tục?")) return;
        }

        const payload = {
            id_ncc: id_ncc,
            ghi_chu: ghi_chu,
            items: items 
        };

        try {
            const res = await fetch(API_SAVE, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            
            const result = await res.json();
            if(result.status === 'success') {
                alert("Nhập kho thành công!");
                window.location.href = '<?= _WEB_ROOT ?>/Kho';
            } else {
                alert("Lỗi: " + result.message);
            }
        } catch (error) {
            alert("Lỗi kết nối hệ thống!");
        }
    }
</script>
</body>
</html>