<?php

require_once dirname(__DIR__) . "/Core/Database.php";
require_once __DIR__ . "/Entity/Produit.php";

class ProduitModel
{
    private static ?Database $db = null;

    private static function getDb(): Database
    {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }

        return self::$db;
    }

    private static function convertirEnProduit(array $ligne): Produit
    {
        return new Produit(
            (int) $ligne["id"],
            $ligne["nom"],
            (float) $ligne["prix_unitaire"],
            (int) $ligne["quantite_stock"],
            (int) $ligne["seuil_alerte"]
        );
    }

    public static function getProduits(): array
    {
        $lignes = self::getDb()->getAllTables("produits");

        $produits = [];

        foreach ($lignes as $ligne) {
            $produits[] = self::convertirEnProduit($ligne);
        }

        return $produits;
    }

    public static function getProduitParId(int $id): ?Produit
    {
        $sql = "SELECT * FROM produits WHERE id = :id";

        $ligne = self::getDb()->executeQuery($sql, [
            "id" => $id
        ]);

        if (!$ligne) {
            return null;
        }

        return self::convertirEnProduit($ligne);
    }

    public static function rechercherParNom(string $terme): array
    {
        $operateur = (
            self::getDb()
                ->connexion()
                ->getAttribute(PDO::ATTR_DRIVER_NAME) === "pgsql"
        ) ? "ILIKE" : "LIKE";

        $sql = "SELECT * FROM produits
                WHERE nom $operateur :terme
                ORDER BY nom ASC";

        $lignes = self::getDb()->executeQuery(
            $sql,
            ["terme" => "%" . $terme . "%"],
            false
        );

        $produits = [];

        foreach ($lignes as $ligne) {
            $produits[] = self::convertirEnProduit($ligne);
        }

        return $produits;
    }

    public static function creerProduit(Produit $produit): int
    {
        $sql = "INSERT INTO produits
                (nom, prix_unitaire, quantite_stock, seuil_alerte)
                VALUES
                (:nom, :prix_unitaire, :quantite_stock, :seuil_alerte)";

        return self::getDb()->executeUpdate($sql, [
            "nom" => $produit->getNom(),
            "prix_unitaire" => $produit->getPrixUnitaire(),
            "quantite_stock" => $produit->getQuantiteStock(),
            "seuil_alerte" => $produit->getSeuilAlerte()
        ]);
    }

    public static function mettreAJourProduit(Produit $produit): int
    {
        $sql = "UPDATE produits
                SET nom = :nom,
                    prix_unitaire = :prix_unitaire,
                    quantite_stock = :quantite_stock,
                    seuil_alerte = :seuil_alerte
                WHERE id = :id";

        return self::getDb()->executeUpdate($sql, [
            "nom" => $produit->getNom(),
            "prix_unitaire" => $produit->getPrixUnitaire(),
            "quantite_stock" => $produit->getQuantiteStock(),
            "seuil_alerte" => $produit->getSeuilAlerte(),
            "id" => $produit->getId()
        ]);
    }

    public static function mettreAJourStock(
        int $produitId,
        int $nouvelleQuantite
    ): int {
        
        $sql = "UPDATE produits
                SET quantite_stock = :quantite
                WHERE id = :id";

        return self::getDb()->executeUpdate($sql, [
            "quantite" => $nouvelleQuantite,
            "id" => $produitId
        ]);
    }
}