<?php
require_once dirname(__DIR__)."/Core/Database.php";
require_once "Entity/Client.php";

class ClientModel
{
    public Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function convertirEnClient(array $ligne): Client
    {
        return new Client(
            (int) $ligne["id"],
            $ligne["nom"],
            $ligne["telephone"],
            $ligne["email"],
            (float) $ligne["limite_credit"]
        );
    }

    public function getClients(): array
    {
        $lignes = $this->db->getAllTables("clients");

        $clients = [];

        foreach ($lignes as $ligne) {
            $clients[] = $this->convertirEnClient($ligne);
        }

        return $clients;
    }

   public function getClientParId(int $id): ?Client
{
    $sql = "SELECT * FROM clients WHERE id = :id";

    $ligne = $this->db->executeQuery($sql, [
        "id" => $id
    ]);

    if (!$ligne) {
        return null;
    }

    return $this->convertirEnClient($ligne);
}

   
    public function calculerEncoursTotal(int $clientId): float
    {
        $sql = "SELECT COALESCE(SUM(montant_initial - montant_paye), 0) AS encours
                FROM dettes
                WHERE client_id = :client_id AND statut != :statut_soldee";

        $ligne = $this->db->executeQuery($sql, [
            "client_id" => $clientId,
            "statut_soldee" => "SOLDEE",
        ]);

        return (float) ($ligne["encours"] ?? 0);
    }

    public function saveClient(Client $client): int
    {
        $sql = "INSERT INTO clients (nom, telephone, email, limite_credit)
                VALUES (:nom, :telephone, :email, :limite_credit)";

        return $this->db->executeUpdate($sql, [
            "nom" => $client->getNom(),
            "telephone" => $client->getTelephone(),
            "email" => $client->getEmail(),
            "limite_credit" => $client->getLimiteCredit(),
        ]);
    }

    public function updateClient(Client $client): int
    {
        $sql = "UPDATE clients
                SET nom = :nom, telephone = :telephone, email = :email, limite_credit = :limite_credit
                WHERE id = :id";

        return $this->db->executeUpdate($sql, [
            "nom" => $client->getNom(),
            "telephone" => $client->getTelephone(),
            "email" => $client->getEmail(),
            "limite_credit" => $client->getLimiteCredit(),
            "id" => $client->getId(),
        ]);
    }
}
