<?php
require_once dirname(__DIR__)."/Core/Database.php";
require_once "Entity/Commande.php";
require_once "Entity/LigneCommande.php";
require_once "ClientModel.php";
require_once "UtilisateurModel.php";
require_once "ProduitModel.php";

class CommandeModel
{
    public Database $db;

    private ClientModel $clientModel;
    private UtilisateurModel $utilisateurModel;
    private ProduitModel $produitModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->clientModel = new ClientModel();
        $this->utilisateurModel = new UtilisateurModel();
        $this->produitModel = new ProduitModel();
    }

    private function convertirEnCommande(array $ligne): Commande
    {
        $client = $ligne["client_id"] !== null ? $this->clientModel->getClientParId((int) $ligne["client_id"]) : null;
        $utilisateur = $this->utilisateurModel->getUtilisateurParId((int) $ligne["utilisateur_id"]);

        $commande = new Commande(
            (int) $ligne["id"],
            $client,
            $utilisateur,
            (float) $ligne["montant_verse"],
            $ligne["date_vente"]
        );

        return $commande;
    }

    private function convertirEnLigneCommande(array $ligne, Commande $commande): LigneCommande
    {
        $produit = $this->produitModel->getProduitParId((int) $ligne["produit_id"]);

        return new LigneCommande(
            (int) $ligne["id"],
            $commande,
            $produit,
            (int) $ligne["quantite"],
            (float) $ligne["prix_unitaire"]
        );
    }

  
    public function creerCommande(Commande $commande): int
    {
        $sql = "INSERT INTO commandes (date_vente, client_id, utilisateur_id, montant_total, montant_verse, statut_paiement)
                VALUES (:date_vente, :client_id, :utilisateur_id, :montant_total, :montant_verse, :statut_paiement)";

        return $this->db->executeUpdate($sql, [
            "date_vente" => $commande->getDateVente(),
            "client_id" => $commande->getClient()?->getId(),
            "utilisateur_id" => $commande->getUtilisateur()->getId(),
            "montant_total" => $commande->getMontantTotal(),
            "montant_verse" => $commande->getMontantVerse(),
            "statut_paiement" => $commande->getStatutPaiement(),
        ]);
    }

    public function ajouterLigne(int $commandeId, LigneCommande $ligne): int
    {
        $sql = "INSERT INTO lignes_commandes (commande_id, produit_id, quantite, prix_unitaire)
                VALUES (:commande_id, :produit_id, :quantite, :prix_unitaire)";

        return $this->db->executeUpdate($sql, [
            "commande_id" => $commandeId,
            "produit_id" => $ligne->getProduit()->getId(),
            "quantite" => $ligne->getQuantite(),
            "prix_unitaire" => $ligne->getPrixUnitaire(),
        ]);
    }

    public function getLignesParCommande(Commande $commande): array
    {
        $lignesSql = $this->db->executeQuery(
            "SELECT * FROM lignes_commandes WHERE commande_id = :commande_id",
            ["commande_id" => $commande->getId()],
            false
        );

        $lignes = [];

        foreach ($lignesSql as $ligneSql) {
            $lignes[] = $this->convertirEnLigneCommande($ligneSql, $commande);
        }

        return $lignes;
    }

    public function getCommandeParId(int $id): ?Commande
    {
        $ligne = $this->db->executeQuery(
            "SELECT * FROM commandes WHERE id = :id",
            ["id" => $id]
        );

        if (!$ligne) {
            return null;
        }

        $commande = $this->convertirEnCommande($ligne);

        foreach ($this->getLignesParCommande($commande) as $ligneCommande) {
            $commande->ajouterLigne($ligneCommande);
        }

        return $commande;
    }

  
    public function getCommandes(): array
    {
        $lignes = $this->db->query("SELECT * FROM commandes ORDER BY id DESC", false);

        $commandes = [];

        foreach ($lignes as $ligne) {
            $commandes[] = $this->convertirEnCommande($ligne);
        }

        return $commandes;
    }
}
