<?php
require_once _DIR_ROOT . '/app/Controllers/Web/BaseWebController.php';

class KhachHang_EditController extends BaseWebController {
    public function index() {
        $this->requireLogin();
        $this->render('KhachHang_Edit');
    }
}
