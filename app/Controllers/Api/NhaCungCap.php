<?php
require_once _DIR_ROOT . '/app/Models/NhaCungCap.php';

/**
 * REST API: /api/nhacungcap
 *
 * GET    /api/nhacungcap        → list()
 * GET    /api/nhacungcap/{id}   → detail()
 * POST   /api/nhacungcap        → add()
 * PUT    /api/nhacungcap/{id}   → update()
 * DELETE /api/nhacungcap/{id}   → delete()
 */
class NhaCungCap {
    private $model;

    public function __construct() {
        header('Content-Type: application/json');
        $this->model = new NhaCungCapModel();
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
            echo json_encode(['status' => 'error', 'message' => 'Khong tim thay nha cung cap']);
            return;
        }
        echo json_encode($data);
    }

    private function add() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['ten_ncc']) || empty($data['sdt'])) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Vui long nhap ten va so dien thoai']);
            return;
        }
        if ($this->model->checkPhone($data['sdt'])) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'So dien thoai da thuoc ve nha cung cap khac']);
            return;
        }
        if (!empty($data['email']) && $this->model->checkEmail($data['email'])) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Email da thuoc ve nha cung cap khac']);
            return;
        }
        if ($this->model->create($data)) {
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Them thanh cong']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Loi Database']);
        }
    }

    private function update($id) {
        if (!$id) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Thieu ID']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->model->checkPhone($data['sdt'] ?? '', $id)) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'So dien thoai da bi trung']);
            return;
        }
        if (!empty($data['email']) && $this->model->checkEmail($data['email'], $id)) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Email da bi trung']);
            return;
        }
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
        if ($this->model->hasImports($id)) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Khong the xoa! Nha cung cap nay da co lich su nhap hang.']);
            return;
        }
        if ($this->model->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Xoa thanh cong']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Loi he thong']);
        }
    }
}
