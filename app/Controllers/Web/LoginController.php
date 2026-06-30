<?php
require_once _DIR_ROOT . '/app/Controllers/Web/BaseWebController.php';

class LoginController extends BaseWebController {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!empty($_SESSION['is_logged_in'])) {
            header('Location: ' . _WEB_ROOT . '/SanPham');
            exit;
        }
        $this->render('Login');
    }
}
