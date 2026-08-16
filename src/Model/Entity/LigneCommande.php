<?php
require_once __DIR__ . "/Commande.php";
require_once __DIR__ . "/Produit.php";

class LigneCommande
{
    private int $id;
    private Commande $commande;
    private Produit $produit;
    private int $quantite;
    private float $prixUnitaire;

    public function __construct(int $id = 0, ?Commande $commande = null, ?Produit $produit = null, int $quantite = 0, float $prixUnitaire = 0.0)
    {
        $this->id = $id;
        $this->commande = $commande ?? new Commande();
        $this->produit = $produit ?? new Produit();
        $this->quantite = $quantite;
        $this->prixUnitaire = $prixUnitaire;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCommande(): Commande
    {
        return $this->commande;
    }

    public function getProduit(): Produit
    {
        return $this->produit;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function getPrixUnitaire(): float
    {
        return $this->prixUnitaire;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }


    public function calculerSousTotal(): float
    {
        return $this->quantite * $this->prixUnitaire;
    }
}
