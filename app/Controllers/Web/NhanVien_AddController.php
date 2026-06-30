<?php
require_once _DIR_ROOT . '/app/Controllers/Web/BaseWebController.php';

class NhanVien_AddController extends BaseWebController {
    public function index() {
        $this->requireAdmin();
        $this->render('NhanVien_Add');
    }
}
