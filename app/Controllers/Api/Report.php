<?php
require_once _DIR_ROOT . '/app/Models/ReportModel.php';

/**
 * REST API: /api/report
 *
 * GET /api/report/monthly?month=2025-01
 */
class Report {
    private $model;

    public function __construct() {
        header('Content-Type: application/json');
        $this->model = new ReportModel();
    }

    public function handle($method, $id = null, $extra = null) {
        if ($method !== 'GET') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Chi chap nhan GET']);
            return;
        }
        // GET /api/report/monthly
        if ($id === 'monthly') {
            $month = $_GET['month'] ?? date('Y-m');
            echo json_encode($this->model->getMonthlyStats($month));
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Endpoint khong ton tai']);
        }
    }
}
