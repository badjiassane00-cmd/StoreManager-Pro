<?php

require_once dirname(__DIR__) . "/Core/Database.php";
require_once dirname(__DIR__) . "/Model/Entity/Commande.php";
require_once dirname(__DIR__) . "/Model/Entity/LigneCommande.php";
require_once dirname(__DIR__) . "/Model/Entity/Dette.php";
require_once dirname(__DIR__) . "/Model/CommandeModel.php";
require_once dirname(__DIR__) . "/Model/DetteModel.php";
require_once dirname(__DIR__) . "/Model/ProduitModel.php";
require_once dirname(__DIR__) . "/Model/ClientModel.php";
require_once dirname(__DIR__) . "/Model/UtilisateurModel.php";

class VenteService
{
    private static ?Database $db = null;

    private static function getDb(): Database
    {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }

        return self::$db;
    }

    public static function construireCommande(
        ?int $clientId,
        int $utilisateurId,
        array $panier,
        float $montantVerse
    ): Commande {

        $client = $clientId !== null
            ? ClientModel::getClientParId($clientId)
            : null;

        $utilisateur = UtilisateurModel::getUtilisateurParId(
            $utilisateurId
        );

        if ($utilisateur === null) {
            throw new RuntimeException(
                "Utilisateur introuvable (id " . $utilisateurId . ")."
            );
        }

        $commande = new Commande(
            0,
            $client,
            $utilisateur,
            $montantVerse
        );

        foreach ($panier as $produitId => $quantite) {

            $produit = ProduitModel::getProduitParId(
                (int) $produitId
            );

            if ($produit === null) {
                throw new RuntimeException(
                    "Produit introuvable (id " . $produitId . ")."
                );
            }

            $ligne = new LigneCommande(
                0,
                $commande,
                $produit,
                (int) $quantite,
                $produit->getPrixUnitaire()
            );

            $commande->ajouterLigne($ligne);
        }

        $commande->calculerTotal();
        $commande->determinerStatutPaiement();

        return $commande;
    }

    public static function validerVente(Commande $commande): int
    {
        if (!$commande->enregistrer()) {
            throw new RuntimeException(
                "La commande est vide ou son montant total est invalide."
            );
        }

        self::verifierDisponibiliteStock($commande);
        self::verifierLimiteCredit($commande);

        $pdo = self::getDb()->connexion();

        $pdo->beginTransaction();

        try {

            $commandeId = CommandeModel::creerCommande($commande);

            foreach ($commande->getLignes() as $ligne) {

                CommandeModel::ajouterLigne(
                    $commandeId,
                    $ligne
                );

                self::decrementerStockProduit(
                    $ligne->getProduit()->getId(),
                    $ligne->getQuantite()
                );
            }

            $resteAPayer = $commande->calculerResteAPayer();

            if (
                $resteAPayer > 0 &&
                $commande->getClient() !== null
            ) {

                $commandePersistee = CommandeModel::getCommandeParId(
                    $commandeId
                );

                $dette = new Dette(
                    0,
                    $commandePersistee,
                    $commande->getClient(),
                    $resteAPayer
                );

                DetteModel::creerDette($dette);
            }

            $pdo->commit();

            return $commandeId;

        } catch (Exception $exception) {

            $pdo->rollBack();

            throw $exception;
        }
    }

    private static function verifierDisponibiliteStock(
        Commande $commande
    ): void {

        foreach ($commande->getLignes() as $ligne) {

            $produit = ProduitModel::getProduitParId(
                $ligne->getProduit()->getId()
            );

            if ($produit === null) {
                throw new RuntimeException(
                    "Produit introuvable (id " .
                    $ligne->getProduit()->getId() .
                    ")."
                );
            }

            if (
                $ligne->getQuantite() >
                $produit->getQuantiteStock()
            ) {

                throw new RuntimeException(
                    "Stock insuffisant pour \"" .
                    $produit->getNom() .
                    "\" (disponible : " .
                    $produit->getQuantiteStock() .
                    ")."
                );
            }
        }
    }

    private static function verifierLimiteCredit(
        Commande $commande
    ): void {

        $resteAPayer = $commande->calculerResteAPayer();

        if ($resteAPayer <= 0) {
            return;
        }

        $client = $commande->getClient();

        if ($client === null) {
            throw new RuntimeException(
                "Une vente à crédit doit être rattachée à un client."
            );
        }

        $encoursActuel = ClientModel::calculerEncoursTotal(
            $client->getId()
        );

        $peutEmprunter = $client->peutEmprunter(
            $resteAPayer,
            $encoursActuel
        );

        if (!$peutEmprunter) {
            throw new RuntimeException(
                "Limite de crédit dépassée pour \"" .
                $client->getNom() .
                "\" (limite : " .
                $client->getLimiteCredit() .
                " FCFA)."
            );
        }
    }

    private static function decrementerStockProduit(
        int $produitId,
        int $quantite
    ): void {

        $produit = ProduitModel::getProduitParId($produitId);

        if ($produit === null) {
            throw new RuntimeException(
                "Produit introuvable (id " . $produitId . ")."
            );
        }

        $produit->decrementerStock($quantite);

        ProduitModel::mettreAJourStock(
            $produitId,
            $produit->getQuantiteStock()
        );
    }
}