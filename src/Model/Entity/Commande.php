<?php
require_once "Client.php";
require_once "Utilisateur.php";
require_once "LigneCommande.php";

class Commande
{
    public const COMPTANT = "COMPTANT";
    public const CREDIT = "CREDIT";

    private int $id;
    private string $dateVente;
    private ?Client $client;
    private Utilisateur $utilisateur;
    private float $montantTotal;
    private float $montantVerse;
    private string $statutPaiement;

  
    private array $lignes;

    public function __construct(int $id = 0, ?Client $client = null, ?Utilisateur $utilisateur = null, float $montantVerse = 0.0, ?string $dateVente = null)
    {
        $this->id = $id;
        $this->dateVente = $dateVente ?? date("Y-m-d H:i:s");
        $this->client = $client;
        $this->utilisateur = $utilisateur ?? new Utilisateur();
        $this->montantTotal = 0.0;
        $this->montantVerse = $montantVerse;
        $this->statutPaiement = self::COMPTANT;
        $this->lignes = [];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDateVente(): string
    {
        return $this->dateVente;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function getMontantTotal(): float
    {
        return $this->montantTotal;
    }

    public function getMontantVerse(): float
    {
        return $this->montantVerse;
    }

    public function getStatutPaiement(): string
    {
        return $this->statutPaiement;
    }

 
    public function getLignes(): array
    {
        return $this->lignes;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }



    public function ajouterLigne(LigneCommande $ligne): void
    {
        $this->lignes[] = $ligne;
    }

    public function calculerTotal(): float
    {
        $total = 0.0;

        foreach ($this->lignes as $ligne) {
            $total = $total + $ligne->calculerSousTotal();
        }

        $this->montantTotal = $total;

        return $this->montantTotal;
    }

    public function determinerStatutPaiement(): string
    {
        if ($this->montantVerse >= $this->montantTotal) {
            $this->statutPaiement = self::COMPTANT;
            $this->montantVerse = $this->montantTotal;
        } else {
            $this->statutPaiement = self::CREDIT;
        }

        return $this->statutPaiement;
    }

    public function calculerResteAPayer(): float
    {
        $reste = $this->montantTotal - $this->montantVerse;

        if ($reste < 0) {
            $reste = 0.0;
        }

        return $reste;
    }

    public function enregistrer(): bool
    {
        if (count($this->lignes) === 0) {
            return false;
        }

        if ($this->calculerTotal() <= 0) {
            return false;
        }

        return true;
    }
}
