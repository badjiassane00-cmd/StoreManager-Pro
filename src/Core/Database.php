<?php

class Database
{
    private static ?self $instance = null;
    private static ?PDO $pdo = null;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function connexion(): PDO
    {
        if (self::$pdo === null) {
            try {
              
                self::$pdo = new PDO(
                    "pgsql:host=localhost;dbname=storemanager;port=5432",
                    "postgres",
                    "narutobadji"
                );

                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            } catch (PDOException $ex) {
                
                $databasePath = dirname(__DIR__, 2) . '/erp.db';

                self::$pdo = new PDO("sqlite:" . $databasePath);

                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
        }

        return self::$pdo;
    }

    public function deconnecte(): void
    {
        self::$pdo = null;
    }

    public function query(string $sql, bool $single = true): array
    {
        $query = $this->connexion()->query($sql);
        $result = $single ? $query->fetch() : $query->fetchAll();

        return $result !== false ? $result : [];
    }

    public function prepare(string $sql, array $datas): PDOStatement
    {
        $statement = $this->connexion()->prepare($sql);
        $statement->execute($datas);

        return $statement;
    }

    public function executeQuery(string $sql, array $datas, bool $single = true): array
    {
        $statement = $this->prepare($sql, $datas);
        $result = $single ? $statement->fetch() : $statement->fetchAll();

        return $result !== false ? $result : [];
    }

    public function executeUpdate(string $sql, array $datas): int
    {
        $statement = $this->prepare($sql, $datas);

        if (str_starts_with(strtoupper(trim($sql)), 'INSERT')) {
            return (int) $this->connexion()->lastInsertId();
        }

        return $statement->rowCount();
    }

    public function getAllTables(string $nameTable): array
    {
        $sql = "SELECT * FROM $nameTable";

        return $this->query($sql, false);
    }
}