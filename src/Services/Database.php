<?php

namespace App\Services;

use Exception;
use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    /**
     * @throws Exception
     */
    private function __construct(array $config)
    {
        $dsn = "{$config['type']}:host={$config['host']};dbname={$config['database']}";
        $username = $config['username'];
        $password = $config['password'];

        try {
            $this->connection = new PDO($dsn, $username, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception('Connection failed: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public static function getInstance(): ?Database
    {
        if (self::$instance === null) {
            $envFile = __DIR__ . '/../../.env';
            if (!file_exists($envFile)) {
                throw new Exception('.env file not found');
            }

            $dotenv = parse_ini_file($envFile);

            $config = [
                'type' => $dotenv['DB_CONNECTION'] ?? 'mysql',
                'host' => $dotenv['DB_HOST'] ?? 'localhost',
                'database' => $dotenv['DB_DATABASE'] ?? '',
                'username' => $dotenv['DB_USERNAME'] ?? '',
                'password' => $dotenv['DB_PASSWORD'] ?? '',
            ];

            self::$instance = new Database($config);
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * @throws Exception
     */
    public function tableExists(string $tableName): bool
    {
        try {
            $sql = "SHOW TABLES LIKE ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$tableName]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return !empty($result);
        } catch (Exception $e) {
            throw new Exception('Ошибка проверки таблицы: ' . $e->getMessage());
        }
    }
}
