<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo thống kê | Honda Thắng Lợi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= _WEB_ROOT ?>/assets/css/navi.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Reset & Layout */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; }

        .main-content {
            margin-left: 250px; 
            padding: 30px;
            min-height: 100vh;
            width: calc(100% - 250px);
            transition: all 0.3s;
        }

        .page-header { 
            margin-bottom: 25px; 
            border-bottom: 2px solid #ddd; 
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-header h2 { color: #333; display: flex; align-items: center; gap: 10px; }

        /* Bộ lọc tháng */
        .filter-box {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .filter-box input[type="month"] {
            border: 1px solid #ddd;
            padding: 5px 10px;
            border-radius: 4px;
            outline: none;
        }

        /* Grid thông số */
        .stat-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 20px; 
            margin-bottom: 30px; 
        }
        .stat-card { 
            background: white; 
            padding: 25px; 
            border-radius: 12px; 
            display: flex; 
            align-items: center; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
        }
        .stat-card i { font-size: 2.5rem; margin-right: 20px; opacity: 0.5; }
        .stat-info h3 { margin: 0; color: #7f8c8d; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; }
        .stat-info p { margin: 5px 0 0; font-size: 1.6rem; font-weight: bold; color: #2c3e50; }

        /* Biểu đồ & Danh sách */
        .report-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .card h3 { margin-bottom: 20px; font-size: 1.1rem; color: #333; border-left: 4px solid #cc0000; padding-left: 10px; }

        table { width: 100%; border-collapse: collapse; }
        table tr { border-bottom: 1px solid #f0f0f0; }
        table td { padding: 12px 5px; color: #444; }
        .rank-badge { background: #eee; padding: 2px 8px; border-radius: 50%; font-size: 12px; font-weight: bold; margin-right: 8px; }

        @media (max-width: 1024px) {
            .report-grid { grid-template-columns: 1fr; }
            .main-content { margin-left: 0; width: 100%; }
        }
    </style>
</head>
<body>
    
    <?php include_once _DIR_ROOT . '/views/navi.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h2><i class="fas fa-chart-pie"></i> BÁO CÁO KINH DOANH</h2>
            <div class="filter-box">
                <label for="reportMonth"><i class="fas fa-filter"></i> Xem theo tháng:</label>
                <input type="month" id="reportMonth" value="<?= date('Y-m') ?>" onchange="loadData()">
            </div>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <i class="fas fa-coins" style="color: #2ecc71;"></i>
                <div class="stat-info">
                    <h3>Tổng doanh thu</h3>
                    <p id="sumDoanhThu">0 đ</p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-motorcycle" style="color: #3498db;"></i>
                <div class="stat-info">
                    <h3>Xe máy đã bán</h3>
                    <p id="sumXeBan">0 chiếc</p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-user-check" style="color: #f1c40f;"></i>
                <div class="stat-info">
                    <h3>Khách mua hàng</h3>
                    <p id="sumKhach">0 người</p>
                </div>
            </div>
        </div>

        <div class="report-grid">
            <div class="card">
                <h3 id="chartTitle">Biểu đồ doanh thu</h3>
                <canvas id="revenueChart" style="max-height: 400px;"></canvas>
            </div>

            <div class="card">
                <h3>Phân tích dữ liệu</h3>
                <div id="additionalInfo" style="line-height: 1.8; color: #666;">
                    <p><i class="fas fa-info-circle"></i> Chọn tháng để xem biến động doanh thu chi tiết theo từng ngày.</p>
                    <hr style="margin: 15px 0; border: 0; border-top: 1px solid #eee;">
                    <p>Dữ liệu được cập nhật thời gian thực từ hệ thống <b>Thắng Lợi Motors</b>.</p>
                </div>
            </div>
        </div>
    </main>

    <script>
        let myChart = null;
        const API_URL = '<?= _WEB_ROOT ?>/api/report';

        document.addEventListener("DOMContentLoaded", () => {
            loadData();
        });

        async function loadData() {
            const selectedMonth = document.getElementById('reportMonth').value;
            const [year, month] = selectedMonth.split('-');
            
            document.getElementById('chartTitle').innerText = `Doanh thu tháng ${month}/${year}`;

            try {
                // Gọi API với tham số tháng
                const res = await fetch(`${API_URL}/monthly?month=${selectedMonth}`);
                const result = await res.json();

                if (result.status === 'success') {
                    // 1. Cập nhật các ô số liệu (Sử dụng dữ liệu từ Model trả về)
                    document.getElementById('sumDoanhThu').innerText = result.summary.revenue + " đ";
                    document.getElementById('sumXeBan').innerText = result.summary.sales + " chiếc";
                    document.getElementById('sumKhach').innerText = result.summary.customers + " khách";

                    // 2. Vẽ biểu đồ
                    renderChart(result.chart.labels, result.chart.values);
                }
            } catch (err) {
                console.error("Lỗi tải dữ liệu:", err);
                alert("Không thể tải dữ liệu báo cáo!");
            }
        }

        function renderChart(labels, values) {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            
            // Nếu đã có biểu đồ trước đó thì hủy để vẽ cái mới
            if (myChart) {
                myChart.destroy();
            }

            myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels.map(day => 'Ngày ' + day),
                    datasets: [{
                        label: 'Doanh thu (VNĐ)',
                        data: values,
                        borderColor: '#e74c3c',
                        backgroundColor: 'rgba(231, 76, 60, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#e74c3c'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { 
                        legend: { display: false } 
                    },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            grid: { color: '#f0f0f0' },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('vi-VN') + ' đ';
                                }
                            }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    </script>
</body>
</html>