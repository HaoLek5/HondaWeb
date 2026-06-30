<?php
class HoaDonModel {
    private $conn;
    private $table = "hoadon";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Cập nhật lại hàm lấy chi tiết hóa đơn
    public function getById($id) {
        // Thêm kh.sdt và kh.dia_chi vào SELECT
        $sql = "SELECT hd.*, kh.ten_kh, kh.sdt, kh.dia_chi, nv.ten_nv 
                FROM {$this->table} hd
                LEFT JOIN khachhang kh ON hd.id_khachhang = kh.id
                LEFT JOIN nhanvien nv ON hd.id_nhanvien = nv.id
                WHERE hd.id = :id LIMIT 1";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Nên cập nhật luôn hàm getAll để danh sách hiển thị đủ thông tin nếu cần
    public function getAll() {
        $sql = "SELECT hd.*, kh.ten_kh, kh.sdt, nv.ten_nv
                FROM {$this->table} hd
                LEFT JOIN khachhang kh ON hd.id_khachhang = kh.id
                LEFT JOIN nhanvien nv ON hd.id_nhanvien = nv.id
                ORDER BY hd.id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Chi tiết hóa đơn
    public function getDetails($id_hoadon) {
        $sql = "SELECT ct.*, xm.ten_xe 
                FROM chitiet_hoadon ct
                JOIN xemay xm ON ct.id_xemay = xm.id
                WHERE ct.id_hoadon = :id_hd";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_hd' => $id_hoadon]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Tạo hóa đơn
    public function create($data) {
        try {
            $this->conn->beginTransaction();
            $items = $data['items'];
            $tong_tien = 0;

            foreach ($items as $item) {
                $sql_check = "SELECT so_luong, ten_xe FROM xemay WHERE id = :id";
                $stmt_check = $this->conn->prepare($sql_check);
                $stmt_check->execute([':id' => $item['id_xe']]);
                $xe = $stmt_check->fetch(PDO::FETCH_ASSOC);

                if (!$xe || $xe['so_luong'] < $item['so_luong']) {
                    throw new Exception("Xe '" . ($xe['ten_xe'] ?? "Không xác định") . "' không đủ hàng.");
                }
                $tong_tien += $item['so_luong'] * $item['gia_ban'];
            }

            $sql_hd = "INSERT INTO {$this->table} (id_khachhang, id_nhanvien, tong_tien, ghi_chu, ngay_lap)
                       VALUES (:id_kh, :id_nv, :tong, :ghi_chu, NOW())";
            $stmt_hd = $this->conn->prepare($sql_hd);
            $stmt_hd->execute([
                ':id_kh'    => $data['id_khachhang'],
                ':id_nv'    => $data['id_nhanvien'],
                ':tong'     => $tong_tien,
                ':ghi_chu'  => $data['ghi_chu'] ?? ''
            ]);
            $id_hoadon = $this->conn->lastInsertId();

            $sql_ct = "INSERT INTO chitiet_hoadon (id_hoadon, id_xemay, so_luong, gia_ban, thanh_tien)
                       VALUES (:id_hd, :id_xe, :sl, :gia, :thanh_tien)";
            $sql_update_kho = "UPDATE xemay SET so_luong = so_luong - :sl WHERE id = :id_xe";
            
            $stmt_ct = $this->conn->prepare($sql_ct);
            $stmt_up = $this->conn->prepare($sql_update_kho);

            foreach ($items as $item) {
                $thanh_tien = $item['so_luong'] * $item['gia_ban'];
                $stmt_ct->execute([
                    ':id_hd'     => $id_hoadon,
                    ':id_xe'     => $item['id_xe'],
                    ':sl'         => $item['so_luong'],
                    ':gia'        => $item['gia_ban'],
                    ':thanh_tien' => $thanh_tien
                ]);
                $stmt_up->execute([':sl' => $item['so_luong'], ':id_xe' => $item['id_xe']]);
            }

            $this->conn->commit();
            return ['status' => 'success', 'message' => 'Lập hóa đơn thành công!', 'id' => $id_hoadon];
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // 4. Hủy hóa đơn (Sửa lỗi tên cột id_xemay)
    public function delete($id) {
        try {
            $this->conn->beginTransaction();
            $details = $this->getDetails($id);
            
            $sql_revert = "UPDATE xemay SET so_luong = so_luong + :sl WHERE id = :id_xe";
            $stmt_revert = $this->conn->prepare($sql_revert);

            foreach ($details as $item) {
                $stmt_revert->execute([
                    ':sl'    => $item['so_luong'],
                    ':id_xe' => $item['id_xemay'] // Lưu ý: khớp với tên cột trong bảng chitiet_hoadon
                ]);
            }

            $this->conn->prepare("DELETE FROM chitiet_hoadon WHERE id_hoadon = :id")->execute([':id' => $id]);
            $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id")->execute([':id' => $id]);

            $this->conn->commit();
            return ['status' => 'success', 'message' => 'Đã hủy hóa đơn và hoàn trả kho!'];
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // 5. Thống kê (Bổ sung cho API)
    public function getRevenue() {
        $sql = "SELECT DATE(ngay_lap) as ngay, SUM(tong_tien) as doanh_thu, COUNT(id) as so_don_hang
                FROM {$this->table}
                GROUP BY DATE(ngay_lap)
                ORDER BY ngay DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}