<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa phiếu nhập | Honda Thắng Lợi</title>
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
        .btn-update { background: #007bff; border: none; cursor: pointer; color: white; padding: 12px 25px; border-radius: 6px; font-weight: bold; }
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
            <h2><i class="fas fa-edit"></i> CHỈNH SỬA PHIẾU NHẬP #<span id="labelIdPhieu"></span></h2>
        </div>

        <div class="import-container">
            <div class="info-grid">
                <div>
                    <label>Nhà cung cấp</label>
                    <select id="selectNCC"><option value="">-- Chọn nhà cung cấp --</option></select>
                </div>
                <div>
                    <label>Ghi chú</label>
                    <input type="text" id="ghi_chu" placeholder="Nhập ghi chú...">
                </div>
                <div>
                    <label>Ngày nhập</label>
                    <input type="text" id="txtNgayNhap" disabled style="background: #f4f4f4;">
                </div>
            </div>

            <hr>
            
            <div style="margin-top: 20px;">
                <h4><i class="fas fa-motorcycle"></i> Danh sách xe nhập</h4>
                <div style="display:flex; gap: 10px; margin-top: 10px;">
                    <select id="selectXe" style="flex: 2;"><option value="">-- Chọn xe máy để thêm --</option></select>
                    <button type="button" onclick="addItem()" class="btn-add" style="flex: 1; justify-content: center; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-plus"></i> THÊM XE
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
                <button class="btn-update" onclick="updatePhieuNhap()">
                    <i class="fas fa-save"></i> LƯU THAY ĐỔI
                </button>
                <a href="<?= _WEB_ROOT ?>/Kho" class="btn-cancel">QUAY LẠI</a>
            </div>
        </div>
    </main>

    <script>
    let items = [];
    let currentId = null;

    document.addEventListener("DOMContentLoaded", async () => {
        const urlParams = new URLSearchParams(window.location.search);
        currentId = urlParams.get('id');

        await loadInitData(); // Load danh mục NCC và Xe trước
        if (currentId) {
            loadCurrentPhieu(currentId); // Load dữ liệu của phiếu cần sửa
        }
    });

    async function loadInitData() {
        try {
            const [resNCC, resXe] = await Promise.all([
                fetch('<?= _WEB_ROOT ?>/api/nhacungcap'),
                fetch('<?= _WEB_ROOT ?>/api/xemay')
            ]);
            const dataNCC = await resNCC.json();
            const dataXe = await resXe.json();

            const nccSelect = document.getElementById('selectNCC');
            dataNCC.forEach(n => nccSelect.innerHTML += `<option value="${n.id}">${n.ten_ncc}</option>`);

            const xeSelect = document.getElementById('selectXe');
            dataXe.forEach(x => {
                if(parseInt(x.trang_thai) === 1) {
                    xeSelect.innerHTML += `<option value="${x.id}" data-name="${x.ten_xe}">${x.ten_xe}</option>`;
                }
            });
        } catch (e) { console.error(e); }
    }

    async function loadCurrentPhieu(id) {
        try {
            const response = await fetch(`<?= _WEB_ROOT ?>/api/phieunhap/${id}`);
            const result = await response.json();

            if (result.status === 'success') {
                document.getElementById('labelIdPhieu').innerText = id;
                document.getElementById('selectNCC').value = result.info.id_ncc;
                document.getElementById('ghi_chu').value = result.info.ghi_chu;
                document.getElementById('txtNgayNhap').value = result.info.ngay_nhap;

                // Đưa dữ liệu chi tiết vào mảng items
                items = result.details.map(d => ({
                id_xe: d.id_xemay, // Phải lấy id_xemay từ DB trả về
                ten_xe: d.ten_xe,
                so_luong: parseInt(d.so_luong),
                gia_nhap: parseInt(d.gia_nhap)
            }));
            renderItems();
            }
        } catch (e) { console.error(e); }
    }

    // --- Các hàm xử lý giao diện giống hệt trang Add ---
    function addItem() {
        const select = document.getElementById('selectXe');
        const id = select.value;
        const name = select.options[select.selectedIndex].getAttribute('data-name');
        if(!id) return alert("Chọn xe!");
        if(items.find(i => i.id_xe == id)) return alert("Đã có trong danh sách!");
        items.push({ id_xe: id, ten_xe: name, so_luong: 1, gia_nhap: 0 });
        renderItems();
    }

    function renderItems() {
        const tableBody = document.getElementById('importList');
        tableBody.innerHTML = items.map((item, index) => `
            <tr data-index="${index}">
                <td><strong>${item.ten_xe}</strong></td>
                <td><input type="number" class="inp-qty" value="${item.so_luong}" min="1" oninput="updateRowData(${index})"></td>
                <td><input type="number" class="inp-price" value="${item.gia_nhap}" min="0" oninput="updateRowData(${index})"></td>
                <td><strong class="row-subtotal">${(item.so_luong * item.gia_nhap).toLocaleString('vi-VN')} đ</strong></td>
                <td><i class="fas fa-trash btn-remove" onclick="removeItem(${index})"></i></td>
            </tr>
        `).join('');
        updateGrandTotal();
    }

    function updateRowData(index) {
        const row = document.querySelector(`tr[data-index="${index}"]`);
        items[index].so_luong = parseInt(row.querySelector('.inp-qty').value) || 0;
        items[index].gia_nhap = parseInt(row.querySelector('.inp-price').value) || 0;
        row.querySelector('.row-subtotal').innerText = (items[index].so_luong * items[index].gia_nhap).toLocaleString('vi-VN') + ' đ';
        updateGrandTotal();
    }

    function updateGrandTotal() {
        const total = items.reduce((sum, i) => sum + (i.so_luong * i.gia_nhap), 0);
        document.getElementById('grandTotal').innerText = total.toLocaleString('vi-VN');
    }

    function removeItem(index) { items.splice(index, 1); renderItems(); }

    async function updatePhieuNhap() {
        if(!confirm("Bạn có chắc chắn muốn cập nhật lại phiếu này?")) return;

        const payload = {
            id: currentId,
            id_ncc: document.getElementById('selectNCC').value,
            ghi_chu: document.getElementById('ghi_chu').value,
            items: items 
        };

        try {
            const res = await fetch(`<?= _WEB_ROOT ?>/api/phieunhap/${currentId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if(result.status === 'success') {
                alert("Cập nhật thành công!");
                window.location.href = '<?= _WEB_ROOT ?>/Kho';
            } else { alert(result.message); }
        } catch (e) { alert("Lỗi hệ thống!"); }
    }
    </script>
</body>
</html>