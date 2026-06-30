<?php
require_once _DIR_ROOT . '/app/Controllers/Web/BaseWebController.php';

class TaiKhoan_EditController extends BaseWebController {
    public function index() {
        $this->requireAdmin();
        $this->render('TaiKhoan_Edit');
    }
}
