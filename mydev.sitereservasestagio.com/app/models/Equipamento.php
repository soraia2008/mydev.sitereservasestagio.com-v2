<?php

class Equipamento
{
    private ?int $id;
    private string $nome;
    private int $idTipo;
    private ?string $nomeTipo; // opcional: preenchido quando o DAO faz JOIN com Tipos

    public function __construct(
        ?int $id,
        string $nome,
        int $idTipo,
        ?string $nomeTipo = null
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->idTipo = $idTipo;
        $this->nomeTipo = $nomeTipo;
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int) $row['id'],
            $row['nome'],
            (int) $row['id_tipo'],
            $row['nome_tipo'] ?? null
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

    public function getIdTipo(): int
    {
        return $this->idTipo;
    }

    public function getNomeTipo(): ?string
    {
        return $this->nomeTipo;
    }
}