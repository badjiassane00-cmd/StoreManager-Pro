<?php

require_once dirname(__DIR__) . "/Core/Database.php";
require_once __DIR__ . "/Entity/Dette.php";
require_once __DIR__ . "/ClientModel.php";
require_once __DIR__ . "/CommandeModel.php";

class DetteModel
{
    private static ?Database $db = null;

    private static function getDb(): Database
    {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }

        return self::$db;
    }

    private static function convertirEnDette(array $ligne): Dette
    {
        $commande = CommandeModel::getCommandeParId(
            (int) $ligne["commande_id"]
        );

        $client = ClientModel::getClientParId(
            (int) $ligne["client_id"]
        );

        return new Dette(
            (int) $ligne["id"],
            $commande,
            $client,
            (float) $ligne["montant_initial"],
            (float) $ligne["montant_paye"]
        );
    }

    public static function creerDette(Dette $dette): int
    {
        $sql = "INSERT INTO dettes
                (commande_id, client_id, montant_initial, montant_paye, statut)
                VALUES
                (:commande_id, :client_id, :montant_initial, :montant_paye, :statut)";

        return self::getDb()->executeUpdate($sql, [
            "commande_id" => $dette->getCommande()->getId(),
            "client_id" => $dette->getClient()->getId(),
            "montant_initial" => $dette->getMontantInitial(),
            "montant_paye" => $dette->getMontantPaye(),
            "statut" => $dette->getStatut()
        ]);
    }

    public static function getDetteParId(int $id): ?Dette
    {
        $sql = "SELECT * FROM dettes WHERE id = :id";

        $ligne = self::getDb()->executeQuery($sql, [
            "id" => $id
        ]);

        if (!$ligne) {
            return null;
        }

        return self::convertirEnDette($ligne);
    }

    public static function getDettesParClient(int $clientId): array
    {
        $sql = "SELECT * FROM dettes
                WHERE client_id = :client_id
                ORDER BY id DESC";

        $lignes = self::getDb()->executeQuery($sql, [
            "client_id" => $clientId
        ], false);

        $dettes = [];

        foreach ($lignes as $ligne) {
            $dettes[] = self::convertirEnDette($ligne);
        }

        return $dettes;
    }
}