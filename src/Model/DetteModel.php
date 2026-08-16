<?php
require_once dirname(__DIR__) . "/Core/Database.php";
require_once __DIR__ . "/Entity/Dette.php";
require_once __DIR__ . "/ClientModel.php";
require_once __DIR__ . "/CommandeModel.php";

class DetteModel
{
    public Database $db;

    private ClientModel $clientModel;
    private CommandeModel $commandeModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->clientModel = new ClientModel();
        $this->commandeModel = new CommandeModel();
    }

    private function convertirEnDette(array $ligne): Dette
    {
        $commande = $this->commandeModel->getCommandeParId((int) $ligne["commande_id"]);
        $client = $this->clientModel->getClientParId((int) $ligne["client_id"]);

        return new Dette(
            (int) $ligne["id"],
            $commande,
            $client,
            (float) $ligne["montant_initial"],
            (float) $ligne["montant_paye"]
        );
    }

    public function creerDette(Dette $dette): int
    {
        $sql = "INSERT INTO dettes (commande_id, client_id, montant_initial, montant_paye, statut)
                VALUES (:commande_id, :client_id, :montant_initial, :montant_paye, :statut)";

        return $this->db->executeUpdate($sql, [
            "commande_id" => $dette->getCommande()->getId(),
            "client_id" => $dette->getClient()->getId(),
            "montant_initial" => $dette->getMontantInitial(),
            "montant_paye" => $dette->getMontantPaye(),
            "statut" => $dette->getStatut(),
        ]);
    }

   public function getDetteParId(int $id): ?Dette
{
    $sql = "SELECT * FROM dettes WHERE id = :id";

    $ligne = $this->db->executeQuery($sql, [
        "id" => $id
    ]);

    if (!$ligne) {
        return null;
    }

    return $this->convertirEnDette($ligne);
}

public function getDettesParClient(int $clientId): array
{
    $sql = "SELECT * FROM dettes
            WHERE client_id = :client_id
            ORDER BY id DESC";

    $lignes = $this->db->executeQuery($sql, [
        "client_id" => $clientId
    ], false);

    $dettes = [];

    foreach ($lignes as $ligne) {
        $dettes[] = $this->convertirEnDette($ligne);
    }

    return $dettes;
}
}
