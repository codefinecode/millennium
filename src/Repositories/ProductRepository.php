<?php

namespace App\Repositories;

use App\Services\Database;
use PDO;
use Exception;
class ProductRepository
{
    private PDO $db;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * @throws Exception
     */
    public function insertProducts(array $products): bool
    {
        try {
            $this->db->beginTransaction();

            // Подготовка запроса для вставки или обновления
            $stmtUpsert = $this->db->prepare("
            INSERT INTO products (title, price)
            VALUES (:title, :price)
            ON DUPLICATE KEY UPDATE
                price = VALUES(price)
        ");

            foreach ($products as $product) {
                $stmtUpsert->execute([
                    ':title' => $product['title'],
                    ':price' => $product['price'],
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     *  метод на случай если апдейты цены делать не нужно, и просто пропускать запись
     * TODO уточнить условия
     */
    /*public function insertProductsNotUpdate(array $products): bool
    {
        try {
            $this->db->beginTransaction();

            $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM products WHERE title = :title");
            $stmtInsert = $this->db->prepare("INSERT INTO products (title, price) VALUES (:title, :price)");

            foreach ($products as $product) {
                // Проверка на существование записи
                $stmtCheck->execute([':title' => $product['title']]);
                if ($stmtCheck->fetchColumn() > 0) {
                    // Если запись существует, пропускаем вставку. можно бросить исключение или обновить существующую запись
                    continue;
                }

                // Вставка новой записи
                $stmtInsert->execute([
                    ':title' => $product['title'],
                    ':price' => $product['price'],
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }*/

    /**
     * Валидация массива продуктов
     *
     * @param array $products
     * @return array
     * @throws Exception
     */
    public function validateProducts(array $products): array
    {
        // хотелось показать владение фильтрами php, на практике и для улучшения поддержки прошелся бы обычным foreach с условиями
        $filters = [
            'title' => [
                'filter' => FILTER_CALLBACK,
                'options' => function ($title) {
                    if (!is_string($title) || mb_strlen($title) > 255) {
                        throw new Exception("Неправильный title: {$title} (должен быть непустой строкой)");
                    }
                    return htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
                }
            ],
            'price' => [
                'filter' => FILTER_CALLBACK,
                'options' => function ($price) {
                    $validatedPrice = filter_var($price, FILTER_VALIDATE_FLOAT);
                    if ($validatedPrice === false || $validatedPrice < 0) {
                        throw new Exception("Неправильный price: {$price} (должен быть положительным вещественным числом)");
                    }
                    return $validatedPrice;
                }
            ]
        ];

        $validatedProducts = array_map(function($product) use ($filters) {
            try {
                return filter_var_array($product, $filters);
            } catch (Exception $e) {
                throw new Exception("Ошибка валидации для продукта: \"{$product['title']} : {$product['price']}\" " . $e->getMessage());
            }
        }, $products);
        return $validatedProducts;
    }


}
