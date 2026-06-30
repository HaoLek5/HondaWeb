<?php
require_once _DIR_ROOT . '/app/Controllers/Web/BaseWebController.php';

class Kho_DetailController extends BaseWebController {
    public function index() {
        $this->requireLogin();
        $this->render('Kho_Detail');
    }
}
