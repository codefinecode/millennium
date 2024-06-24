<?php

namespace App\Repositories;

use PDO;

class OrderRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findByClientId($clientId): false|array
    {
        $stmt = $this->db->prepare('
            SELECT p.title, p.price
            FROM user_order uo
            JOIN products p ON uo.product_id = p.id
            WHERE uo.user_id = :user_id
            ORDER BY p.title ASC, p.price DESC
        ');
        $stmt->bindParam(':user_id', $clientId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
