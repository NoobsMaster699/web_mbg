<?php

require_once __DIR__ . '/../app/core/helpers.php';

$sessionPath = base_path('storage/sessions');
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0775, true);
}

if (is_dir($sessionPath)) {
    session_save_path($sessionPath);
}
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
require_once app_path('core/Database.php');
require_once app_path('core/Controller.php');
require_once app_path('core/Router.php');

spl_autoload_register(function (string $class): void {
    foreach (['models', 'services'] as $folder) {
        $file = app_path($folder . '/' . $class . '.php');
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

$router = new Router();

$router->get('/', ['DashboardController', 'homepage']);
$router->get('/login', ['AuthController', 'login']);
$router->post('/login', ['AuthController', 'authenticate']);
$router->post('/logout', ['AuthController', 'logout']);

$router->get('/dashboard', ['DashboardController', 'index']);
$router->get('/menu', ['MenuController', 'index']);
$router->post('/menu/store', ['MenuController', 'store']);
$router->post('/menu/update/{id}', ['MenuController', 'update']);
$router->post('/menu/delete/{id}', ['MenuController', 'delete']);

$router->get('/kriteria', ['KriteriaController', 'index']);
$router->post('/kriteria/store', ['KriteriaController', 'store']);
$router->post('/kriteria/update/{id}', ['KriteriaController', 'update']);

$router->get('/penilaian', ['PenilaianController', 'index']);
$router->post('/penilaian/simpan', ['PenilaianController', 'save']);

$router->get('/ahp', ['AhpController', 'index']);
$router->post('/ahp/hitung', ['AhpController', 'calculate']);

$router->get('/hasil', ['SawController', 'index']);
$router->post('/saw/hitung', ['SawController', 'calculate']);

$router->get('/laporan', ['LaporanController', 'index']);
$router->get('/laporan/pdf', ['LaporanController', 'pdf']);
$router->get('/laporan/excel', ['LaporanController', 'excel']);
$router->get('/riwayat', ['RiwayatController', 'index']);

$router->dispatch();
