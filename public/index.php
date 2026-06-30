<?php
session_start();

// 1. Đường dẫn vật lý cho PHP (Giữ nguyên)
define('_DIR_ROOT', str_replace('\\', '/', dirname(__DIR__)));

// 2. Đường dẫn URL cho Web/JavaScript (Thêm dòng này)
// Nếu folder dự án của bạn là quanlyxemay2, hãy viết như sau:
define('_WEB_ROOT', '/quanlyxemay2_fixed/public'); 

require_once _DIR_ROOT . '/app/Database.php';
require_once _DIR_ROOT . '/app/App.php';

$myApp = new App();