<?php

require_once __DIR__ . '/../config/DatabaseSingle.php';
require_once __DIR__ . '/../models/Reserva.php';

class ReservaDAO
{
    private PDO $conexao;

    public function __construct()
    {
        $this->conexao = DatabaseSingle::connect();
    }

    /**
     * @return Reserva[]
     */
    public function getAll(): array
    {
        $stmt = $this->conexao->query(
            'SELECT * FROM Reservas ORDER BY data_reserva ASC, hora_inicio ASC'
        );

        return array_map(fn(array $row) => Reserva::fromArray($row), $stmt->fetchAll());
    }

    /**
     * @return Reserva[]
     */
    public function getByUtilizador(int $idUtilizador): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT * FROM Reservas
             WHERE id_utilizador = :id_utilizador
             ORDER BY data_reserva DESC, hora_inicio ASC'
        );
        $stmt->execute(['id_utilizador' => $idUtilizador]);

        return array_map(fn(array $row) => Reserva::fromArray($row), $stmt->fetchAll());
    }

    /**
     * Verifica se já existe alguma reserva ATIVA (não cancelada) para a mesma
     * sala, no mesmo dia, cujo intervalo de horas se sobreponha ao pedido.
     *
     * Dois intervalos [inicioA, fimA) e [inicioB, fimB) sobrepõem-se quando:
     *   inicioA < fimB  E  fimA > inicioB
     *
     * $ignorarReservaId serve para quando estás a EDITAR uma reserva existente
     * e não queres que ela se compare consigo própria.
     */
    public function existeSobreposicao(
        int $idSala,
        string $dataReserva,
        string $horaInicio,
        string $horaFim,
        ?int $ignorarReservaId = null
    ): bool {
        $sql = 'SELECT COUNT(*) AS total FROM Reservas
                WHERE id_sala = :id_sala
                  AND data_reserva = :data_reserva
                  AND estado != :estado_cancelada
                  AND hora_inicio < :hora_fim
                  AND hora_fim > :hora_inicio';

        $params = [
            'id_sala' => $idSala,
            'data_reserva' => $dataReserva,
            'estado_cancelada' => 'cancelada',
            'hora_inicio' => $horaInicio,
            'hora_fim' => $horaFim,
        ];

        if ($ignorarReservaId !== null) {
            $sql .= ' AND id != :ignorar_id';
            $params['ignorar_id'] = $ignorarReservaId;
        }

        $stmt = $this->conexao->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetch()['total'] > 0;
    }

    /**
     * Cria a reserva e associa os equipamentos pedidos, tudo dentro de uma
     * transação: se a associação de equipamentos falhar, a reserva também
     * não fica gravada (evita reservas "órfãs" sem os equipamentos certos).
     *
     * @param int[] $equipamentosIds
     */
    public function create(Reserva $reserva, array $equipamentosIds = []): int
    {
        try {
            $this->conexao->beginTransaction();

            $stmt = $this->conexao->prepare(
                'INSERT INTO Reservas
                    (id_utilizador, id_sala, support_needed, n_participantes,
                     acao, observacoes, data_reserva, hora_inicio, hora_fim, estado)
                 VALUES
                    (:id_utilizador, :id_sala, :support_needed, :n_participantes,
                     :acao, :observacoes, :data_reserva, :hora_inicio, :hora_fim, :estado)'
            );

            $stmt->execute([
                'id_utilizador' => $reserva->getIdUtilizador(),
                'id_sala' => $reserva->getIdSala(),
                'support_needed' => (int) $reserva->isSupportNeeded(),
                'n_participantes' => $reserva->getNParticipantes(),
                'acao' => $reserva->getAcao(),
                'observacoes' => $reserva->getObservacoes(),
                'data_reserva' => $reserva->getDataReserva(),
                'hora_inicio' => $reserva->getHoraInicio(),
                'hora_fim' => $reserva->getHoraFim(),
                'estado' => $reserva->getEstado(),
            ]);

            $novoId = (int) $this->conexao->lastInsertId();

            if (!empty($equipamentosIds)) {
                $stmtEquip = $this->conexao->prepare(
                    'INSERT INTO Reserva_Equipamentos (id_reserva, id_equipamento)
                     VALUES (:id_reserva, :id_equipamento)'
                );

                foreach ($equipamentosIds as $idEquipamento) {
                    $stmtEquip->execute([
                        'id_reserva' => $novoId,
                        'id_equipamento' => (int) $idEquipamento,
                    ]);
                }
            }

            // Regista a criação no histórico logo de início
            $this->registarHistorico($novoId, null, $reserva->getEstado());

            $this->conexao->commit();

            return $novoId;
        } catch (Exception $e) {
            $this->conexao->rollBack();
            throw $e;
        }
    }

    /**
     * Atualiza o estado de uma reserva e regista a alteração em Historico_Reservas.
     */
    public function updateEstado(int $id, string $novoEstado): void
    {
        $stmt = $this->conexao->prepare('SELECT estado FROM Reservas WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new RuntimeException("Reserva #$id não encontrada");
        }

        $estadoAnterior = $row['estado'];

        try {
            $this->conexao->beginTransaction();

            $stmtUpdate = $this->conexao->prepare(
                'UPDATE Reservas SET estado = :estado WHERE id = :id'
            );
            $stmtUpdate->execute(['estado' => $novoEstado, 'id' => $id]);

            $this->registarHistorico($id, $estadoAnterior, $novoEstado);

            $this->conexao->commit();
        } catch (Exception $e) {
            $this->conexao->rollBack();
            throw $e;
        }
    }

    private function registarHistorico(int $idReserva, ?string $estadoAnterior, string $estadoNovo): void
    {
        $stmt = $this->conexao->prepare(
            'INSERT INTO Historico_Reservas (id_reserva, estado_anterior, estado_novo)
             VALUES (:id_reserva, :estado_anterior, :estado_novo)'
        );

        $stmt->execute([
            'id_reserva' => $idReserva,
            'estado_anterior' => $estadoAnterior,
            'estado_novo' => $estadoNovo,
        ]);
    }
}