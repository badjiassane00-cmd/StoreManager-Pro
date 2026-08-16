<?php
require_once dirname(__DIR__) . "/Core/Database.php";
require_once __DIR__ . "/Entity/Fournisseur.php";

class FournisseurModel
{
    public Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function convertirEnFournisseur(array $ligne): Fournisseur
    {
        return new Fournisseur(
            (int) $ligne["id"],
            $ligne["nom"],
            $ligne["telephone"],
            $ligne["adresse"],
            $ligne["email"]
        );
    }

    public function getFournisseurs(): array
    {
        $lignes = $this->db->getAllTables("fournisseurs");

        $fournisseurs = [];

        foreach ($lignes as $ligne) {
            $fournisseurs[] = $this->convertirEnFournisseur($ligne);
        }

        return $fournisseurs;
    }

   public function getFournisseurParId(int $id): ?Fournisseur
{
    $sql = "SELECT * FROM fournisseurs WHERE id = :id";

    $ligne = $this->db->executeQuery($sql, [
        "id" => $id
    ]);

    if (!$ligne) {
        return null;
    }

    return $this->convertirEnFournisseur($ligne);
}

    public function creerFournisseur(Fournisseur $fournisseur): int
    {
        $sql = "INSERT INTO fournisseurs (nom, telephone, adresse, email)
                VALUES (:nom, :telephone, :adresse, :email)";

        return $this->db->executeUpdate($sql, [
            "nom" => $fournisseur->getNom(),
            "telephone" => $fournisseur->getTelephone(),
            "adresse" => $fournisseur->getAdresse(),
            "email" => $fournisseur->getEmail(),
        ]);
    }

    public function mettreAJourFournisseur(Fournisseur $fournisseur): int
    {
        $sql = "UPDATE fournisseurs
                SET nom = :nom, telephone = :telephone, adresse = :adresse, email = :email
                WHERE id = :id";

        return $this->db->executeUpdate($sql, [
            "nom" => $fournisseur->getNom(),
            "telephone" => $fournisseur->getTelephone(),
            "adresse" => $fournisseur->getAdresse(),
            "email" => $fournisseur->getEmail(),
            "id" => $fournisseur->getId(),
        ]);
    }
}
