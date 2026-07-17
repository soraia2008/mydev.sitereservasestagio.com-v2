<?php

class Reserva
{
    private ?int $id;
    private int $idUtilizador;
    private int $idSala;
    private bool $supportNeeded;
    private ?int $nParticipantes;
    private ?string $acao; // título/motivo da reserva
    private ?string $observacoes;
    private string $dataReserva;   // formato 'YYYY-MM-DD'
    private string $horaInicio;    // formato 'HH:MM:SS'
    private string $horaFim;       // formato 'HH:MM:SS'
    private string $estado;        // 'pendente' | 'confirmada' | 'cancelada'

    /** @var Equipamento[] Preenchido pelo DAO quando necessário, não é obrigatório */
    private array $equipamentos = [];

    public function __construct(
        ?int $id,
        int $idUtilizador,
        int $idSala,
        bool $supportNeeded,
        ?int $nParticipantes,
        ?string $acao,
        ?string $observacoes,
        string $dataReserva,
        string $horaInicio,
        string $horaFim,
        string $estado = 'pendente'
    ) {
        $this->id = $id;
        $this->idUtilizador = $idUtilizador;
        $this->idSala = $idSala;
        $this->supportNeeded = $supportNeeded;
        $this->nParticipantes = $nParticipantes;
        $this->acao = $acao;
        $this->observacoes = $observacoes;
        $this->dataReserva = $dataReserva;
        $this->horaInicio = $horaInicio;
        $this->horaFim = $horaFim;
        $this->estado = $estado;
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int) $row['id'],
            (int) $row['id_utilizador'],
            (int) $row['id_sala'],
            (bool) $row['support_needed'],
            isset($row['n_participantes']) ? (int) $row['n_participantes'] : null,
            $row['acao'] ?? null,
            $row['observacoes'] ?? null,
            $row['data_reserva'],
            $row['hora_inicio'],
            $row['hora_fim'],
            $row['estado'] ?? 'pendente'
        );
    }

    // ----- Getters -----
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdUtilizador(): int
    {
        return $this->idUtilizador;
    }

    public function getIdSala(): int
    {
        return $this->idSala;
    }

    public function isSupportNeeded(): bool
    {
        return $this->supportNeeded;
    }

    public function getNParticipantes(): ?int
    {
        return $this->nParticipantes;
    }

    public function getAcao(): ?string
    {
        return $this->acao;
    }

    public function getObservacoes(): ?string
    {
        return $this->observacoes;
    }

    public function getDataReserva(): string
    {
        return $this->dataReserva;
    }

    public function getHoraInicio(): string
    {
        return $this->horaInicio;
    }

    public function getHoraFim(): string
    {
        return $this->horaFim;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    /** @return Equipamento[] */
    public function getEquipamentos(): array
    {
        return $this->equipamentos;
    }

    // ----- Setters -----
    public function setEstado(string $estado): void
    {
        $this->estado = $estado;
    }

    /** @param Equipamento[] $equipamentos */
    public function setEquipamentos(array $equipamentos): void
    {
        $this->equipamentos = $equipamentos;
    }
}