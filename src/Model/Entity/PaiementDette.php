<?php
require_once "Dette.php";

class PaiementDette
{
    public const ESPECES = "ESPECES";
    public const WAVE = "WAVE";
    public const OM = "OM";
    public const CARTE = "CARTE";
    public const AUTRE = "AUTRE";

    private int $id;
    private Dette $dette;
    private float $montant;
    private string $datePaiement;
    private string $modePaiement;

    public function __construct(int $id = 0, ?Dette $dette = null, float $montant = 0.0, string $modePaiement = self::ESPECES, ?string $datePaiement = null)
    {
        $this->id = $id;
        $this->dette = $dette ?? new Dette();
        $this->montant = $montant;
        $this->modePaiement = $modePaiement;
        $this->datePaiement = $datePaiement ?? date("Y-m-d H:i:s");
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDette(): Dette
    {
        return $this->dette;
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
