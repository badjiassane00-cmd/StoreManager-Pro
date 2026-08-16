<?php
require_once __DIR__ . "/Fournisseur.php";
require_once __DIR__ . "/BonLivraison.php";

class PaiementFournisseur
{
    public const ESPECES = "ESPECES";
    public const WAVE = "WAVE";
    public const OM = "OM";
    public const CARTE = "CARTE";
    public const AUTRE = "AUTRE";

    private int $id;
    private Fournisseur $fournisseur;
    private ?BonLivraison $bonLivraison;
    private float $montant;
    private string $datePaiement;
    private string $modePaiement;

    public function __construct(int $id = 0, ?Fournisseur $fournisseur = null, ?BonLivraison $bonLivraison = null, float $montant = 0.0, string $modePaiement = self::ESPECES, ?string $datePaiement = null)
    {
        $this->id = $id;
        $this->fournisseur = $fournisseur ?? new Fournisseur();
        $this->bonLivraison = $bonLivraison;
        $this->montant = $montant;
        $this->modePaiement = $modePaiement;
        $this->datePaiement = $datePaiement ?? date("Y-m-d H:i:s");
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFournisseur(): Fournisseur
    {
        return $this->fournisseur;
    }

    public function getBonLivraison(): ?BonLivraison
    {
        return $this->bonLivraison;
    }

    public function getMontant(): float
    {
        return $this->montant;
    }

    public function getDatePaiement(): string
    {
        return $this->datePaiement;
    }

    public function getModePaiement(): string
    {
        return $this->modePaiement;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
