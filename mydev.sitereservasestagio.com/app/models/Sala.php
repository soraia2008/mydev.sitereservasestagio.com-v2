<?php

class Sala
{
    private ?int $id;
    private string $nome;
    private ?string $tipo;
    private int $capacidade;
    private bool $ocupada;
    private bool $ativa;

    public function __construct(
        ?int $id,
        string $nome,
        ?string $tipo,
        int $capacidade,
        bool $ocupada = false,
        bool $ativa = true
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->tipo = $tipo;
        $this->capacidade = $capacidade;
        $this->ocupada = $ocupada;
        $this->ativa = $ativa;
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int) $row['id'],
            $row['nome'],
            $row['tipo'] ?? null,
            (int) $row['capacidade'],
            (bool) $row['ocupada'],
            (bool) $row['ativa']
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

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function getCapacidade(): int
    {
        return $this->capacidade;
    }

    public function isOcupada(): bool
    {
        return $this->ocupada;
    }

    public function isAtiva(): bool
    {
        return $this->ativa;
    }

    // ----- Setters -----
    public function setNome(string $nome): void
    {
        $this->nome = $nome;
    }

    public function setCapacidade(int $capacidade): void
    {
        $this->capacidade = $capacidade;
    }

    public function setOcupada(bool $ocupada): void
    {
        $this->ocupada = $ocupada;
    }
}