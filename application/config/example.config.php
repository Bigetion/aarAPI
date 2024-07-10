<?php
$root = $_SERVER['DOCUMENT_ROOT'];
$path = str_replace('\\', '/', getcwd());
$base_path = str_replace('//', '/', '/' . str_replace($root, '', $path) . '/');
$base_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $base_path;
return array(
    'base_url' => $base_url,
    'default_app_method' => 'index',
    'default_project' => 'page',
    'default_project_controller' => 'home',
    'default_project_method' => 'index',
    'secret_key' => '3vhYPqExZX',
    'allowed_origin' => '*',
)
?>
