<?php
require_once _DIR_ROOT . '/app/Models/HoaDon.php';

/**
 * REST API: /api/hoadon
 *
 * GET    /api/hoadon        → list()
 * GET    /api/hoadon/{id}   → detail()
 * POST   /api/hoadon        → create()
 * DELETE /api/hoadon/{id}   → delete()  (huy hoa don)
 */
class HoaDon {
    private $model;

    public function __construct() {
        header('Content-Type: application/json');
        $this->model = new HoaDonModel();
    }

    public function handle($method, $id = null, $extra = null) {
        switch ($method) {
            case 'GET':
                $id ? $this->detail($id) : $this->list();
                break;
            case 'POST':
                $this->create();
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
        try {
            echo json_encode($this->model->getAll());
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    private function detail($id) {
        $info = $this->model->getById($id);
        if (!$info) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Khong tim thay hoa don']);
            return;
        }
        echo json_encode(['status' => 'success', 'info' => $info, 'details' => $this->model->getDetails($id)]);
    }

    private function create() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id_khachhang']) || empty($data['id_nhanvien']) || empty($data['items'])) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Vui long chon khach hang, nhan vien va it nhat 1 san pham']);
            return;
        }
        $result = $this->model->create($data);
        if ($result['status'] === 'success') http_response_code(201);
        else http_response_code(422);
        echo json_encode($result);
    }

    private function delete($id) {
        if (!$id) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Thieu ID hoa don']);
            return;
        }
        $result = $this->model->delete($id);
        if ($result['status'] === 'error') http_response_code(500);
        echo json_encode($result);
    }
}
