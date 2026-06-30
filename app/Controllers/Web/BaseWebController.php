<?php
/**
 * BaseWebController — Lớp cha cho tất cả Web Controller
 * Cung cấp hàm render() để load View đúng chuẩn MVC
 */
abstract class BaseWebController {

    /**
     * Kiểm tra đăng nhập. Nếu chưa đăng nhập thì chuyển về trang Login.
     */
    protected function requireLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['is_logged_in'])) {
            header('Location: ' . _WEB_ROOT . '/Login');
            exit;
        }
    }

    /**
     * Kiểm tra quyền Admin.
     */
    protected function requireAdmin() {
        $this->requireLogin();
        if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            echo "<h2>403 - Ban khong co quyen truy cap trang nay.</h2>";
            exit;
        }
    }

    /**
     * Load view và truyền data vào.
     * @param string $viewName  Tên file view (không có .php), ví dụ: 'SanPham'
     * @param array  $data      Mảng biến truyền vào view
     */
    protected function render($viewName, $data = []) {
        // Giải nén $data thành biến cục bộ để view dùng trực tiếp
        extract($data);
        $viewPath = _DIR_ROOT . "/views/" . $viewName . ".php";
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            http_response_code(404);
            echo "<h2>404 - View '$viewName' khong ton tai.</h2>";
        }
    }
}
