<?php
class ReportModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getMonthlyStats($month) {
        try {
            // 1. Tổng doanh thu và số hóa đơn (xe đã bán) trong tháng
            // Sử dụng đúng cột 'ngay_lap' của Hào
            $sqlSummary = "SELECT 
                            SUM(tong_tien) as total_revenue, 
                            COUNT(id) as total_sales 
                          FROM hoadon 
                          WHERE DATE_FORMAT(ngay_lap, '%Y-%m') = :month";
            
            $stmtSum = $this->conn->prepare($sqlSummary);
            $stmtSum->execute([':month' => $month]);
            $summary = $stmtSum->fetch(PDO::FETCH_ASSOC);

            // 2. Dữ liệu biểu đồ: Doanh thu theo từng ngày trong tháng
            $sqlChart = "SELECT 
                            DAY(ngay_lap) as day, 
                            SUM(tong_tien) as daily_revenue 
                         FROM hoadon 
                         WHERE DATE_FORMAT(ngay_lap, '%Y-%m') = :month 
                         GROUP BY DAY(ngay_lap) 
                         ORDER BY day ASC";
            
            $stmtChart = $this->conn->prepare($sqlChart);
            $stmtChart->execute([':month' => $month]);
            $chartData = $stmtChart->fetchAll(PDO::FETCH_ASSOC);

            // 3. Số lượng khách hàng đã mua hàng trong tháng
            // Đếm số ID khách hàng duy nhất xuất hiện trong bảng hoadon
            $sqlCust = "SELECT COUNT(DISTINCT id_khachhang) as active_cust 
                        FROM hoadon 
                        WHERE DATE_FORMAT(ngay_lap, '%Y-%m') = :month";
            $stmtCust = $this->conn->prepare($sqlCust);
            $stmtCust->execute([':month' => $month]);
            $cust = $stmtCust->fetch(PDO::FETCH_ASSOC);

            return [
                'status' => 'success',
                'summary' => [
                    // Format tiền để hiển thị đẹp ngay từ API
                    'revenue' => number_format($summary['total_revenue'] ?? 0, 0, ',', '.'),
                    'sales' => $summary['total_sales'] ?? 0,
                    'customers' => $cust['active_cust'] ?? 0
                ],
                'chart' => [
                    'labels' => array_column($chartData, 'day'),
                    'values' => array_column($chartData, 'daily_revenue')
                ]
            ];

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}