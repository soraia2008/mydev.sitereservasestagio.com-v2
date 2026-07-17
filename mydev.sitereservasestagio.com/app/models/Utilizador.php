<?php

class Utilizador
{
    private ?int $id;
    private string $nome;
    private string $email;
    private ?string $criadoEm;

    public function __construct(
        ?int $id,
        string $nome,
        string $email,
        ?string $criadoEm = null
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->email = $email;
        $this->criadoEm = $criadoEm;
    }

    /**
     * Cria um Utilizador a partir de uma linha da BD (array associativo).
     * Facilita a conversão feita pelo DAO.
     */
    public static function fromArray(array $row): self
    {
        return new self(
            (int) $row['id'],
            $row['nome'],
            $row['email'],
            $row['criado_em'] ?? null
        );
    }

    // ----- Getters -----
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCriadoEm(): ?string
    {
        return $this->criadoEm;
    }

    // ----- Setters -----
    public function setNome(string $nome): void
    {
        $this->nome = $nome;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}