<?php
class PhieuNhapModel {

    private $conn;

    public function __construct() {
        // Khởi tạo kết nối database
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * TẠO PHIẾU NHẬP MỚI
     * Bao gồm: Lưu phiếu -> Lưu chi tiết -> Cập nhật số lượng trong kho xe
     */
    public function create($data) {
        try {
            $this->conn->beginTransaction();

            $id_ncc = $data['id_ncc'];
            $id_nhan_vien = $data['id_nhanvien_login']; // Lấy ID nhân viên từ API đã gán session
            $ghi_chu = $data['ghi_chu'] ?? '';
            $items = $data['items']; 

            // 1. Tính tổng tiền và kiểm tra tính hợp lệ
            $tong_tien = 0;
            foreach ($items as $item) {
                if ($item['so_luong'] <= 0) {
                    throw new Exception("Số lượng xe không hợp lệ.");
                }
                $tong_tien += $item['so_luong'] * $item['gia_nhap'];
            }

            // 2. Chèn vào bảng phieunhap
            // Lưu ý: Tên cột 'id_nhanvien' phải khớp với DB của bạn
            $sql_pn = "INSERT INTO phieunhap (id_ncc, tong_tien, ghi_chu, id_nhanvien, ngay_nhap)
                    VALUES (:id_ncc, :tong_tien, :ghi_chu, :id_nv, NOW())";
            
            $stmt_pn = $this->conn->prepare($sql_pn);
            $stmt_pn->execute([
                ':id_ncc' => $id_ncc,
                ':tong_tien' => $tong_tien,
                ':ghi_chu' => $ghi_chu,
                ':id_nv' => $id_nhan_vien
            ]);

            $id_phieunhap = $this->conn->lastInsertId();

            // 3. Chuẩn bị SQL chi tiết và cập nhật kho
            $sql_ct = "INSERT INTO chitiet_phieunhap 
                    (id_phieunhap, id_xemay, so_luong, gia_nhap, thanh_tien)
                    VALUES (:id_pn, :id_xe, :so_luong, :gia_nhap, :thanh_tien)";

            $sql_update_kho = "UPDATE xemay 
                            SET so_luong = so_luong + :so_luong 
                            WHERE id = :id";

            $stmt_ct = $this->conn->prepare($sql_ct);
            $stmt_update_kho = $this->conn->prepare($sql_update_kho);

            // 4. Duyệt mảng items
            foreach ($items as $item) {
                $thanh_tien = $item['so_luong'] * $item['gia_nhap'];

                // Lưu chi tiết
                $stmt_ct->execute([
                    ':id_pn' => $id_phieunhap,
                    ':id_xe' => $item['id_xe'], // Phải khớp với key trong file JS (addItem)
                    ':so_luong' => $item['so_luong'],
                    ':gia_nhap' => $item['gia_nhap'],
                    ':thanh_tien' => $thanh_tien
                ]);

                // Cập nhật tồn kho xe máy
                $stmt_update_kho->execute([
                    ':so_luong' => $item['so_luong'],
                    ':id' => $item['id_xe']
                ]);
            }

            $this->conn->commit();
            return ['status' => 'success', 'message' => 'Nhập kho thành công!', 'id' => $id_phieunhap];

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) { $this->conn->rollBack(); }
            return ['status' => 'error', 'message' => 'Lỗi Model: ' . $e->getMessage()];
        }
    }

    /**
     * LẤY DANH SÁCH TẤT CẢ PHIẾU NHẬP
     * Dùng cho trang quản lý chính
     */
    public function getAll() {
        // Thêm LEFT JOIN users để lấy tên nhân viên nếu cần
        $sql = "SELECT pn.*, ncc.ten_ncc, nv.ten_nv as ten_nhan_vien
                FROM phieunhap pn
                JOIN nhacungcap ncc ON pn.id_ncc = ncc.id
                LEFT JOIN nhanvien nv ON pn.id_nhanvien = nv.id
                ORDER BY pn.ngay_nhap DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * LẤY THÔNG TIN TỔNG QUÁT CỦA 1 PHIẾU
     */
    public function getInfo($id) {
        $sql = "SELECT pn.*, ncc.ten_ncc, nv.ten_nv as ten_nhan_vien
                FROM phieunhap pn
                JOIN nhacungcap ncc ON pn.id_ncc = ncc.id
                LEFT JOIN nhanvien nv ON pn.id_nhanvien = nv.id  -- Sửa lại đoạn này
                WHERE pn.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * LẤY CHI TIẾT CÁC XE TRONG PHIẾU NHẬP
     */
    public function getDetails($id) {
        $sql = "SELECT ct.*, xm.ten_xe, xm.hinh_anh
                FROM chitiet_phieunhap ct
                JOIN xemay xm ON ct.id_xemay = xm.id
                WHERE ct.id_phieunhap = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($data) {
    try {
        $this->conn->beginTransaction();

        $id_pn = $data['id'];
        $id_ncc = $data['id_ncc'];
        $ghi_chu = $data['ghi_chu'] ?? '';
        $items = $data['items']; 

        // --- BƯỚC 1: LẤY CHI TIẾT CŨ ĐỂ KIỂM TRA VÀ HOÀN TRẢ ---
        $sql_get_old = "SELECT id_xemay, so_luong FROM chitiet_phieunhap WHERE id_phieunhap = :id_pn";
        $stmt_get_old = $this->conn->prepare($sql_get_old);
        $stmt_get_old->execute([':id_pn' => $id_pn]);
        $old_items = $stmt_get_old->fetchAll(PDO::FETCH_ASSOC);

        // --- BƯỚC MỚI: KIỂM TRA LOGIC ÂM KHO ---
        foreach ($old_items as $old) {
            // Lấy tồn kho hiện tại của xe này trong bảng xemay
            $sql_check = "SELECT so_luong, ten_xe FROM xemay WHERE id = :id";
            $stmt_check = $this->conn->prepare($sql_check);
            $stmt_check->execute([':id' => $old['id_xemay']]);
            $xe = $stmt_check->fetch(PDO::FETCH_ASSOC);

            // Tìm số lượng mới của xe này trong dữ liệu gửi lên
            $item_moi = null;
            foreach ($items as $i) {
                if ($i['id_xe'] == $old['id_xemay']) {
                    $item_moi = $i;
                    break;
                }
            }
            $qty_moi = $item_moi ? $item_moi['so_luong'] : 0;

            /**
             * CÔNG THỨC VÀNG:
             * (Tồn hiện tại - Số cũ + Số mới) >= 0
             */
            if (($xe['so_luong'] - $old['so_luong'] + $qty_moi) < 0) {
                throw new Exception("Không thể sửa! Xe [{$xe['ten_xe']}] đã bán quá nhiều, không đủ tồn kho để giảm số lượng nhập.");
            }
        }

        // --- BƯỚC 2: HOÀN TRẢ KHO CŨ ---
        $sql_revert_stock = "UPDATE xemay SET so_luong = so_luong - :qty WHERE id = :id_xe";
        $stmt_revert = $this->conn->prepare($sql_revert_stock);
        foreach ($old_items as $old) {
            $stmt_revert->execute([
                ':qty' => $old['so_luong'],
                ':id_xe' => $old['id_xemay']
            ]);
        }

        // --- BƯỚC 3: XÓA CHI TIẾT CŨ ---
        $sql_del_details = "DELETE FROM chitiet_phieunhap WHERE id_phieunhap = :id_pn";
        $this->conn->prepare($sql_del_details)->execute([':id_pn' => $id_pn]);

        // --- BƯỚC 4: CẬP NHẬT PHIẾU NHẬP ---
        $tong_tien = 0;
        foreach ($items as $item) {
            $tong_tien += ($item['so_luong'] * $item['gia_nhap']);
        }

        $sql_up_pn = "UPDATE phieunhap 
                      SET id_ncc = :id_ncc, tong_tien = :tong_tien, ghi_chu = :ghi_chu 
                      WHERE id = :id_pn";
        $this->conn->prepare($sql_up_pn)->execute([
            ':id_ncc' => $id_ncc,
            ':tong_tien' => $tong_tien,
            ':ghi_chu' => $ghi_chu,
            ':id_pn' => $id_pn
        ]);

        // --- BƯỚC 5: CHÈN CHI TIẾT MỚI & CỘNG KHO MỚI ---
        $sql_ins_ct = "INSERT INTO chitiet_phieunhap (id_phieunhap, id_xemay, so_luong, gia_nhap, thanh_tien) 
                       VALUES (:id_pn, :id_xe, :so_luong, :gia_nhap, :thanh_tien)";
        $sql_add_stock = "UPDATE xemay SET so_luong = so_luong + :so_luong WHERE id = :id_xe";

        $stmt_ins_ct = $this->conn->prepare($sql_ins_ct);
        $stmt_add_stock = $this->conn->prepare($sql_add_stock);

        foreach ($items as $item) {
            $thanh_tien = $item['so_luong'] * $item['gia_nhap'];
            $stmt_ins_ct->execute([
                ':id_pn' => $id_pn,
                ':id_xe' => $item['id_xe'],
                ':so_luong' => $item['so_luong'],
                ':gia_nhap' => $item['gia_nhap'],
                ':thanh_tien' => $thanh_tien
            ]);

            $stmt_add_stock->execute([
                ':so_luong' => $item['so_luong'],
                ':id_xe' => $item['id_xe']
            ]);
        }

        $this->conn->commit();
        return ['status' => 'success', 'message' => 'Cập nhật phiếu nhập thành công!'];

    } catch (Exception $e) {
        if ($this->conn->inTransaction()) { $this->conn->rollBack(); }
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}
}