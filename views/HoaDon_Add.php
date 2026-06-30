<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lập hóa đơn bán hàng | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/navi.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/nhacungcap.css">
    <style>
        /* Giao diện tổng thể */
        .search-container { position: relative; width: 100%; }
        .kv-search-group { display: flex; gap: 0; margin-top: 5px; position: relative; }
        .kv-input-wrapper { position: relative; flex-grow: 1; display: flex; align-items: center; }
        .kv-search-icon { position: absolute; left: 12px; color: #999; z-index: 5; }
        .kv-input-wrapper input { width: 100%; padding: 10px 10px 10px 35px; border: 1px solid #cbd5e0; border-radius: 6px; outline: none; font-size: 14px; }
        .kv-suggestion-box { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); z-index: 9999; max-height: 300px; overflow-y: auto; display: none; border-radius: 0 0 6px 6px; }
        .kv-item { padding: 10px 15px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; border-bottom: 1px solid #edf2f7; }
        .kv-item:hover { background: #ebf8ff; }
        .kv-item .info .name { display: block; font-weight: 600; color: #2d3748; }
        .kv-item .info .subtext { font-size: 13px; color: #38a169; font-weight: bold; }
        .table input[type="number"] { width: 60px; padding: 5px; border: 1px solid #ddd; border-radius: 4px; text-align: center; }
        .btn-remove { color: #dc3545; cursor: pointer; }
        .main-content { padding: 20px; background: #f7fafc; min-height: 100vh; }
        .info-label { font-weight: bold; color: #4a5568; margin-bottom: 5px; display: block; }
    </style>
</head>
<body>
    
    <?php 
        $naviPath = _DIR_ROOT . '/views/navi.php';
        if (file_exists($naviPath)) { include_once $naviPath; }
    ?>

    <main class="main-content">
        <div class="page-header">
            <h2><i class="fas fa-shopping-cart"></i> LẬP HÓA ĐƠN MỚI</h2>
        </div>

        <div class="import-container" style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
            
            <div style="margin-bottom: 20px; padding: 10px 15px; background: #edf2f7; border-radius: 6px; display: inline-block;">
                <i class="fas fa-user-tie"></i> Nhân viên lập: <strong><?= $_SESSION['user_name'] ?? 'Chưa đăng nhập' ?></strong>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="search-container">
                    <label class="info-label">Khách hàng <span style="color:red">*</span></label>
                    <div class="kv-search-group">
                        <div class="kv-input-wrapper">
                            <i class="fas fa-search kv-search-icon"></i>
                            <input type="text" id="inputSearchKH" placeholder="Tìm tên hoặc SĐT khách hàng..." autocomplete="off">
                            <div id="suggestionBox" class="kv-suggestion-box"></div>
                        </div>
                    </div>
                    <input type="hidden" id="selectedKH_ID">
                </div>

                <div>
                    <label class="info-label">Ghi chú</label>
                    <input type="text" id="ghi_chu" style="width:100%; padding:10px; border: 1px solid #cbd5e0; border-radius: 6px; margin-top:5px;" placeholder="Nhập ghi chú hóa đơn...">
                </div>
            </div>

            <input type="hidden" id="id_nhanvien_hidden" value="<?= $_SESSION['id_nhanvien'] ?? '' ?>">

            <div style="margin-top: 25px; display: flex; gap: 10px; align-items: flex-end;">
                <div style="flex: 2; position: relative;">
                    <label class="info-label"><i class="fas fa-motorcycle"></i> Chọn xe máy cần bán <span style="color:red">*</span></label>
                    <div class="kv-input-wrapper" style="margin-top:5px;">
                        <i class="fas fa-search kv-search-icon"></i>
                        <input type="text" id="inputSearchXe" placeholder="Tìm xe máy theo tên..." autocomplete="off">
                        <div id="suggestionBoxXe" class="kv-suggestion-box"></div>
                    </div>
                    <input type="hidden" id="tempXeData">
                </div>
                <button onclick="addItem()" class="btn-add" style="height: 41px; background: #333; color: white; border: none; padding: 0 20px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                    <i class="fas fa-plus"></i> THÊM VÀO ĐƠN
                </button>
            </div>

            <table class="table" style="width:100%; margin-top: 25px; border-collapse: collapse;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th style="padding: 12px; text-align: left;">Sản phẩm</th>
                        <th width="120" style="text-align: center;">Số lượng</th>
                        <th width="180" style="text-align: right;">Đơn giá</th>
                        <th width="180" style="text-align: right;">Thành tiền</th>
                        <th width="60"></th>
                    </tr>
                </thead>
                <tbody id="cartBody">
                    <tr><td colspan="5" style="text-align:center; padding: 30px; color: #999;">Đơn hàng chưa có sản phẩm nào</td></tr>
                </tbody>
            </table>

            <div style="text-align: right; margin-top: 25px; font-size: 1.6rem; font-weight: bold; color: #cc0000;">
                TỔNG CỘNG: <span id="txtTotal">0</span> đ
            </div>

            <div style="margin-top: 30px; display: flex; gap: 10px;">
                <button id="btnConfirm" onclick="saveHoaDon()" style="background: #28a745; padding: 12px 40px; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:bold; font-size: 16px;">
                    <i class="fas fa-save"></i> LƯU & XUẤT HÓA ĐƠN
                </button>
                <button onclick="if(confirm('Bạn muốn xóa trắng dữ liệu đang nhập?')) location.reload()" style="background: #a0aec0; padding: 12px 20px; color:white; border:none; border-radius:6px; cursor:pointer;">LÀM MỚI</button>
            </div>
        </div>
    </main>

    <script>
        const BASE_URL = '<?= _WEB_ROOT ?>';
        let cart = [];

        // 1. Khởi tạo chức năng tìm kiếm
        function initSearch(inputId, boxId, apiPath, type) {
            const input = document.getElementById(inputId);
            const box = document.getElementById(boxId);

            input.addEventListener('input', async function() {
                const q = this.value.trim();
                if (q.length < 1) { box.style.display = 'none'; return; }

                try {
                    const res = await fetch(`${BASE_URL}/${apiPath}?q=${encodeURIComponent(q)}`);
                    const data = await res.json();

                    if (Array.isArray(data) && data.length > 0) {
                        box.innerHTML = data.map(item => `
                            <div class="kv-item" onclick='handleSelect(${JSON.stringify(item)}, "${type}")'>
                                <div class="info">
                                    <span class="name">${item.ten_kh || item.ten_xe}</span>
                                    <span class="subtext">${item.sdt || Number(item.gia_ban).toLocaleString() + ' đ'}</span>
                                </div>
                                <div class="addr">${item.dia_chi || 'Số lượng tồn: ' + item.so_luong}</div>
                            </div>
                        `).join('');
                        box.style.display = 'block';
                    } else {
                        box.innerHTML = '<div class="kv-item" style="color:#999">Không tìm thấy dữ liệu...</div>';
                        box.style.display = 'block';
                    }
                } catch (err) { console.error("Lỗi API Search:", err); }
            });
        }

        function handleSelect(item, type) {
            if (type === 'KH') {
                document.getElementById('inputSearchKH').value = `${item.ten_kh} - ${item.sdt}`;
                document.getElementById('selectedKH_ID').value = item.id;
            } else {
                document.getElementById('inputSearchXe').value = item.ten_xe;
                document.getElementById('tempXeData').value = JSON.stringify(item);
            }
            document.querySelectorAll('.kv-suggestion-box').forEach(b => b.style.display = 'none');
        }

        // 2. Quản lý giỏ hàng
        function addItem() {
            const raw = document.getElementById('tempXeData').value;
            if (!raw) return alert("Vui lòng tìm và chọn một mẫu xe máy!");
            
            const xe = JSON.parse(raw);
            const existing = cart.find(c => c.id_xemay === xe.id);

            if (existing) {
                existing.so_luong++;
            } else {
                cart.push({
                    id_xemay: xe.id,
                    ten_xe: xe.ten_xe,
                    gia_ban: parseInt(xe.gia_ban),
                    so_luong: 1
                });
            }
            renderCart();
            document.getElementById('inputSearchXe').value = "";
            document.getElementById('tempXeData').value = "";
            document.getElementById('inputSearchXe').focus();
        }

        function renderCart() {
            const body = document.getElementById('cartBody');
            if (cart.length === 0) {
                body.innerHTML = '<tr><td colspan="5" align="center" style="padding:30px; color:#999;">Đơn hàng chưa có sản phẩm nào</td></tr>';
                document.getElementById('txtTotal').innerText = "0";
                return;
            }
            let total = 0;
            body.innerHTML = cart.map((c, i) => {
                const tt = c.so_luong * c.gia_ban;
                total += tt;
                return `<tr>
                    <td style="padding:12px;">${c.ten_xe}</td>
                    <td align="center"><input type="number" min="1" value="${c.so_luong}" onchange="updateQty(${i}, this.value)"></td>
                    <td align="right">${c.gia_ban.toLocaleString()}</td>
                    <td align="right"><b>${tt.toLocaleString()}</b></td>
                    <td align="center"><i class="fas fa-trash btn-remove" onclick="removeItem(${i})"></i></td>
                </tr>`;
            }).join('');
            document.getElementById('txtTotal').innerText = total.toLocaleString();
        }

        function updateQty(i, v) { 
            const val = parseInt(v);
            cart[i].so_luong = (val > 0) ? val : 1; 
            renderCart(); 
        }

        function removeItem(i) { 
            if(confirm('Xóa sản phẩm này?')) {
                cart.splice(i, 1); 
                renderCart(); 
            }
        }

        // 3. HÀM LƯU HÓA ĐƠN GỬI VỀ API
        async function saveHoaDon() {
            const id_kh = document.getElementById('selectedKH_ID').value;
            const id_nv = document.getElementById('id_nhanvien_hidden').value;

            if (!id_kh) return alert("Vui lòng chọn khách hàng!");
            if (!id_nv) return alert("Không tìm thấy ID nhân viên. Vui lòng đăng nhập lại!");
            if (cart.length === 0) return alert("Đơn hàng phải có ít nhất 1 sản phẩm!");

            const btn = document.getElementById('btnConfirm');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ĐANG LƯU HÓA ĐƠN...';

            try {
                const res = await fetch(`${BASE_URL}/api/hoadon`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id_khachhang: id_kh,
                        id_nhanvien: id_nv,
                        ghi_chu: document.getElementById('ghi_chu').value,
                        items: cart.map(item => ({
                            id_xe: item.id_xemay,
                            so_luong: item.so_luong,
                            gia_ban: item.gia_ban
                        }))
                    })
                });

                const result = await res.json();
                if (result.status === 'success') {
                    alert(" Lập hóa đơn thành công!");
                    window.location.href = `${BASE_URL}/HoaDon/list`; 
                } else {
                    alert(" Lỗi: " + result.message);
                }
            } catch (e) {
                alert("Lỗi kết nối đến máy chủ!");
                console.error("Fetch error:", e);
            } finally { 
                btn.disabled = false; 
                btn.innerHTML = '<i class="fas fa-save"></i> LƯU & XUẤT HÓA ĐƠN';
            }
        }

        // Đóng suggestion box khi click ra ngoài
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-container') && !e.target.closest('.kv-input-wrapper')) {
                document.querySelectorAll('.kv-suggestion-box').forEach(b => b.style.display = 'none');
            }
        });

        // Khởi động khi trang load xong
        document.addEventListener('DOMContentLoaded', () => {
            initSearch('inputSearchKH', 'suggestionBox', 'api/khachhang', 'KH');
            initSearch('inputSearchXe', 'suggestionBoxXe', 'api/xemay', 'XE');
        });
    </script>
</body>
</html>