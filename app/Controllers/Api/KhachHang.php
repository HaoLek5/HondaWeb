<?php
require_once _DIR_ROOT . '/app/Models/KhachHang.php';

/**
 * REST API: /api/khachhang
 *
 * GET    /api/khachhang          → list()
 * GET    /api/khachhang?q=...    → search()
 * GET    /api/khachhang/{id}     → detail()
 * POST   /api/khachhang          → add()
 * PUT    /api/khachhang/{id}     → update()
 * DELETE /api/khachhang/{id}     → delete()
 */
class KhachHang {
    private $model;

    public function __construct() {
        header('Content-Type: application/json');
        $this->model = new KhachHangModel();
    }

    public function handle($method, $id = null, $extra = null) {
        switch ($method) {
            case 'GET':
                if (!$id) {
                    isset($_GET['q']) ? $this->search() : $this->list();
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

    private function detail($id) {
        $data = $this->model->getById($id);
        if (!$data) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Khong tim thay khach hang']);
            return;
        }
        echo json_encode($data);
    }

    private function add() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['ten_kh']) || empty($data['sdt'])) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Vui long nhap day du ten va so dien thoai']);
            return;
        }
        if ($this->model->isExists($data['sdt'])) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'So dien thoai da ton tai tren he thong']);
            return;
        }
        if ($this->model->create($data)) {
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Them khach hang thanh cong']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Loi luu Database']);
        }
    }

    private function update($id) {
        if (!$id) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Thieu ID khach hang']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->model->isExists($data['sdt'] ?? '', $id)) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'So dien thoai nay da thuoc ve khach hang khac']);
            return;
        }
        if ($this->model->update($id, $data)) {
            echo json_encode(['status' => 'success', 'message' => 'Cap nhat thong tin khach hang thanh cong']);
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
        if ($this->model->hasInvoices($id)) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Khong the xoa khach hang nay vi da co lich su mua hang.']);
            return;
        }
        if ($this->model->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Da xoa khach hang thanh cong.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Loi he thong, khong the xoa.']);
        }
    }
}
