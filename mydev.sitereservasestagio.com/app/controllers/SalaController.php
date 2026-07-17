<?php

require_once __DIR__ . '/../models/Sala.php';
require_once __DIR__ . '/../dao/SalaDAO.php';
require_once __DIR__ . '/../utils/Utils.php';

class SalaController
{
    private SalaDAO $salaDAO;

    public function __construct()
    {
        $this->salaDAO = new SalaDAO();
    }

    /**
     * GET /api/salas
     * Lista todas as salas ativas.
     */
    public function listar(): void
    {
        $salas = $this->salaDAO->getAll();

        Utils::jsonResponse([
            'sucesso' => true,
            'dados' => array_map(fn(Sala $s) => $this->salaParaArray($s), $salas),
        ]);
    }

    /**
     * GET /api/salas/{id}
     */
    public function obter(int $id): void
    {
        $sala = $this->salaDAO->getById($id);

        if (!$sala) {
            Utils::jsonResponse(['sucesso' => false, 'erro' => 'Sala não encontrada'], 404);
            return;
        }

        Utils::jsonResponse(['sucesso' => true, 'dados' => $this->salaParaArray($sala)]);
    }

    /**
     * POST /api/salas
     * Corpo esperado (JSON): { "nome": "...", "tipo": "...", "capacidade": 10 }
     */
    public function criar(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['nome']) || empty($input['capacidade'])) {
            Utils::jsonResponse(['sucesso' => false, 'erro' => 'Nome e capacidade são obrigatórios'], 400);
            return;
        }

        $sala = new Sala(
            null,
            $input['nome'],
            $input['tipo'] ?? null,
            (int) $input['capacidade']
        );

        $novoId = $this->salaDAO->create($sala);

        Utils::jsonResponse(['sucesso' => true, 'id' => $novoId], 201);
    }

    private function salaParaArray(Sala $sala): array
    {
        return [
            'id' => $sala->getId(),
            'nome' => $sala->getNome(),
            'tipo' => $sala->getTipo(),
            'capacidade' => $sala->getCapacidade(),
            'ocupada' => $sala->isOcupada(),
            'ativa' => $sala->isAtiva(),
        ];
    }

}