<?php

require_once __DIR__ . "/../../app/utils/Utils.php";
require_once __DIR__ . "/../../app/controllers/SalaController.php";
require_once __DIR__ . "/../../app/controllers/ReservaController.php";
require_once __DIR__ . "/../../app/controllers/UtilizadorController.php";
require_once __DIR__ . "/../../app/controllers/EquipamentoController.php";

// Quando adicionares login, é aqui que entram:
// require __DIR__ . '/../../vendor/autoload.php';
// require_once __DIR__ . "/../../app/controllers/AuthController.php";
// require_once __DIR__ . "/../../app/middleware/AuthMiddlewareAPI.php";
// use Firebase\JWT\JWT;
// use Firebase\JWT\Key;

// 1. Configuração
header("Content-Type: application/json; charset=UTF-8");

// Garante que QUALQUER erro/exceção não tratada volta sempre como JSON válido,
// em vez do PHP cuspir HTML/texto simples que parte o fetch().json() no frontend.
set_exception_handler(function (Throwable $e) {
    Utils::jsonResponse([
        'success' => false,
        'message' => 'Erro interno do servidor',
        'detalhe' => $e->getMessage(), // remove esta linha em produção
    ], 500);
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace("/api", "", $uri);
$uri = rtrim($uri, "/");
if ($uri === "") {
    $uri = "/";
}
$method = $_SERVER['REQUEST_METHOD'];

if (($uri === "/" || $uri === "/index") && $method === 'GET') {

    Utils::jsonResponse([
        "success" => true,
        "message" => "API SalaFácil"
    ], 200);
    exit;
}

//==================================================
// SALAS
//==================================================
elseif ($uri === '/salas' && $method === 'GET') {
    (new SalaController())->listar();

} elseif (preg_match('#^/salas/(\d+)$#', $uri, $m) && $method === 'GET') {
    $id = (int) $m[1];
    (new SalaController())->obter($id);

} elseif ($uri === '/salas' && $method === 'POST') {
    (new SalaController())->criar();
}

//==================================================
// RESERVAS
//==================================================
elseif ($uri === '/reservas' && $method === 'GET') {
    (new ReservaController())->listar();

} elseif (preg_match('#^/reservas/utilizador/(\d+)$#', $uri, $m) && $method === 'GET') {
    $idUtilizador = (int) $m[1];
    (new ReservaController())->listarPorUtilizador($idUtilizador);

} elseif ($uri === '/reservas' && $method === 'POST') {
    (new ReservaController())->criar();

} elseif (preg_match('#^/reservas/(\d+)/estado$#', $uri, $m) && $method === 'PATCH') {
    $id = (int) $m[1];
    (new ReservaController())->atualizarEstado($id);
}

//==================================================
// EQUIPAMENTOS
//==================================================
elseif ($uri === '/equipamentos' && $method === 'GET') {
    (new EquipamentoController())->listar();

} elseif ($uri === '/equipamentos/agrupados' && $method === 'GET') {
    (new EquipamentoController())->listarAgrupados();
}

//==================================================
// UTILIZADORES
//==================================================
elseif ($uri === '/utilizadores' && $method === 'GET') {
    (new UtilizadorController())->listar();

} elseif (preg_match('#^/utilizadores/(\d+)$#', $uri, $m) && $method === 'GET') {
    $id = (int) $m[1];
    (new UtilizadorController())->obter($id);

} elseif ($uri === '/utilizadores' && $method === 'POST') {
    (new UtilizadorController())->criar();
}

else {

    $dataResponse = [
        'success' => false,
        'message' => "Rota não encontrada",
        'data' => []
    ];

    Utils::jsonResponse($dataResponse, 404);
}