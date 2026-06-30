<?php
require_once _DIR_ROOT . '/app/Models/NhanVien.php';

/**
 * REST API: /api/nhanvien
 *
 * GET    /api/nhanvien        → list()
 * GET    /api/nhanvien/{id}   → detail()
 * POST   /api/nhanvien        → add()
 * PUT    /api/nhanvien/{id}   → update()
 * DELETE /api/nhanvien/{id}   → delete()
 */
class NhanVien {
    private $model;

    public function __construct() {
        header('Content-Type: application/json');
        $this->model = new NhanVienModel();
    }

    public function handle($method, $id = null, $extra = null) {
        switch ($method) {
            case 'GET':
                $id ? $this->detail($id) : $this->list();
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

    private function detail($id) {
        $data = $this->model->getById($id);
        if (!$data) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Khong tim thay nhan vien']);
            return;
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
    }

    private function add() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['ten_nv']) || empty($data['sdt'])) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Vui long nhap ten va so dien thoai']);
            return;
        }
        if ($this->model->create($data)) {
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Them nhan vien thanh cong']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Khong the them nhan vien']);
        }
    }

    private function update($id) {
        if (!$id) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Thieu ID']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->model->update($id, $data)) {
            echo json_encode(['status' => 'success', 'message' => 'Cap nhat thanh cong']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Cap nhat that bai']);
        }
    }

    private function delete($id) {
        if (!$id) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Thieu ID']);
            return;
        }
        if ($this->model->hasWorkHistory($id)) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Khong the xoa nhan vien nay vi da co ten tren cac hoa don hoac phieu nhap kho.']);
            return;
        }
        if ($this->model->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Da xoa nhan vien khoi he thong.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Loi he thong, khong the xoa.']);
        }
    }
}
