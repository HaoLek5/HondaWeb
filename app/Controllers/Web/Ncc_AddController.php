<?php
require_once _DIR_ROOT . '/app/Controllers/Web/BaseWebController.php';

class Ncc_AddController extends BaseWebController {
    public function index() {
        $this->requireLogin();
        $this->render('Ncc_Add');
    }
}
