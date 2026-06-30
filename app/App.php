<?php
class App {
    public function __construct() {
        $url    = $this->urlProcess();
        $method = $_SERVER['REQUEST_METHOD'];

        // ── TRƯỜNG HỢP 1: API REST (/api/resource[/id])
        if (isset($url[0]) && strtolower($url[0]) === 'api') {
            array_shift($url);                                  // bỏ "api"
            $resource = strtolower($url[0] ?? '');             // xemay, khachhang …
            $id       = $url[1] ?? null;                        // /api/xemay/5
            $extra    = $url[2] ?? null;                        // dùng cho /api/xemay/5/toggle

            header('Content-Type: application/json');

            // Map tên resource → tên file Controller
            $map = [
                'xemay'      => 'XeMay',
                'khachhang'  => 'KhachHang',
                'nhacungcap' => 'NhaCungCap',
                'nhanvien'   => 'NhanVien',
                'hoadon'     => 'HoaDon',
                'phieunhap'  => 'PhieuNhap',
                'user'       => 'User',
                'report'     => 'Report',
            ];

            if (!isset($map[$resource])) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => "Resource '$resource' khong ton tai."]);
                return;
            }

            $controllerName = $map[$resource];
            require_once _DIR_ROOT . "/app/Controllers/Api/{$controllerName}.php";
            $controller = new $controllerName();
            $controller->handle($method, $id, $extra);
            return;
        }

        // ── TRƯỜNG HỢP 2: WEB (đi qua Web Controller)
        $viewName      = !empty($url[0]) ? $url[0] : 'Login';
        $controllerPath = _DIR_ROOT . "/app/Controllers/Web/{$viewName}Controller.php";

        if (file_exists($controllerPath)) {
            require_once $controllerPath;
            $cls = $viewName . 'Controller';
            (new $cls())->index();
        } else {
            $viewPath = _DIR_ROOT . "/views/{$viewName}.php";
            if (file_exists($viewPath)) {
                require_once $viewPath;
            } else {
                http_response_code(404);
                echo "<h2>404 – Khong tim thay trang: {$viewName}</h2>";
            }
        }
    }

    private function urlProcess() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(trim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}
