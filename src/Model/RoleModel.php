<?php
require_once dirname(__DIR__) . "/Core/Database.php";
require_once __DIR__ . "/Entity/Role.php";

class RoleModel
{
    public Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function convertirEnRole(array $ligne): Role
    {
        return new Role((int) $ligne["id"], $ligne["nom"]);
    }

    public function getRoles(): array
    {
        $lignes = $this->db->getAllTables("roles");

        $roles = [];

        foreach ($lignes as $ligne) {
            $roles[] = $this->convertirEnRole($ligne);
        }

        return $roles;
    }

public function getRoleParId(int $id): ?Role
{
    $sql = "SELECT * FROM roles WHERE id = :id";

    $ligne = $this->db->executeQuery($sql, [
        "id" => $id
    ]);

    if (!$ligne) {
        return null;
    }

    return $this->convertirEnRole($ligne);
}

public function getRoleParNom(string $nom): ?Role
{
    $sql = "SELECT * FROM roles WHERE nom = :nom";

    $ligne = $this->db->executeQuery($sql, [
        "nom" => $nom
    ]);

    if (!$ligne) {
        return null;
    }

    return $this->convertirEnRole($ligne);
}
}
