<?php
require_once _DIR_ROOT . '/app/Controllers/Web/BaseWebController.php';

class NhanVien_EditController extends BaseWebController {
    public function index() {
        $this->requireAdmin();
        $this->render('NhanVien_Edit');
    }
}
