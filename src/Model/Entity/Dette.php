<?php
require_once __DIR__ . "/Commande.php";
require_once __DIR__ . "/Client.php";

class Dette
{
    public const NON_SOLDEE = "NON_SOLDEE";
    public const PARTIELLE = "PARTIELLE";
    public const SOLDEE = "SOLDEE";

    private int $id;
    private Commande $commande;
    private Client $client;
    private float $montantInitial;
    private float $montantPaye;
    private string $statut;

    public function __construct(int $id = 0, ?Commande $commande = null, ?Client $client = null, float $montantInitial = 0.0, float $montantPaye = 0.0)
    {
        $this->id = $id;
        $this->commande = $commande ?? new Commande();
        $this->client = $client ?? new Client();
        $this->montantInitial = $montantInitial;
        $this->montantPaye = $montantPaye;
        $this->statut = self::NON_SOLDEE;
        $this->mettreAJourStatut();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCommande(): Commande
    {
        return $this->commande;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getMontantInitial(): float
    {
        return $this->montantInitial;
    }

    public function getMontantPaye(): float
    {
        return $this->montantPaye;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }



    public function rembourser(float $montant): void
    {
        if ($montant <= 0) {
            throw new InvalidArgumentException("Le montant remboursé doit être positif.");
        }

        $resteAvant = $this->calculerReste();

        if ($montant > $resteAvant) {
            throw new RuntimeException("Le montant remboursé dépasse le reste dû.");
        }

        $this->montantPaye = $this->montantPaye + $montant;
        $this->mettreAJourStatut();
    }

    public function calculerReste(): float
    {
        $reste = $this->montantInitial - $this->montantPaye;

        if ($reste < 0) {
            $reste = 0.0;
        }

        return $reste;
    }

    public function mettreAJourStatut(): void
    {
        $reste = $this->calculerReste();

        if ($reste <= 0) {
            $this->statut = self::SOLDEE;
        } elseif ($this->montantPaye > 0) {
            $this->statut = self::PARTIELLE;
        } else {
            $this->statut = self::NON_SOLDEE;
        }
    }
}
