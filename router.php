<?php

chdir(__DIR__ . '/..');

$uri = $_SERVER["REQUEST_URI"];
$uri = strpos($uri, '?') !== false ? strstr($uri, '?', true) : $uri;

$index = isset($_GET['old']) ? '/stable.php' : '/index.php';

if (preg_match('/\.(?:png|jpg|jpeg|gif|ico)$/', $uri)) {
    return false;
} else {
    $path = explode('/', trim($uri, '/'), 5);
    $files[] = $path[0] . $index;
    
    if (isset($path[1])) {
        $files[] = $path[0] . '/' . $path[1] . $index;
    }
    
    if (isset($path[2])) {
        $files[] = $path[0] . '/' . $path[1] . '/' . $path[2] . $index;
    }
    
    $path = implode('/', array_slice($path, 2, 4)); 
    
    foreach ($files as $file) {
        if (file_exists(__DIR__ . '/' . $file)) {
            chdir(__DIR__.'/'.dirname($file));
            $_GET = ['path' => $path] + $_GET;
            
            include __DIR__ . '/' . $file;
            return ;
        }
    }
    
    return false;
}