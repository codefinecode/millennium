<?php

namespace App\Services;

use Exception;
use PDO;
use PDOException;

class MigrationService
{
    private PDO $connection;
    private Database $db;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->connection = $this->db->getConnection();
    }

    /**
     * Применяет миграцию из SQL-файла к базе данных.
     *
     * @param string $filePath Путь к SQL-файлу с миграциями.
     * @param string $tableName Имя таблицы, на которую применяется миграция.
     * @throws Exception
     */
    public function migrate(string $filePath, string $tableName): void
    {
        if (!file_exists($filePath)) {
            throw new Exception("Файл миграций не найден: $filePath");
        }
        $sqlData = file_get_contents($filePath);
        if (empty($sqlData)) {
            throw new Exception("Файл миграций пуст: $filePath");
        }
        // Разделяем SQL-запросы по точке с запятой
        $statements = explode(';', $sqlData);

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                try {
                    // Проверяем, является ли инструкция CREATE или ALTER
                    if (preg_match('/^(CREATE|ALTER|DROP)/i', $statement)) {
                        // Если да, выполняем запрос без транзакции
                        $stmt = $this->connection->prepare($statement);
                        $stmt->execute();
                    } else {
                        // Иначе, начинаем транзакцию для запроса
                        $this->connection->beginTransaction();
                        $stmt = $this->connection->prepare($statement);
                        $stmt->execute();
                        $this->connection->commit();
                    }
                } catch (Exception $e) {
                    if ($this->connection->inTransaction()) {
                        $this->connection->rollBack();
                    }
                    throw new Exception("Ошибка при выполнении запроса '$statement' для таблицы '$tableName': " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Оптимизирует таблицы базы данных.
     *
     * @throws Exception
     */
    public function optimizeTables(): void
    {
        if ($this->db->tableExists('products')) {
            // Добавление уникального индекса на поле title таблицы products
            $this->connection->exec('ALTER TABLE products ADD UNIQUE INDEX idx_title_unique (title);');
        }

        if ($this->db->tableExists('user')) {
            // Добавление индекса на поле second_name таблицы user
            $this->connection->exec('ALTER TABLE user ADD INDEX idx_second_name (second_name);');
        }

        if ($this->db->tableExists('user_order')) {
            // Добавление индексов на поля user_id и product_id таблицы user_order
            $this->connection->exec('ALTER TABLE user_order ADD INDEX idx_user_id (user_id);');
            $this->connection->exec('ALTER TABLE user_order ADD INDEX idx_product_id (product_id);');
            // Добавление внешних ключей
            $this->connection->exec('ALTER TABLE user_order ADD CONSTRAINT fk_user_order_user FOREIGN KEY (user_id) REFERENCES user(id);');
            $this->connection->exec('ALTER TABLE user_order ADD CONSTRAINT fk_user_order_product FOREIGN KEY (product_id) REFERENCES products(id);');
        }

    }

    /**
     * Очистка базы
     *
     * @throws Exception
     */
    public function flushDatabase(): void
    {
        try {
            $this->connection->exec('DROP TABLE IF EXISTS user_order;');
            $this->connection->exec('DROP TABLE IF EXISTS products;');
            $this->connection->exec('DROP TABLE IF EXISTS user;');
        } catch (PDOException $e) {
            throw new Exception('Ошибка при выполнении запроса: ' . $e->getMessage() );
        }
    }
}
