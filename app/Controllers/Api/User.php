<?php
require_once _DIR_ROOT . '/app/Models/User.php';

/**
 * REST API: /api/user
 *
 * POST   /api/user  + body { _action:'login', username, password }  → login()
 * POST   /api/user  + body { _action:'logout' }                     → logout()
 * POST   /api/user  + body { username, password, id_nhanvien... }   → create()
 * GET    /api/user                   → index()  (user dang nhap)
 * GET    /api/user/list              → list()
 * GET    /api/user/nhanvien          → nhanvien()
 * GET    /api/user/{id}              → detail()
 * PUT    /api/user/{id}              → update()
 * DELETE /api/user/{id}              → delete()
 */
class User {
    private $model;
    private $body; // đọc php://input MỘT LẦN duy nhất, tái sử dụng

    public function __construct() {
        header('Content-Type: application/json');
        $this->model = new UserModel();
        // Đọc body 1 lần, lưu vào property
        $raw = file_get_contents('php://input');
        $this->body = $raw ? (json_decode($raw, true) ?? []) : [];
    }

    public function handle($method, $id = null, $extra = null) {

        if ($method === 'POST' && !$id) {
            $action = $this->body['_action'] ?? '';
            if ($action === 'login')  { $this->login();  return; }
            if ($action === 'logout') { $this->logout(); return; }
            $this->create();
            return;
        }

        switch ($method) {
            case 'GET':
                if (!$id)                { $this->index();     break; }
                if ($id === 'list')      { $this->list();      break; }
                if ($id === 'nhanvien')  { $this->nhanvien();  break; }
                $this->detail($id);
                break;
            case 'PUT':
                $this->update($id);
                break;
            case 'DELETE':
                $this->delete($id);
                break;
            default:
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Method khong duoc ho tro']);
        }
    }

    // GET /api/user — thông tin user đang đăng nhập
    private function index() {
        if (UserModel::isLoggedIn()) {
            echo json_encode(['status' => 'success', 'user' => [
                'id'          => $_SESSION['user_id'],
                'id_nhanvien' => $_SESSION['id_nhanvien'],
                'name'        => $_SESSION['user_name'],
                'role'        => $_SESSION['user_role']
            ]]);
        } else {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Chua dang nhap']);
        }
    }

    // POST + _action=login
    private function login() {
        $username = $this->body['username'] ?? '';
        $password = $this->body['password'] ?? '';

        if (!$username || !$password) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Vui long nhap tai khoan va mat khau']);
            return;
        }

        $result = $this->model->login($username, $password);
        if ($result['status'] === 'error') {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Sai tai khoan hoac mat khau']);
        } else {
            echo json_encode($result);
        }
    }

    // POST + _action=logout
    private function logout() {
        echo json_encode($this->model->logout());
    }

    // GET /api/user/list
    private function list() {
        echo json_encode(['status' => 'success', 'data' => $this->model->getAll()]);
    }

    // GET /api/user/nhanvien
    private function nhanvien() {
        echo json_encode($this->model->getNhanVienList());
    }

    // GET /api/user/{id}
    private function detail($id) {
        if (!UserModel::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Vui long dang nhap']);
            return;
        }
        $user = $this->model->getById($id);
        if ($user) {
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Tai khoan khong ton tai']);
        }
    }

    // POST (tạo tài khoản mới)
    private function create() {
        if (!UserModel::isAdmin()) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Ban khong co quyen thuc hien thao tac nay']);
            return;
        }
        $data = $this->body;
        if (empty($data['username']) || empty($data['password']) || empty($data['id_nhanvien'])) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Vui long nhap day du thong tin']);
            return;
        }
        $result = $this->model->create($data);
        if ($result['status'] === 'success') http_response_code(201);
        else http_response_code(400);
        echo json_encode($result);
    }

    // PUT /api/user/{id}
    private function update($id) {
        if (!UserModel::isAdmin()) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Khong co quyen']);
            return;
        }
        echo json_encode($this->model->update($id, $this->body));
    }

    // DELETE /api/user/{id}
    private function delete($id) {
        if (!UserModel::isAdmin()) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Khong co quyen']);
            return;
        }
        echo json_encode($this->model->changeStatus($id, 0));
    }
}