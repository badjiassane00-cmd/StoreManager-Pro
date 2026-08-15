<?php

class Produit
{
    private int $id;
    private string $nom;
    private float $prixUnitaire;
    private int $quantiteStock;
    private int $seuilAlerte;

    public function __construct(int $id = 0, string $nom = "", float $prixUnitaire = 0.0, int $quantiteStock = 0, int $seuilAlerte = 5)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->prixUnitaire = $prixUnitaire;
        $this->quantiteStock = $quantiteStock;
        $this->seuilAlerte = $seuilAlerte;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getPrixUnitaire(): float
    {
        return $this->prixUnitaire;
    }

    public function getQuantiteStock(): int
    {
        return $this->quantiteStock;
    }

    public function getSeuilAlerte(): int
    {
        return $this->seuilAlerte;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function setPrixUnitaire(float $prixUnitaire): void
    {
        $this->prixUnitaire = $prixUnitaire;
    }

    public function setSeuilAlerte(int $seuilAlerte): void
    {
        $this->seuilAlerte = $seuilAlerte;
    }


    public function estEnRupture(): bool
    {
        return $this->quantiteStock <= $this->seuilAlerte;
    }

    public function decrementerStock(int $qte): void
    {
        if ($qte <= 0) {
            throw new InvalidArgumentException("La quantité à décrémenter doit être positive.");
        }

        if ($qte > $this->quantiteStock) {
            throw new RuntimeException("Stock insuffisant pour le produit \"".$this->nom."\".");
        }

        $this->quantiteStock = $this->quantiteStock - $qte;
    }

    public function incrementerStock(int $qte): void
    {
        if ($qte <= 0) {
            throw new InvalidArgumentException("La quantité à incrémenter doit être positive.");
        }

        $this->quantiteStock = $this->quantiteStock + $qte;
    }
}
