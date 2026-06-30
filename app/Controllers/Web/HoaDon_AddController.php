<?php
require_once _DIR_ROOT . '/app/Controllers/Web/BaseWebController.php';

class HoaDon_AddController extends BaseWebController {
    public function index() {
        $this->requireLogin();
        $this->render('HoaDon_Add');
    }
}
