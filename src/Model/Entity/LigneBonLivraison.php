<?php
require_once "BonLivraison.php";
require_once "Produit.php";

class LigneBonLivraison
{
    private int $id;
    private BonLivraison $bonLivraison;
    private Produit $produit;
    private int $quantiteLivree;
    private float $coutAchat;

    public function __construct(int $id = 0, ?BonLivraison $bonLivraison = null, ?Produit $produit = null, int $quantiteLivree = 0, float $coutAchat = 0.0)
    {
        $this->id = $id;
        $this->bonLivraison = $bonLivraison ?? new BonLivraison();
        $this->produit = $produit ?? new Produit();
        $this->quantiteLivree = $quantiteLivree;
        $this->coutAchat = $coutAchat;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBonLivraison(): BonLivraison
    {
        return $this->bonLivraison;
    }

    public function getProduit(): Produit
    {
        return $this->produit;
    }

    public function getQuantiteLivree(): int
    {
        return $this->quantiteLivree;
    }

    public function getCoutAchat(): float
    {
        return $this->coutAchat;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
  

    public function calculerSousTotal(): float
    {
        return $this->quantiteLivree * $this->coutAchat;
    }
}
