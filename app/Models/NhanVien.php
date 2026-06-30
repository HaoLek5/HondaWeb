<?php
class NhanVienModel {
    private $conn;

    public function __construct() {
        // Khởi tạo kết nối database đồng bộ với hệ thống
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // 1. Lấy danh sách tất cả nhân viên
    public function getAll() {
        $sql = "SELECT id, ten_nv, sdt, dia_chi FROM nhanvien ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Lấy thông tin chi tiết 1 nhân viên
    public function getById($id) {
        $sql = "SELECT * FROM nhanvien WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. Thêm nhân viên mới
    public function create($data) {
        try {
            $sql = "INSERT INTO nhanvien (ten_nv, sdt, dia_chi) 
                    VALUES (:ten, :sdt, :dc)";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                ':ten' => $data['ten_nv'],
                ':sdt' => $data['sdt'],
                ':dc'  => $data['dia_chi']
            ]);
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }

    // 4. Cập nhật thông tin nhân viên
    public function update($id, $data) {
        try {
            $sql = "UPDATE nhanvien 
                    SET ten_nv = :ten, sdt = :sdt, dia_chi = :dc 
                    WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':id'  => $id,
                ':ten' => $data['ten_nv'],
                ':sdt' => $data['sdt'],
                ':dc'  => $data['dia_chi']
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    // Kiểm tra nhân viên đã lập hóa đơn hoặc phiếu nhập nào chưa
    public function hasWorkHistory($id) {
        // 1. Kiểm tra trong bảng hóa đơn (người bán hàng)
        $sql_hd = "SELECT COUNT(*) FROM hoadon WHERE id_nhanvien = :id";
        $stmt_hd = $this->conn->prepare($sql_hd);
        $stmt_hd->execute([':id' => $id]);
        if ($stmt_hd->fetchColumn() > 0) return true;

        // 2. Kiểm tra trong bảng phiếu nhập (người nhập hàng)
        $sql_pn = "SELECT COUNT(*) FROM phieunhap WHERE id_nhanvien = :id";
        $stmt_pn = $this->conn->prepare($sql_pn);
        $stmt_pn->execute([':id' => $id]);
        if ($stmt_pn->fetchColumn() > 0) return true;

        $sql_users = "SELECT COUNT(*) FROM users WHERE id_nhanvien = :id";
        $stmt_users = $this->conn->prepare($sql_users);
        $stmt_users->execute([':id' => $id]);
        if ($stmt_users->fetchColumn() > 0) return true;

        return false;
    }

    // Xóa nhân viên
    public function delete($id) {
        $sql = "DELETE FROM nhanvien WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}