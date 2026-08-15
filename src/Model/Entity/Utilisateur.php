<?php
require_once "Role.php";

class Utilisateur
{
    private int $id;
    private string $nom;
    private string $email;
    private string $motDePasse;
    private Role $role;
    private bool $actif;

    public function __construct(int $id = 0, string $nom = "", string $email = "", string $motDePasse = "", ?Role $role = null, bool $actif = true)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->email = $email;
        $this->motDePasse = $motDePasse;
        $this->role = $role ?? new Role();
        $this->actif = $actif;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getMotDePasse(): string
    {
        return $this->motDePasse;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function estActif(): bool
    {
        return $this->actif;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function setActif(bool $actif): void
    {
        $this->actif = $actif;
    }

  

    public function seConnecter(string $email, string $motDePasse): bool
    {
        if ($email !== $this->email) {
            return false;
        }

        if (!$this->actif) {
            return false;
        }

        return password_verify($motDePasse, $this->motDePasse);
    }

    public function aLeDroit(string $action): bool
    {
        if ($this->role->getNom() === Role::ADMIN) {
            return true;
        }

        $droitsParRole = [
            Role::VENTE => ["vente_creer", "client_consulter", "dette_consulter"],
            Role::STOCK => ["stock_consulter", "approvisionnement_creer"],
            Role::INVENTAIRE => ["stock_consulter", "produit_consulter"],
        ];

        $droitsAutorises = $droitsParRole[$this->role->getNom()] ?? [];

        return in_array($action, $droitsAutorises, true);
    }

    public function obtenirInitiales(): string
    {
        $parties = explode(" ", trim($this->nom));
        $premiere = $parties[0][0] ?? "";
        $derniere = $parties[count($parties) - 1][0] ?? "";

        return strtoupper($premiere.$derniere);
    }
}
