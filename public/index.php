<?php

require_once '../vendor/autoload.php';

use App\Services\UnpackService;
use App\Services\MigrationService;
use App\Controllers\ClientController;
use App\Controllers\ProductController;

// Обработка POST запроса для добавления продуктов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    try {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON');
        }

        $productController = new ProductController();
        $productController->addProducts($input);

        echo json_encode(['success' => true, 'message' => 'Products added successfully']);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }

    exit();
}

// Обработка GET запроса
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // для удобства отладки - очищаем базу
    if (isset($_GET['flush_database']) && $_GET['flush_database'] === 'password') {
        $migrationService = new MigrationService();
        $migrationService->flushDatabase();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Базы очищены']);
        exit();
    }

    //если есть параметр migrate запускаем распаковку/заполнение БД/оптимизацию
    if (isset($_GET['migrate'])) {
        header('Content-Type: application/json');
        try {
            // Распаковка архива и загрузка дампа
            $unpackService = new UnpackService();
            $unpackService->unpackFile('../data/products.zip', '../storage/', 'sql');

            $migrationService = new MigrationService();

            // Миграция базы данных
            $migrationService->migrate('../storage/products.sql', 'products');
            $migrationService->migrate('../storage/user.sql', 'user');
            $migrationService->migrate('../storage/user_order.sql', 'user_order');

            // Оптимизация таблиц
            $migrationService->optimizeTables();

            echo json_encode(['success' => true, 'message' => 'Migration and optimization completed']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => json_encode($e)]);
        }
        exit();
    }

    $clientId = filter_input(INPUT_GET, 'client_id', FILTER_SANITIZE_NUMBER_INT);
    if ($clientId > 0) {
        try {
            $clientController = new ClientController();
            $clientController->getClientOrders($clientId);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit();
    }

    // Если нет параметров client_id и migrate, подключаем HTML-контент
    include '../src/Views/content.php';
}
