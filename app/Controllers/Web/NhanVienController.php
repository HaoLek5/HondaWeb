<?php
require_once _DIR_ROOT . '/app/Controllers/Web/BaseWebController.php';

class NhanVienController extends BaseWebController {
    public function index() {
        $this->requireAdmin();
        $this->render('NhanVien');
    }
}
