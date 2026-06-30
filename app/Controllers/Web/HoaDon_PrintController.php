<?php
require_once _DIR_ROOT . '/app/Controllers/Web/BaseWebController.php';

class HoaDon_PrintController extends BaseWebController {
    public function index() {
        $this->requireLogin();
        $this->render('HoaDon_Print');
    }
}
