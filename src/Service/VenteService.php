<?php
require_once dirname(__DIR__)."/Core/Database.php";
require_once dirname(__DIR__)."/Model/Entity/Commande.php";
require_once dirname(__DIR__)."/Model/Entity/LigneCommande.php";
require_once dirname(__DIR__)."/Model/Entity/Dette.php";
require_once dirname(__DIR__)."/Model/CommandeModel.php";
require_once dirname(__DIR__)."/Model/DetteModel.php";
require_once dirname(__DIR__)."/Model/ProduitModel.php";
require_once dirname(__DIR__)."/Model/ClientModel.php";
require_once dirname(__DIR__)."/Model/UtilisateurModel.php";


class VenteService
{
    private Database $db;

    private CommandeModel $commandeModel;
    private DetteModel $detteModel;
    private ProduitModel $produitModel;
    private ClientModel $clientModel;
    private UtilisateurModel $utilisateurModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->commandeModel = new CommandeModel();
        $this->detteModel = new DetteModel();
        $this->produitModel = new ProduitModel();
        $this->clientModel = new ClientModel();
        $this->utilisateurModel = new UtilisateurModel();
    }

   
    public function construireCommande(?int $clientId, int $utilisateurId, array $panier, float $montantVerse): Commande
    {
        $client = $clientId !== null ? $this->clientModel->getClientParId($clientId) : null;
        $utilisateur = $this->utilisateurModel->getUtilisateurParId($utilisateurId);

        if ($utilisateur === null) {
            throw new RuntimeException("Utilisateur introuvable (id ".$utilisateurId.").");
        }

        $commande = new Commande(0, $client, $utilisateur, $montantVerse);

        foreach ($panier as $produitId => $quantite) {
            $produit = $this->produitModel->getProduitParId((int) $produitId);

            if ($produit === null) {
                throw new RuntimeException("Produit introuvable (id ".$produitId.").");
            }

            $ligne = new LigneCommande(0, $commande, $produit, (int) $quantite, $produit->getPrixUnitaire());
            $commande->ajouterLigne($ligne);
        }

        $commande->calculerTotal();
        $commande->determinerStatutPaiement();

        return $commande;
    }

   
    public function validerVente(Commande $commande): int
    {
        if (!$commande->enregistrer()) {
            throw new RuntimeException("La commande est vide ou son montant total est invalide.");
        }

        $this->verifierDisponibiliteStock($commande);
        $this->verifierLimiteCredit($commande);

        $pdo = $this->db->connexion();
        $pdo->beginTransaction();

        try {
            $commandeId = $this->commandeModel->creerCommande($commande);

            foreach ($commande->getLignes() as $ligne) {
                $this->commandeModel->ajouterLigne($commandeId, $ligne);
                $this->decrementerStockProduit($ligne->getProduit()->getId(), $ligne->getQuantite());
            }

            $resteAPayer = $commande->calculerResteAPayer();

            if ($resteAPayer > 0 && $commande->getClient() !== null) {
                $commandePersistee = $this->commandeModel->getCommandeParId($commandeId);
                $dette = new Dette(0, $commandePersistee, $commande->getClient(), $resteAPayer);
                $this->detteModel->creerDette($dette);
            }

            $pdo->commit();

            return $commandeId;
        } catch (Exception $exception) {
            $pdo->rollBack();

            throw $exception;
        }
    }

   
    private function verifierDisponibiliteStock(Commande $commande): void
    {
        foreach ($commande->getLignes() as $ligne) {
            $produit = $this->produitModel->getProduitParId($ligne->getProduit()->getId());

            if ($produit === null) {
                throw new RuntimeException("Produit introuvable (id ".$ligne->getProduit()->getId().").");
            }

            if ($ligne->getQuantite() > $produit->getQuantiteStock()) {
                throw new RuntimeException(
                    "Stock insuffisant pour \"".$produit->getNom()."\" (disponible : ".$produit->getQuantiteStock().")."
                );
            }
        }
    }

    
    private function verifierLimiteCredit(Commande $commande): void
    {
        $resteAPayer = $commande->calculerResteAPayer();

        if ($resteAPayer <= 0) {
            return;
        }

        $client = $commande->getClient();

        if ($client === null) {
            throw new RuntimeException("Une vente à crédit doit être rattachée à un client.");
        }

        $encoursActuel = $this->clientModel->calculerEncoursTotal($client->getId());
        $peutEmprunter = $client->peutEmprunter($resteAPayer, $encoursActuel);

        if (!$peutEmprunter) {
            throw new RuntimeException(
                "Limite de crédit dépassée pour \"".$client->getNom()."\" (limite : ".$client->getLimiteCredit()." FCFA)."
            );
        }
    }

   
    private function decrementerStockProduit(int $produitId, int $quantite): void
    {
        $produit = $this->produitModel->getProduitParId($produitId);
        $produit->decrementerStock($quantite);

        $this->produitModel->mettreAJourStock($produitId, $produit->getQuantiteStock());
    }
}
