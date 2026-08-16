<?php
require_once dirname(__DIR__) . "/Core/Database.php";
require_once __DIR__ . "/Entity/Utilisateur.php";
require_once __DIR__ . "/Entity/Role.php";

class UtilisateurModel
{
    public Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

  
    private function requeteBase(): string
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

    private function convertirEnUtilisateur(array $ligne): Utilisateur
    {
        $role = new Role((int) $ligne["role_id"], $ligne["role_nom"]);

        return new Utilisateur(
            (int) $ligne["id"],
            $ligne["nom"],
            $ligne["email"],
            $ligne["mot_de_passe"],
            $role,
            (bool) $ligne["actif"]
        );
    }

    public function getUtilisateurs(): array
    {
        $sql = $this->requeteBase()." ORDER BY u.nom ASC";
        $lignes = $this->db->query($sql, false);

        $utilisateurs = [];

        foreach ($lignes as $ligne) {
            $utilisateurs[] = $this->convertirEnUtilisateur($ligne);
        }

        return $utilisateurs;
    }

    public function getUtilisateurParId(int $id): ?Utilisateur
    {
        $sql = $this->requeteBase()." WHERE u.id = :id";
        $ligne = $this->db->executeQuery($sql, ["id" => $id]);

        if (empty($ligne)) {
            return null;
        }

        return $this->convertirEnUtilisateur($ligne);
    }

    public function getUtilisateurParEmail(string $email): ?Utilisateur
    {
        $sql = $this->requeteBase()." WHERE u.email = :email";
        $ligne = $this->db->executeQuery($sql, ["email" => $email]);

        if (empty($ligne)) {
            return null;
        }

        return $this->convertirEnUtilisateur($ligne);
    }
}
