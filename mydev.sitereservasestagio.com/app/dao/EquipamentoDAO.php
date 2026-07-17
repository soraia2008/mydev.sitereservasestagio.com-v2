<?php

require_once __DIR__ . '/../config/DatabaseSingle.php';
require_once __DIR__ . '/../models/Equipamento.php';

class EquipamentoDAO
{
    private PDO $conexao;

    public function __construct()
    {
        $this->conexao = DatabaseSingle::connect();
    }

    /**
     * Lista todos os equipamentos, já com o nome do tipo (via JOIN),
     * para o formulário "Nova Reserva" conseguir agrupar os checkboxes
     * por categoria (ex: "Projeção" -> Tela, TV, Projetor...).
     *
     * @return Equipamento[]
     */
    public function getAll(): array
    {
        $stmt = $this->conexao->query(
            'SELECT e.id, e.nome, e.id_tipo, t.nome AS nome_tipo
             FROM Equipamentos e
             JOIN Tipos t ON t.id = e.id_tipo
             ORDER BY t.nome ASC, e.nome ASC'
        );

        return array_map(fn(array $row) => Equipamento::fromArray($row), $stmt->fetchAll());
    }

    public function getById(int $id): ?Equipamento
    {
        $stmt = $this->conexao->prepare(
            'SELECT e.id, e.nome, e.id_tipo, t.nome AS nome_tipo
             FROM Equipamentos e
             JOIN Tipos t ON t.id = e.id_tipo
             WHERE e.id = :id'
        );
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        return $row ? Equipamento::fromArray($row) : null;
    }

    /**
     * Devolve todos os equipamentos associados a uma reserva específica.
     * Útil para o controller de Reservas mostrar o que foi pedido em cada uma.
     *
     * @return Equipamento[]
     */
    public function getByReserva(int $idReserva): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT e.id, e.nome, e.id_tipo, t.nome AS nome_tipo
             FROM Equipamentos e
             JOIN Tipos t ON t.id = e.id_tipo
             JOIN Reserva_Equipamentos re ON re.id_equipamento = e.id
             WHERE re.id_reserva = :id_reserva
             ORDER BY e.nome ASC'
        );
        $stmt->execute(['id_reserva' => $idReserva]);

        return array_map(fn(array $row) => Equipamento::fromArray($row), $stmt->fetchAll());
    }

    public function create(Equipamento $equipamento): int
    {
        $stmt = $this->conexao->prepare(
            'INSERT INTO Equipamentos (nome, id_tipo) VALUES (:nome, :id_tipo)'
        );

        $stmt->execute([
            'nome' => $equipamento->getNome(),
            'id_tipo' => $equipamento->getIdTipo(),
        ]);

        return (int) $this->conexao->lastInsertId();
    }
}