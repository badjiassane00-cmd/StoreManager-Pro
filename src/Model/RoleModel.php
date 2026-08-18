<?php

require_once dirname(__DIR__) . "/Core/Database.php";
require_once __DIR__ . "/Entity/Role.php";

class RoleModel
{
    private static ?Database $db = null;

    private static function getDb(): Database
    {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }

        return self::$db;
    }

    private static function convertirEnRole(array $ligne): Role
    {
        return new Role(
            (int) $ligne["id"],
            $ligne["nom"]
        );
    }

    public static function getRoles(): array
    {
        $lignes = self::getDb()->getAllTables("roles");

        $roles = [];

        foreach ($lignes as $ligne) {
            $roles[] = self::convertirEnRole($ligne);
        }

        return $roles;
    }

    public static function getRoleParId(int $id): ?Role
    {
        $sql = "SELECT * FROM roles WHERE id = :id";

        $ligne = self::getDb()->executeQuery($sql, [
            "id" => $id
        ]);

        if (!$ligne) {
            return null;
        }

        return self::convertirEnRole($ligne);
    }

    public static function getRoleParNom(string $nom): ?Role
    {
        $sql = "SELECT * FROM roles WHERE nom = :nom";

        $ligne = self::getDb()->executeQuery($sql, [
            "nom" => $nom
        ]);

        if (!$ligne) {
            return null;
        }

        return self::convertirEnRole($ligne);
    }
}