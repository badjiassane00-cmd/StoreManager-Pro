<?php
require_once __DIR__ . "/Fournisseur.php";
require_once __DIR__ . "/Utilisateur.php";
require_once __DIR__ . "/LigneBonLivraison.php";

class BonLivraison
{
    public const PAYEE = "PAYEE";
    public const IMPAYEE = "IMPAYEE";

    private int $id;
    private Fournisseur $fournisseur;
    private Utilisateur $utilisateur;
    private string $dateReception;
    private float $montantFacture;
    private string $statutReglement;

  
    private array $lignes;

    public function __construct(int $id = 0, ?Fournisseur $fournisseur = null, ?Utilisateur $utilisateur = null, ?string $dateReception = null)
    {
        $this->id = $id;
        $this->fournisseur = $fournisseur ?? new Fournisseur();
        $this->utilisateur = $utilisateur ?? new Utilisateur();
        $this->dateReception = $dateReception ?? date("Y-m-d H:i:s");
        $this->montantFacture = 0.0;
        $this->statutReglement = self::IMPAYEE;
        $this->lignes = [];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFournisseur(): Fournisseur
    {
        return $this->fournisseur;
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function getDateReception(): string
    {
        return $this->dateReception;
    }

    public function getMontantFacture(): float
    {
        return $this->montantFacture;
    }

    public function getStatutReglement(): string
    {
        return $this->statutReglement;
    }

    /**
     * @return LigneBonLivraison[]
     */
    public function getLignes(): array
    {
        return $this->lignes;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setStatutReglement(string $statutReglement): void
    {
        $this->statutReglement = $statutReglement;
    }

  

    public function ajouterLigne(LigneBonLivraison $ligne): void
    {
        $this->lignes[] = $ligne;
    }

    public function receptionner(): void
    {
        $total = 0.0;

        foreach ($this->lignes as $ligne) {
            $total = $total + $ligne->calculerSousTotal();
        }

        $this->montantFacture = $total;
    }
}
