<?php

namespace App\Controllers;

use App\Repositories\ClientRepository;
use App\Repositories\OrderRepository;
use App\Services\Database;
use Exception;

class ClientController
{
    private ClientRepository $clientRepository;
    private OrderRepository $orderRepository;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->clientRepository = new ClientRepository($db);
        $this->orderRepository = new OrderRepository($db);
    }

    public function getClientOrders($clientId): void
    {
        $client = $this->clientRepository->findById($clientId);
        if (!$client) {
            http_response_code(404);
            echo json_encode(['error' => 'Client not found']);
            return;
        }

        $orders = $this->orderRepository->findByClientId($clientId);

        $data = [
            'client' => $client,
            'orders' => $orders
        ];

        header('Content-Type: application/json');
        echo json_encode($data);
    }

}

