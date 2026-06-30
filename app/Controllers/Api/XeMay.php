<?php
require_once _DIR_ROOT . '/app/Models/XeMay.php';

/**
 * REST API: /api/xemay
 *
 * GET    /api/xemay           → list()      danh sach tat ca
 * GET    /api/xemay?q=...     → search()    tim kiem
 * GET    /api/xemay/cats      → categories()
 * GET    /api/xemay/{id}      → detail()
 * POST   /api/xemay           → add()
 * PUT    /api/xemay/{id}      → update()
 * DELETE /api/xemay/{id}      → delete()
 * PATCH  /api/xemay/{id}/toggle → toggle()
 */
class XeMay {
    private $model;

    public function __construct() {
        $this->model = new XeMayModel();
    }

    /** Router chính — App gọi vào đây */
    public function handle($method, $id = null, $extra = null) {
        // PATCH /api/xemay/{id}/toggle
        if ($method === 'PATCH' && $id && $extra === 'toggle') {
            $this->toggle($id);
            return;
        }

        switch ($method) {
            case 'GET':
                if (!$id) {
                    isset($_GET['q']) ? $this->search() : $this->list();
                } elseif ($id === 'cats') {
                    $this->categories();
                } else {
                    $this->detail($id);
                }
                break;
            case 'POST':
                $this->add();
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

    private function list() {
        echo json_encode($this->model->getAll());
    }

    private function search() {
        $q = $_GET['q'] ?? '';
        echo json_encode($q ? $this->model->search($q) : []);
    }

    private function categories() {
        echo json_encode($this->model->getCategories());
    }

    private function detail($id) {
        $data = $this->model->getById($id);
        if (!$data) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Khong tim thay xe']);
            return;
        }
        echo json_encode($data);
    }

    private function add() {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Kiểm tra để trống tên
    if (empty($data['ten_xe'])) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Ten xe khong duoc de trong']);
        return;
    }

    // Kiểm tra để trống giá bán hoặc giá bán không phải là số (hoặc nhỏ hơn/bằng 0)
    if (!isset($data['gia_ban']) || !is_numeric($data['gia_ban']) || $data['gia_ban'] < 0) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Gia ban khong duoc de trong va phai la so lon hon hoac bang 0']);
        return;
    }

    if ($this->model->checkExists($data['ten_xe'])) {
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'San pham nay da co trong kho!']);
        return;
    }

    if ($this->model->create($data)) {
        http_response_code(201);
        echo json_encode(['status' => 'success', 'message' => 'Them thanh cong']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Loi he thong']);
    }
}

    private function update($id) {
        if (!$id) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Thieu ID']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['ten_xe'])) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Ten xe khong duoc de trong']);
            return;
        }
        if ($this->model->checkExists($data['ten_xe'], $id)) {
            http_response_code(409);
            header('X-Debug-Status: 409-Reached');
            echo json_encode(['status' => 'error', 'message' => 'Ten xe nay da ton tai o san pham khac!']);
            return;
        }
        if ($this->model->update($id, $data)) {
            echo json_encode(['status' => 'success', 'message' => 'Cap nhat thanh cong']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Khong the luu thay doi']);
        }
    }

    private function delete($id) {
        if (!$id) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Thieu ID']);
            return;
        }
        $xe = $this->model->getById($id);
        if (!$xe) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'San pham khong ton tai']);
            return;
        }
        if ($this->model->hasTransactions($id)) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Khong the xoa! Xe nay da co lich su giao dich.']);
            return;
        }
        if ($this->model->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Xoa san pham thanh cong']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Loi he thong']);
        }
    }

    private function toggle($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $currentStatus = $data['trang_thai'] ?? 1;
        $result = $this->model->toggleStatus($id, $currentStatus);
        echo json_encode(['success' => $result]);
    }
}
