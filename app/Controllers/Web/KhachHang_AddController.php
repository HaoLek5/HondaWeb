<?php
require_once _DIR_ROOT . '/app/Controllers/Web/BaseWebController.php';

class KhachHang_AddController extends BaseWebController {
    public function index() {
        $this->requireLogin();
        $this->render('KhachHang_Add');
    }
}
