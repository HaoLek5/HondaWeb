<?php
class UserModel {
    private $conn;
    private $table = "users";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($username, $password) {
        try {
            $sql = "SELECT id, id_nhanvien, username, password, role, fullname 
                    FROM {$this->table} 
                    WHERE username = :user AND trang_thai = 1 LIMIT 1";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':user' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Đăng nhập thành công, lưu Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['id_nhanvien'] = $user['id_nhanvien']; 
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['fullname'];
                $_SESSION['is_logged_in'] = true;

                return [
                    'status' => 'success', 
                    'message' => 'Đăng nhập thành công!', 
                    'role' => $user['role']
                ];
            }

            return [
                'status' => 'error', 
                'message' => 'Tài khoản hoặc mật khẩu không chính xác.'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error', 
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }

    public function create($data) {
        try {
            // 🔥 Lấy tên nhân viên từ DB
            $sql_nv = "SELECT ten_nv FROM nhanvien WHERE id = :id LIMIT 1";
            $stmt_nv = $this->conn->prepare($sql_nv);
            $stmt_nv->execute([':id' => $data['id_nhanvien']]);
            $nv = $stmt_nv->fetch(PDO::FETCH_ASSOC);

            if (!$nv) {
                return ['status' => 'error', 'message' => 'Nhân viên không tồn tại'];
            }

            $sql = "INSERT INTO {$this->table} 
                    (id_nhanvien, username, password, role, fullname) 
                    VALUES (:id_nv, :user, :pass, :role, :name)";
            
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                ':id_nv' => $data['id_nhanvien'],
                ':user' => $data['username'],
                ':pass' => $hashed_password,
                ':role' => $data['role'] ?? 'nhan_vien',
                ':name' => $nv['ten_nv'] // 🔥 lấy từ bảng nhân viên
            ]);

            return $result 
                ? ['status' => 'success', 'message' => 'Tạo tài khoản thành công!'] 
                : ['status' => 'error', 'message' => 'Không thể tạo tài khoản'];

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public static function isAdmin() {
        return (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
    }

    public static function isLoggedIn() {
        return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
    }

    public function logout() {
        session_unset();
        session_destroy();
        return ['status' => 'success', 'message' => 'Đã đăng xuất.'];
    }

    public function getAll() {
    try {
        // Chỉ lấy những tài khoản có trang_thai = 1
        $sql = "SELECT id, id_nhanvien, username, role, fullname 
                FROM {$this->table} 
                WHERE trang_thai = 1
                ORDER BY id DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

public function getNhanVienList() {
    // Thêm cột sdt để người dùng dễ nhận biết nhân viên khi trùng tên
    $sql = "SELECT id, ten_nv, sdt FROM nhanvien ORDER BY ten_nv ASC";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getById($id) {
    try {
        $sql = "SELECT u.id, u.id_nhanvien, u.username, u.role, u.fullname as ten_nv, 
                       nv.sdt, u.created_at 
                FROM {$this->table} u
                LEFT JOIN nhanvien nv ON u.id_nhanvien = nv.id
                WHERE u.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        // Ép kiểu (int) để đảm bảo ID truyền vào là số sạch
        $stmt->execute([':id' => (int)$id]); 
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result : null;
    } catch (Exception $e) {
        return null;
    }
}

public function update($id, $data) {
    try {
        // 1. Kiểm tra username mới có bị trùng với người khác không
        $checkSql = "SELECT id FROM {$this->table} WHERE username = :user AND id != :id";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->execute([':user' => $data['username'], ':id' => $id]);
        if ($checkStmt->fetch()) {
            return ['status' => 'error', 'message' => 'Tên đăng nhập đã tồn tại'];
        }

        // 2. Chuẩn bị câu lệnh Update
        $sql = "UPDATE {$this->table} SET username = :user, role = :role";
        $params = [
            ':user' => $data['username'],
            ':role' => $data['role'],
            ':id'   => $id
        ];

        // 3. Nếu có nhập mật khẩu mới thì mới cập nhật password
        if (!empty($data['password'])) {
            $sql .= ", password = :pass";
            $params[':pass'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute($params);

        return $result 
            ? ['status' => 'success', 'message' => 'Cập nhật tài khoản thành công']
            : ['status' => 'error', 'message' => 'Không có thay đổi nào được thực hiện'];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

public function changeStatus($id, $status) {
    try {
        $sql = "UPDATE {$this->table} SET trang_thai = :status WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            ':status' => $status,
            ':id' => $id
        ]);
        return $result 
            ? ['status' => 'success', 'message' => 'Đã cập nhật trạng thái tài khoản']
            : ['status' => 'error', 'message' => 'Không có thay đổi nào'];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}
}