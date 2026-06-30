<?php
require_once _DIR_ROOT . '/app/Controllers/Web/BaseWebController.php';

class SanPham_EditController extends BaseWebController {
    public function index() {
        $this->requireLogin();
        $this->render('SanPham_Edit');
    }
}
