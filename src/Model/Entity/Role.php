<?php

class Role
{
    public const ADMIN = "ADMIN";
    public const VENTE = "VENTE";
    public const STOCK = "STOCK";
    public const INVENTAIRE = "INVENTAIRE";

    private int $id;
    private string $nom;

    public function __construct(int $id = 0, string $nom = self::VENTE)
    {
        $this->id = $id;
        $this->nom = $nom;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }
}
