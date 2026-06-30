<?php
require_once _DIR_ROOT . '/app/Controllers/Web/BaseWebController.php';

class KhachHangController extends BaseWebController {
    public function index() {
        $this->requireLogin();
        $this->render('KhachHang');
    }
}
