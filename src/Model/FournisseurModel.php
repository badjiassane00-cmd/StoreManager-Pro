<?php

require_once dirname(__DIR__) . "/Core/Database.php";
require_once __DIR__ . "/Entity/Fournisseur.php";

class FournisseurModel
{
    private static ?Database $db = null;

    private static function getDb(): Database
    {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }

        return self::$db;
    }

    private static function convertirEnFournisseur(array $ligne): Fournisseur
    {
        return new Fournisseur(
            (int) $ligne["id"],
            $ligne["nom"],
            $ligne["telephone"],
            $ligne["adresse"],
            $ligne["email"]
        );
    }

    public static function getFournisseurs(): array
    {
        $lignes = self::getDb()->getAllTables("fournisseurs");

        $fournisseurs = [];

        foreach ($lignes as $ligne) {
            $fournisseurs[] = self::convertirEnFournisseur($ligne);
        }

        return $fournisseurs;
    }

    public static function getFournisseurParId(int $id): ?Fournisseur
    {
        $sql = "SELECT * FROM fournisseurs WHERE id = :id";

        $ligne = self::getDb()->executeQuery($sql, [
            "id" => $id
        ]);

        if (!$ligne) {
            return null;
        }

        return self::convertirEnFournisseur($ligne);
    }

    public static function creerFournisseur(Fournisseur $fournisseur): int
    {
        $sql = "INSERT INTO fournisseurs
                (nom, telephone, adresse, email)
                VALUES
                (:nom, :telephone, :adresse, :email)";

        return self::getDb()->executeUpdate($sql, [
            "nom" => $fournisseur->getNom(),
            "telephone" => $fournisseur->getTelephone(),
            "adresse" => $fournisseur->getAdresse(),
            "email" => $fournisseur->getEmail()
        ]);
    }

    public static function mettreAJourFournisseur(
        Fournisseur $fournisseur
    ): int {
        
        $sql = "UPDATE fournisseurs
                SET nom = :nom,
                    telephone = :telephone,
                    adresse = :adresse,
                    email = :email
                WHERE id = :id";

        return self::getDb()->executeUpdate($sql, [
            "nom" => $fournisseur->getNom(),
            "telephone" => $fournisseur->getTelephone(),
            "adresse" => $fournisseur->getAdresse(),
            "email" => $fournisseur->getEmail(),
            "id" => $fournisseur->getId()
        ]);
    }
}