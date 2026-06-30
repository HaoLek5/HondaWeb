<?php
require_once _DIR_ROOT . '/app/Controllers/Web/BaseWebController.php';

class NccController extends BaseWebController {
    public function index() {
        $this->requireLogin();
        $this->render('Ncc');
    }
}
