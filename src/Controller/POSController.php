<?php
require_once dirname(__DIR__)."/Model/ProduitModel.php";
require_once dirname(__DIR__)."/Model/ClientModel.php";
require_once dirname(__DIR__)."/Model/CommandeModel.php";
require_once dirname(__DIR__)."/Service/VenteService.php";
require_once dirname(__DIR__)."/Core/SessionManager.php";

class POSController
{
    public ProduitModel $produitModel;
    public ClientModel $clientModel;
    public CommandeModel $commandeModel;
    public VenteService $venteService;
    public SessionManager $session;

    public function __construct()
    {
        $this->produitModel = new ProduitModel();
        $this->clientModel = new ClientModel();
        $this->commandeModel = new CommandeModel();
        $this->venteService = new VenteService();
        $this->session = new SessionManager();
    }

  
    public function index(): void
    {
        $panierSession = $this->session->get("pos_panier", []);
        $lignesPanier = $this->construireLignesPanier($panierSession);
        $totalPanier = $this->calculerTotalPanier($lignesPanier);

        $clientId = $this->session->get("pos_client_id");
        $client = $clientId !== null ? $this->clientModel->getClientParId($clientId) : null;
        $encoursClient = $client !== null ? $this->clientModel->calculerEncoursTotal($client->getId()) : 0.0;

        $datas = [
            "produits" => $this->produitModel->getProduits(),
            "clients" => $this->clientModel->getClients(),
            "commandes" => $this->commandeModel->getCommandes(),
            "lignesPanier" => $lignesPanier,
            "totalPanier" => $totalPanier,
            "clientSelectionne" => $client,
            "encoursClient" => $encoursClient,
            "erreur" => $this->session->get("pos_erreur"),
            "succes" => $this->session->get("pos_succes"),
        ];

        $this->session->unset("pos_erreur");
        $this->session->unset("pos_succes");

       require_once dirname(__DIR__) . "/Views/Pos/index.php";
    }

    
    public function choisirClient(): void
    {
        $clientId = !empty($_POST["client_id"]) ? (int) $_POST["client_id"] : null;
        $this->session->set("pos_client_id", $clientId);

        $this->rediriger();
    }

    
    public function ajouterArticle(): void
    {
        $produitId = (int) ($_POST["produit_id"] ?? 0);
        $quantite = (int) ($_POST["quantite"] ?? 0);

        $produit = $this->produitModel->getProduitParId($produitId);

        if ($produit === null) {
            $this->session->set("pos_erreur", "Produit introuvable.");
            $this->rediriger();
        }

        if ($quantite <= 0) {
            $this->session->set("pos_erreur", "Quantité invalide.");
            $this->rediriger();
        }

        $panier = $this->session->get("pos_panier", []);
        $quantiteDejaDansPanier = $panier[$produitId] ?? 0;
        $quantiteTotale = $quantiteDejaDansPanier + $quantite;

        if ($quantiteTotale > $produit->getQuantiteStock()) {
            $this->session->set("pos_erreur", "Stock insuffisant pour \"".$produit->getNom()."\" (disponible : ".$produit->getQuantiteStock().").");
            $this->rediriger();
        }

        $panier[$produitId] = $quantiteTotale;
        $this->session->set("pos_panier", $panier);

        $this->rediriger();
    }

    
    public function retirerArticle(): void
    {
        $produitId = (int) ($_POST["produit_id"] ?? 0);

        $panier = $this->session->get("pos_panier", []);
        unset($panier[$produitId]);
        $this->session->set("pos_panier", $panier);

        $this->rediriger();
    }

    public function viderPanier(): void
    {
        $this->session->unset("pos_panier");
        $this->session->unset("pos_client_id");

        $this->rediriger();
    }

  
    public function creerVente(): void
    {
        $panier = $this->session->get("pos_panier", []);
        $clientId = $this->session->get("pos_client_id");
        $montantVerse = isset($_POST["montant_verse"]) ? (float) $_POST["montant_verse"] : 0.0;

        $utilisateurId = (int) $this->session->get("utilisateur_id", 2);

        try {
            if (count($panier) === 0) {
                throw new RuntimeException("Le panier est vide. Ajoutez au moins un article.");
            }

            $commande = $this->venteService->construireCommande($clientId, $utilisateurId, $panier, $montantVerse);
            $commandeId = $this->venteService->validerVente($commande);

            $this->session->unset("pos_panier");
            $this->session->unset("pos_client_id");
            $this->session->set("pos_succes", "Vente #".$commandeId." enregistrée avec succès.");
        } catch (Exception $exception) {
            $this->session->set("pos_erreur", $exception->getMessage());
        }

        $this->rediriger();
    }

   
    private function construireLignesPanier(array $panierSession): array
    {
        $lignes = [];

        foreach ($panierSession as $produitId => $quantite) {
            $produit = $this->produitModel->getProduitParId((int) $produitId);

            if ($produit === null) {
                continue;
            }

            $lignes[] = [
                "produit" => $produit,
                "quantite" => $quantite,
                "sousTotal" => $produit->getPrixUnitaire() * $quantite,
            ];
        }

        return $lignes;
    }

    private function calculerTotalPanier(array $lignesPanier): float
    {
        $total = 0.0;

        foreach ($lignesPanier as $ligne) {
            $total = $total + $ligne["sousTotal"];
        }

        return $total;
    }

    private function rediriger(): void
    {
        header("Location: /pos");
        exit;
    }
}
