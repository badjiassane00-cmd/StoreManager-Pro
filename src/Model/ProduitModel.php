<?php
require_once dirname(__DIR__) . "/Core/Database.php";
require_once __DIR__ . "/Entity/Produit.php";

class ProduitModel
{
    public Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function convertirEnProduit(array $ligne): Produit
    {
        return new Produit(
            (int) $ligne["id"],
            $ligne["nom"],
            (float) $ligne["prix_unitaire"],
            (int) $ligne["quantite_stock"],
            (int) $ligne["seuil_alerte"]
        );
    }

    public function getProduits(): array
    {
        $lignes = $this->db->getAllTables("produits");

        $produits = [];

        foreach ($lignes as $ligne) {
            $produits[] = $this->convertirEnProduit($ligne);
        }

        return $produits;
    }

   public function getProduitParId(int $id): ?Produit
{
    $sql = "SELECT * FROM produits WHERE id = :id";

    $ligne = $this->db->executeQuery($sql, [
        "id" => $id
    ]);

    if (!$ligne) {
        return null;
    }

    return $this->convertirEnProduit($ligne);
}

    public function rechercherParNom(string $terme): array
    {
        $operateur = ($this->db->connexion()->getAttribute(PDO::ATTR_DRIVER_NAME) === "pgsql") ? "ILIKE" : "LIKE";

        $sql = "SELECT * FROM produits WHERE nom $operateur :terme ORDER BY nom ASC";
        $lignes = $this->db->executeQuery($sql, ["terme" => "%".$terme."%"], false);

        $produits = [];

        foreach ($lignes as $ligne) {
            $produits[] = $this->convertirEnProduit($ligne);
        }

        return $produits;
    }

    public function creerProduit(Produit $produit): int
    {
        $sql = "INSERT INTO produits (nom, prix_unitaire, quantite_stock, seuil_alerte)
                VALUES (:nom, :prix_unitaire, :quantite_stock, :seuil_alerte)";

        return $this->db->executeUpdate($sql, [
            "nom" => $produit->getNom(),
            "prix_unitaire" => $produit->getPrixUnitaire(),
            "quantite_stock" => $produit->getQuantiteStock(),
            "seuil_alerte" => $produit->getSeuilAlerte(),
        ]);
    }

    public function mettreAJourProduit(Produit $produit): int
    {
        $sql = "UPDATE produits
                SET nom = :nom, prix_unitaire = :prix_unitaire,
                    quantite_stock = :quantite_stock, seuil_alerte = :seuil_alerte
                WHERE id = :id";

        return $this->db->executeUpdate($sql, [
            "nom" => $produit->getNom(),
            "prix_unitaire" => $produit->getPrixUnitaire(),
            "quantite_stock" => $produit->getQuantiteStock(),
            "seuil_alerte" => $produit->getSeuilAlerte(),
            "id" => $produit->getId(),
        ]);
    }

    public function mettreAJourStock(int $produitId, int $nouvelleQuantite): int
    {
        $sql = "UPDATE produits SET quantite_stock = :quantite WHERE id = :id";

        return $this->db->executeUpdate($sql, [
            "quantite" => $nouvelleQuantite,
            "id" => $produitId,
        ]);
    }
}
