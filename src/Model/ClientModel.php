<?php
require_once dirname(__DIR__)."/Core/Database.php";
require_once __DIR__ . "/Entity/Client.php";

class ClientModel
{
    public static  ?Database $db=null;

    private static function getDb(): Database{
        if(self::$db === null){
            self::$db = Database ::getInstance();
        }
    }

    private static function convertirEnClient(array $ligne): Client
    {
        return new Client(
            (int) $ligne["id"],
            $ligne["nom"],
            $ligne["telephone"],
            $ligne["email"],
            (float) $ligne["limite_credit"]
        );
    }

    public static function getClients(): array
    {
        $lignes = self ::getDb()->getAllTables("clients");

        $clients = [];

        foreach ($lignes as $ligne) {
            $clients[] = self::convertirEnClient($ligne);
        }

        return $clients;
    }

   public static function  getClientParId(int $id): ?Client
{
    $sql = "SELECT * FROM clients WHERE id = :id";

    $ligne = self::getDb()->executeQuery($sql, [
        "id" => $id
    ]);

    if (!$ligne) {
        return null;
    }

    return self::convertirEnClient($ligne);
}

   
    public static function calculerEncoursTotal(int $clientId): float
    {
        $sql = "SELECT COALESCE(SUM(montant_initial - montant_paye), 0) AS encours
                FROM dettes
                WHERE client_id = :client_id AND statut != :statut_soldee";

        $ligne = self::getDb()->executeQuery($sql, [
            "client_id" => $clientId,
            "statut_soldee" => "SOLDEE",
        ]);

        return (float) ($ligne["encours"] ?? 0);
    }

    public static function saveClient(Client $client): int
    {
        $sql = "INSERT INTO clients (nom, telephone, email, limite_credit)
                VALUES (:nom, :telephone, :email, :limite_credit)";

        return self::getDb()->executeUpdate($sql, [
            "nom" => $client->getNom(),
            "telephone" => $client->getTelephone(),
            "email" => $client->getEmail(),
            "limite_credit" => $client->getLimiteCredit(),
        ]);
    }

    public static function updateClient(Client $client): int
    {
        $sql = "UPDATE clients
                SET nom = :nom, telephone = :telephone, email = :email, limite_credit = :limite_credit
                WHERE id = :id";

        return self::getDb()->executeUpdate($sql, [
            "nom" => $client->getNom(),
            "telephone" => $client->getTelephone(),
            "email" => $client->getEmail(),
            "limite_credit" => $client->getLimiteCredit(),
            "id" => $client->getId(),
        ]);
    }
}
