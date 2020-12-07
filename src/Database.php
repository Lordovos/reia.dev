<?php
declare(strict_types=1);

namespace ReiaDev;

class Database {
    private static ?\ReiaDev\Database $instance = null;
    private ?\PDO $db = null;

    public static function getInstance(): \ReiaDev\Database {
        if (!self::$instance) {
            self::$instance = new \ReiaDev\Database();
        }
        return self::$instance;
    }
    public function getConnection(): \PDO {
        if (!$this->db) {
            $host = (!empty($_ENV["DB_HOST"]) ? $_ENV["DB_HOST"] : getenv("DB_HOST"));
            $name = (!empty($_ENV["DB_NAME"]) ? $_ENV["DB_NAME"] : getenv("DB_NAME"));
            $user = (!empty($_ENV["DB_USER"]) ? $_ENV["DB_USER"] : getenv("DB_USER"));
            $pass = (!empty($_ENV["DB_PASS"]) ? $_ENV["DB_PASS"] : getenv("DB_PASS"));
            $dsn = "pgsql:host=" . $host . ";dbname=" . $name;
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false
            ];
            try {
                $this->db = new \PDO($dsn, $user, $pass, $options);
            } catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int) $e->getCode());
            }
        }
        return $this->db;
    }
}
