<?php

$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (preg_match('/\.(?:png|jpg|jpeg|gif)$/', $_SERVER['REQUEST_URI'])) {
    return false;
}
//elseif (0 === strpos($_SERVER['REQUEST_URI'], '/api')) { }
return require_once __DIR__.'/api.php';
