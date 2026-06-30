<?php
require_once _DIR_ROOT . '/app/Controllers/Web/BaseWebController.php';

class SanPham_AddController extends BaseWebController {
    public function index() {
        $this->requireLogin();
        $this->render('SanPham_Add');
    }
}
