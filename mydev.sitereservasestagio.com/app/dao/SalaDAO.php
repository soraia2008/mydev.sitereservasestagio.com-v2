<?php

require_once __DIR__ . '/../config/DatabaseSingle.php';
require_once __DIR__ . '/../models/Sala.php';

class SalaDAO
{
    private PDO $conexao;

    public function __construct()
    {
        $this->conexao = DatabaseSingle::connect();
    }

    /**
     * @return Sala[]
     */
    public function getAll(): array
    {
        $stmt = $this->conexao->query(
            'SELECT * FROM Salas WHERE ativa = 1 ORDER BY nome ASC'
        );

        return array_map(fn(array $row) => Sala::fromArray($row), $stmt->fetchAll());
    }

    public function getById(int $id): ?Sala
    {
        $stmt = $this->conexao->prepare('SELECT * FROM Salas WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        return $row ? Sala::fromArray($row) : null;
    }

    public function create(Sala $sala): int
    {
        $stmt = $this->conexao->prepare(
            'INSERT INTO Salas (nome, tipo, capacidade, ocupada, ativa)
             VALUES (:nome, :tipo, :capacidade, :ocupada, 1)'
        );

        $stmt->execute([
            'nome' => $sala->getNome(),
            'tipo' => $sala->getTipo(),
            'capacidade' => $sala->getCapacidade(),
            'ocupada' => (int) $sala->isOcupada(),
        ]);

        return (int) $this->conexao->lastInsertId();
    }

    /**
     * Atualiza o estado "ocupada" de uma sala.
     * Nota: como discutimos, o ideal é calcular isto em tempo real a partir das
     * Reservas em vez de depender só deste campo — usa este método com cuidado.
     */
    public function updateOcupada(int $id, bool $ocupada): void
    {
        $stmt = $this->conexao->prepare(
            'UPDATE Salas SET ocupada = :ocupada WHERE id = :id'
        );

        $stmt->execute([
            'ocupada' => (int) $ocupada,
            'id' => $id,
        ]);
    }
}