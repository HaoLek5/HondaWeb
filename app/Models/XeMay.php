<?php
class XeMayModel {
    private $conn;
    private $table = "xemay";

    public function __construct() {
        // Tự khởi tạo kết nối Database khi Model được gọi
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // LẤY TẤT CẢ SẢN PHẨM
    public function getAll() {
        $sql = "SELECT id, ten_xe, gia_ban, so_luong, hinh_anh, trang_thai 
                FROM {$this->table} 
                ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // LẤY CHI TIẾT 1 XE
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // THÊM MỚI XE
    public function create($data) {
        $sql = "INSERT INTO {$this->table}
                (ten_xe, gia_ban, hinh_anh, id_phanh, id_dong_co, id_mau, id_loai_xe, trang_thai, so_luong)
                VALUES (:ten_xe, :gia_ban, :hinh_anh, :id_phanh, :id_dong_co, :id_mau, :id_loai_xe, :trang_thai, 0)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':ten_xe' => $data['ten_xe'],
            ':gia_ban' => $data['gia_ban'],
            ':hinh_anh' => $data['hinh_anh'],
            ':id_phanh' => $data['id_phanh'],
            ':id_dong_co' => $data['id_dong_co'],
            ':id_mau' => $data['id_mau'],
            ':id_loai_xe' => $data['id_loai_xe'],
            ':trang_thai' => $data['trang_thai']
        ]);
    }

    // CẬP NHẬT XE
    public function update($id, $data) {
        $sql = "UPDATE {$this->table}
                SET ten_xe = :ten_xe, gia_ban = :gia_ban, hinh_anh = :hinh_anh,
                    id_phanh = :id_phanh, id_dong_co = :id_dong_co, id_mau = :id_mau,
                    id_loai_xe = :id_loai_xe, trang_thai = :trang_thai
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':ten_xe' => $data['ten_xe'],
            ':gia_ban' => $data['gia_ban'],
            ':hinh_anh' => $data['hinh_anh'],
            ':id_phanh' => $data['id_phanh'],
            ':id_dong_co' => $data['id_dong_co'],
            ':id_mau' => $data['id_mau'],
            ':id_loai_xe' => $data['id_loai_xe'],
            ':trang_thai' => $data['trang_thai']
        ]);
    }


    // ĐỔI TRẠNG THÁI (ẨN/HIỆN)
    public function toggleStatus($id, $current_status) {
        $new = $current_status == 1 ? 0 : 1;
        $sql = "UPDATE {$this->table} SET trang_thai = :status WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':status' => $new, ':id' => $id]);
    }

    // LẤY XE ĐANG KINH DOANH
    public function getActive() {
        $sql = "SELECT id, ten_xe, so_luong, gia_ban 
                FROM {$this->table} 
                WHERE trang_thai = 1 
                ORDER BY ten_xe ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy danh mục
    public function getCategories() {
        // Hàm này gom tất cả các danh mục vào 1 mảng duy nhất để trả về cho API
        $categories = [];

        // Lấy danh mục Loại xe
        $stmt = $this->conn->prepare("SELECT id, ten_loai FROM danhmuc_loaixe ORDER BY ten_loai ASC");
        $stmt->execute();
        $categories['loaixe'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy danh mục Hệ thống phanh
        $stmt = $this->conn->prepare("SELECT id, ten_phanh FROM danhmuc_phanh ORDER BY ten_phanh ASC");
        $stmt->execute();
        $categories['phanh'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy danh mục Động cơ
        $stmt = $this->conn->prepare("SELECT id, ten_dongco FROM danhmuc_dongco ORDER BY ten_dongco ASC");
        $stmt->execute();
        $categories['dongco'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy danh mục Màu sắc
        $stmt = $this->conn->prepare("SELECT id, ten_mau FROM danhmuc_mau ORDER BY ten_mau ASC");
        $stmt->execute();
        $categories['mau'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $categories;
    }

    // Kiểm tra trùng tên xe
    public function checkExists($ten_xe, $exclude_id = null) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE ten_xe = :ten_xe";
        
        // Nếu là cập nhật, bỏ qua ID hiện tại
        if ($exclude_id) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->conn->prepare($sql);
        $params = [':ten_xe' => $ten_xe];
        if ($exclude_id) $params[':id'] = $exclude_id;
        
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // Kiểm tra xe đã có giao dịch chưa
    public function hasTransactions($id) {
        // Kiểm tra trong bảng chi tiết phiếu nhập (ct_phieunhap)
        $sql_nhap = "SELECT COUNT(*) FROM chitiet_phieunhap WHERE id_xemay = :id";
        $stmt_nhap = $this->conn->prepare($sql_nhap);
        $stmt_nhap->execute([':id' => $id]);
        if ($stmt_nhap->fetchColumn() > 0) return true;

        // Kiểm tra trong bảng chi tiết hóa đơn (ct_hoadon) 
        // Giả sử bạn có bảng này để lưu thông tin bán xe
        $sql_xuat = "SELECT COUNT(*) FROM chitiet_hoadon WHERE id_xemay = :id";
        $stmt_xuat = $this->conn->prepare($sql_xuat);
        $stmt_xuat->execute([':id' => $id]);
        if ($stmt_xuat->fetchColumn() > 0) return true;

        return false;
    }

    // Hàm xóa gốc
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function search($q) {
    // Chỉ tìm những xe còn trong kho (so_luong > 0) và khớp tên
    $sql = "SELECT * FROM xemay 
            WHERE (ten_xe LIKE :q) 
            AND trang_thai = 1 
            AND so_luong > 0
            LIMIT 10";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([':q' => "%$q%"]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}