<?php

class Client
{
    private int $id;
    private string $nom;
    private ?string $telephone;
    private ?string $email;
    private float $limiteCredit;

    public function __construct(int $id = 0, string $nom = "", ?string $telephone = null, ?string $email = null, float $limiteCredit = 0.0)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->telephone = $telephone;
        $this->email = $email;
        $this->limiteCredit = $limiteCredit;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getLimiteCredit(): float
    {
        return $this->limiteCredit;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function setTelephone(?string $telephone): void
    {
        $this->telephone = $telephone;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function setLimiteCredit(float $limiteCredit): void
    {
        $this->limiteCredit = $limiteCredit;
    }


   
    public function calculerEncours(array $dettesActives): float
    {
        $encours = 0.0;

        foreach ($dettesActives as $dette) {
            $encours = $encours + $dette->calculerReste();
        }

        return $encours;
    }

    public function peutEmprunter(float $montant, float $encoursActuel = 0.0): bool
    {
        $encoursApresAchat = $encoursActuel + $montant;

        return $encoursApresAchat <= $this->limiteCredit;
    }
}
