<?php

class BaseController {
    
    protected function view($view, $data = []) {
        extract($data);
        
        $flash = getFlash();
        
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        
        include $basePath . 'views/layouts/header.php';
        include $basePath . 'views/' . $view . '.php';
        include $basePath . 'views/layouts/footer.php';
    }
    
    protected function redirect($url) {
        redirect($url);
    }
    
    protected function setFlash($type, $message) {
        setFlash($type, $message);
    }
}
?>