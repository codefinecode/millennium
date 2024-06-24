<?php

namespace App\Controllers;

use App\Repositories\ProductRepository;
use Exception;

class ProductController
{
    private ProductRepository $productRepository;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->productRepository = new ProductRepository();
    }

    /**
     * @throws Exception
     */
    public function addProducts(array $products): string
    {

        // Валидация входящих данных
        $validatedData = $this->productRepository->validateProducts($products['products']);

        if ($validatedData) {
            try {
                $success = $this->productRepository->insertProducts($validatedData);

                if ($success) {
                    return json_encode(['success' => true, 'message' => 'Products added successfully']);
                } else {
                    http_response_code(500);
                    return json_encode(['error' => 'Failed to insert products']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                return json_encode(['error' => $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            return json_encode(['error' => 'Invalid request data']);
        }
    }
}
