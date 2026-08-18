<?php

require_once dirname(__DIR__) . "/Core/Database.php";
require_once __DIR__ . "/Entity/Utilisateur.php";
require_once __DIR__ . "/Entity/Role.php";

class UtilisateurModel
{
    private static ?Database $db = null;

    private static function getDb(): Database
    {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }

        return self::$db;
    }

    private static function requeteBase(): string
    {
        return "SELECT
                    u.id AS id,
                    u.nom AS nom,
                    u.email AS email,
                    u.mot_de_passe AS mot_de_passe,
                    u.actif AS actif,
                    r.id AS role_id,
                    r.nom AS role_nom
                FROM utilisateurs u
                INNER JOIN roles r ON u.role_id = r.id";
    }

    private static function convertirEnUtilisateur(
        array $ligne
    ): Utilisateur {
        
        $role = new Role(
            (int) $ligne["role_id"],
            $ligne["role_nom"]
        );

        return new Utilisateur(
            (int) $ligne["id"],
            $ligne["nom"],
            $ligne["email"],
            $ligne["mot_de_passe"],
            $role,
            (bool) $ligne["actif"]
        );
    }

    public static function getUtilisateurs(): array
    {
        $sql = self::requeteBase() . " ORDER BY u.nom ASC";

        $lignes = self::getDb()->query($sql, false);

        $utilisateurs = [];

        foreach ($lignes as $ligne) {
            $utilisateurs[] = self::convertirEnUtilisateur($ligne);
        }

        return $utilisateurs;
    }

    public static function getUtilisateurParId(
        int $id
    ): ?Utilisateur {

        $sql = self::requeteBase() . " WHERE u.id = :id";

        $ligne = self::getDb()->executeQuery($sql, [
            "id" => $id
        ]);

        if (empty($ligne)) {
            return null;
        }

        return self::convertirEnUtilisateur($ligne);
    }

    public static function getUtilisateurParEmail(
        string $email
    ): ?Utilisateur {

        $sql = self::requeteBase() . " WHERE u.email = :email";

        $ligne = self::getDb()->executeQuery($sql, [
            "email" => $email
        ]);

        if (empty($ligne)) {
            return null;
        }

        return self::convertirEnUtilisateur($ligne);
    }
}