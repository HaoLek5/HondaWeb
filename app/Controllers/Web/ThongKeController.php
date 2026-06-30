<?php
require_once _DIR_ROOT . '/app/Controllers/Web/BaseWebController.php';

class ThongKeController extends BaseWebController {
    public function index() {
        $this->requireLogin();
        $this->render('ThongKe');
    }
}
