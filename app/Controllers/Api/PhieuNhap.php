<?php
require_once _DIR_ROOT . '/app/Models/PhieuNhap.php';

/**
 * REST API: /api/phieunhap
 *
 * GET    /api/phieunhap        → list()
 * GET    /api/phieunhap/{id}   → detail()
 * POST   /api/phieunhap        → add()
 * PUT    /api/phieunhap/{id}   → update()
 */
class PhieuNhap {
    private $model;

    public function __construct() {
        header('Content-Type: application/json');
        $this->model = new PhieuNhapModel();
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
        $info = $this->model->getInfo($id);
        if (!$info) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Khong tim thay phieu nhap']);
            return;
        }
        $info['ngay_nhap'] = date('d/m/Y H:i', strtotime($info['ngay_nhap']));
        echo json_encode(['status' => 'success', 'info' => $info, 'details' => $this->model->getDetails($id)]);
    }

    private function add() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || empty($data['id_ncc']) || empty($data['items'])) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Du lieu khong day du']);
            return;
        }
        if (empty($_SESSION['id_nhanvien'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Chua dang nhap. Vui long dang nhap lai!']);
            return;
        }
        $data['id_nhanvien_login'] = $_SESSION['id_nhanvien'];
        $result = $this->model->create($data);
        if ($result['status'] === 'success') http_response_code(201);
        else http_response_code(500);
        echo json_encode($result);
    }

    private function update($id) {
        if (!$id) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Thieu ID phieu nhap']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || empty($data['items'])) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Du lieu khong hop le']);
            return;
        }
        $data['id'] = $id;
        $result = $this->model->update($data);
        if ($result['status'] === 'error') http_response_code(422);
        echo json_encode($result);
    }
}
