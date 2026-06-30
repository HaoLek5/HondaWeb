<?php
require_once _DIR_ROOT . '/app/Controllers/Web/BaseWebController.php';

class TaiKhoan_AddController extends BaseWebController {
    public function index() {
        $this->requireAdmin();
        $this->render('TaiKhoan_Add');
    }
}
