<?php
class NhaCungCapModel {

    private $conn;
    private $table = "nhacungcap";

    public function __construct() {
        // Tự khởi tạo kết nối Database giống bên XeMayModel
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Lấy tất cả + tổng tiền nhập
    public function getAll() {
        $sql = "SELECT n.id, n.ten_ncc, n.dia_chi, n.sdt, n.email,
                       COALESCE(SUM(p.tong_tien), 0) as tong_nhap
                FROM {$this->table} n
                LEFT JOIN phieunhap p ON n.id = p.id_ncc
                GROUP BY n.id
                ORDER BY n.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy chi tiết 1 nhà cung cấp
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Thêm mới
    public function create($data) {
        $sql = "INSERT INTO {$this->table}
                (ten_ncc, dia_chi, sdt, email)
                VALUES (:ten_ncc, :dia_chi, :sdt, :email)";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':ten_ncc' => $data['ten_ncc'],
            ':dia_chi' => $data['dia_chi'],
            ':sdt' => $data['sdt'],
            ':email' => $data['email']
        ]);
    }

    // Cập nhật
    public function update($id, $data) {
        $sql = "UPDATE {$this->table}
                SET ten_ncc = :ten_ncc,
                    dia_chi = :dia_chi,
                    sdt = :sdt,
                    email = :email
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':ten_ncc' => $data['ten_ncc'],
            ':dia_chi' => $data['dia_chi'],
            ':sdt' => $data['sdt'],
            ':email' => $data['email'],
        ]);
    }

    // Kiểm tra nhà cung cấp đã có phiếu nhập nào chưa
    public function hasImports($id) {
        // Tên bảng giả định là phieunhap, cột là id_ncc. Bạn hãy chỉnh lại cho đúng DB của mình
        $sql = "SELECT COUNT(*) FROM phieunhap WHERE id_ncc = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    // Xóa vĩnh viễn
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // Kiểm tra trùng SDT
    public function checkPhone($sdt, $exclude_id = null) {
        $sql = "SELECT id FROM {$this->table} WHERE sdt = :sdt";
        $params = [':sdt' => $sdt]; // Khởi tạo mảng params với sdt trước

        if ($exclude_id) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $exclude_id; // Chỉ thêm vào khi có ID loại trừ
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params); // Bây giờ số lượng biến sẽ khớp với số lượng dấu : trong SQL
        return $stmt->fetch() ? true : false;
    }

    // Kiểm tra trùng Email
    public function checkEmail($email, $exclude_id = null) {
        if (empty($email)) return false;
        
        $sql = "SELECT id FROM {$this->table} WHERE email = :email";
        $params = [':email' => $email];

        if ($exclude_id) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $exclude_id;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ? true : false;
    }
}