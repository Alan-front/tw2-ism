<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


$dir = __DIR__;
$base_url = "/media_scroll/";

$files = array_values(array_filter(scandir($dir), function($file) {
    return preg_match('/\.(jpg|jpeg|png|gif|mp4|webm)$/i', $file); 
}));

$files = array_map(fn($file) => $base_url . $file, $files);

header('Content-Type: application/json');
echo json_encode($files);
?>
