<?php
class KhachHangModel {

    private $conn;
    private $table = "khachhang";

    public function __construct() {
        // Tự khởi tạo kết nối Database đồng bộ với NhaCungCapModel
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Lấy tất cả khách hàng
    public function getAll() {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy chi tiết 1 khách hàng
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Thêm mới khách hàng
    public function create($data) {
        $sql = "INSERT INTO {$this->table}
                (ten_kh, sdt, dia_chi)
                VALUES (:ten_kh, :sdt, :dia_chi)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':ten_kh' => $data['ten_kh'],
            ':sdt' => $data['sdt'],
            ':dia_chi' => $data['dia_chi']
        ]);
    }

    // Cập nhật khách hàng
    public function update($id, $data) {
        $sql = "UPDATE {$this->table}
                SET ten_kh = :ten_kh,
                    sdt = :sdt,
                    dia_chi = :dia_chi
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':ten_kh' => $data['ten_kh'],
            ':sdt' => $data['sdt'],
            ':dia_chi' => $data['dia_chi']
        ]);
    }

    // Kiểm tra trùng lặp SĐT hoặc Email (Giúp API trả về lỗi chính xác)
    public function isExists($sdt, $exclude_id = null) {
        $sql = "SELECT id FROM {$this->table} WHERE (sdt = :sdt)";
        $params = [':sdt' => $sdt];

        if ($exclude_id) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $exclude_id;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ? true : false;
    }

    // Kiểm tra khách hàng đã có hóa đơn nào chưa
    public function hasInvoices($id) {
        // Giả sử bảng hóa đơn của bạn là 'hoadon' và cột lưu mã khách là 'id_khachhang'
        $sql = "SELECT COUNT(*) FROM hoadon WHERE id_khachhang = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    // Xóa khách hàng
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function search($q) {
    // Tìm theo tên hoặc số điện thoại
    $sql = "SELECT * FROM khachhang 
            WHERE ten_kh LIKE :q 
            OR sdt LIKE :q 
            LIMIT 10"; // Giới hạn 10 kết quả cho nhanh
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([':q' => "%$q%"]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}