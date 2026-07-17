<?php

require_once __DIR__ . '/../models/Reserva.php';
require_once __DIR__ . '/../dao/ReservaDAO.php';
require_once __DIR__ . '/../utils/Utils.php';

class ReservaController
{
    private ReservaDAO $reservaDAO;

    public function __construct()
    {
        $this->reservaDAO = new ReservaDAO();
    }

    /**
     * GET /api/reservas
     * Lista todas as reservas (usado no calendário geral).
     */
    public function listar(): void
    {
        $reservas = $this->reservaDAO->getAll();

        Utils::jsonResponse([
            'sucesso' => true,
            'dados' => array_map(fn(Reserva $r) => $this->reservaParaArray($r), $reservas),
        ]);
    }

    /**
     * GET /api/reservas/utilizador/{id}
     * Usado na página "Minhas Reservas".
     */
    public function listarPorUtilizador(int $idUtilizador): void
    {
        $reservas = $this->reservaDAO->getByUtilizador($idUtilizador);

        Utils::jsonResponse([
            'sucesso' => true,
            'dados' => array_map(fn(Reserva $r) => $this->reservaParaArray($r), $reservas),
        ]);
    }

    /**
     * POST /api/reservas
     * Corpo esperado (JSON):
     * {
     *   "id_utilizador": 1, "id_sala": 2, "n_participantes": 8,
     *   "acao": "Reunião de Projeto", "observacoes": "...",
     *   "data_reserva": "2026-07-20", "hora_inicio": "09:00", "hora_fim": "11:00",
     *   "equipamentos": [1, 3]
     * }
     */
    public function criar(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $obrigatorios = ['id_utilizador', 'id_sala', 'data_reserva', 'hora_inicio', 'hora_fim'];
        foreach ($obrigatorios as $campo) {
            if (empty($input[$campo])) {
                Utils::jsonResponse(['sucesso' => false, 'erro' => "Campo obrigatório em falta: $campo"], 400);
                return;
            }
        }

        if ($input['hora_fim'] <= $input['hora_inicio']) {
            Utils::jsonResponse(['sucesso' => false, 'erro' => 'A hora de fim tem de ser depois da hora de início'], 400);
            return;
        }

        // Verifica sobreposição ANTES de criar — regra de negócio central deste sistema.
        $existeConflito = $this->reservaDAO->existeSobreposicao(
            (int) $input['id_sala'],
            $input['data_reserva'],
            $input['hora_inicio'],
            $input['hora_fim']
        );

        if ($existeConflito) {
            Utils::jsonResponse([
                'sucesso' => false,
                'erro' => 'Já existe uma reserva para esta sala nesse horário',
            ], 409); // 409 Conflict
            return;
        }

        $reserva = new Reserva(
            null,
            (int) $input['id_utilizador'],
            (int) $input['id_sala'],
            !empty($input['equipamentos']),
            $input['n_participantes'] ?? null,
            $input['acao'] ?? null,
            $input['observacoes'] ?? null,
            $input['data_reserva'],
            $input['hora_inicio'],
            $input['hora_fim'],
            'pendente'
        );

        $equipamentosIds = $input['equipamentos'] ?? [];
        $novoId = $this->reservaDAO->create($reserva, $equipamentosIds);

        Utils::jsonResponse(['sucesso' => true, 'id' => $novoId], 201);
    }

    /**
     * PATCH /api/reservas/{id}/estado
     * Corpo esperado: { "estado": "confirmada" | "cancelada" }
     */
    public function atualizarEstado(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $novoEstado = $input['estado'] ?? null;

        $estadosValidos = ['pendente', 'confirmada', 'cancelada'];
        if (!in_array($novoEstado, $estadosValidos, true)) {
            Utils::jsonResponse(['sucesso' => false, 'erro' => 'Estado inválido'], 400);
            return;
        }

        $this->reservaDAO->updateEstado($id, $novoEstado);

        Utils::jsonResponse(['sucesso' => true]);
    }

    private function reservaParaArray(Reserva $reserva): array
    {
        return [
            'id' => $reserva->getId(),
            'id_utilizador' => $reserva->getIdUtilizador(),
            'id_sala' => $reserva->getIdSala(),
            'n_participantes' => $reserva->getNParticipantes(),
            'acao' => $reserva->getAcao(),
            'observacoes' => $reserva->getObservacoes(),
            'data_reserva' => $reserva->getDataReserva(),
            'hora_inicio' => $reserva->getHoraInicio(),
            'hora_fim' => $reserva->getHoraFim(),
            'estado' => $reserva->getEstado(),
            'support_needed' => $reserva->isSupportNeeded(),
        ];
    }

}