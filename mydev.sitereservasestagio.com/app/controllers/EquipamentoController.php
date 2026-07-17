<?php

require_once __DIR__ . '/../models/Equipamento.php';
require_once __DIR__ . '/../dao/EquipamentoDAO.php';
require_once __DIR__ . '/../utils/Utils.php';

class EquipamentoController
{
    private EquipamentoDAO $equipamentoDAO;

    public function __construct()
    {
        $this->equipamentoDAO = new EquipamentoDAO();
    }

    /**
     * GET /api/equipamentos
     * Devolve a lista simples (usado se só precisares de um dropdown/lista plana).
     */
    public function listar(): void
    {
        $equipamentos = $this->equipamentoDAO->getAll();

        Utils::jsonResponse([
            'sucesso' => true,
            'dados' => array_map(fn(Equipamento $e) => $this->equipamentoParaArray($e), $equipamentos),
        ]);
    }

    /**
     * GET /api/equipamentos/agrupados
     * Devolve os equipamentos já agrupados por tipo — pensado para o formulário
     * "Nova Reserva" desenhar os checkboxes organizados por categoria, tipo:
     * { "Projeção": [...], "Som": [...], "Informática": [...] }
     */
    public function listarAgrupados(): void
    {
        $equipamentos = $this->equipamentoDAO->getAll();

        $agrupados = [];
        foreach ($equipamentos as $equipamento) {
            $tipo = $equipamento->getNomeTipo() ?? 'Outros';
            $agrupados[$tipo][] = $this->equipamentoParaArray($equipamento);
        }

        Utils::jsonResponse(['sucesso' => true, 'dados' => $agrupados]);
    }

    private function equipamentoParaArray(Equipamento $equipamento): array
    {
        return [
            'id' => $equipamento->getId(),
            'nome' => $equipamento->getNome(),
            'id_tipo' => $equipamento->getIdTipo(),
            'nome_tipo' => $equipamento->getNomeTipo(),
        ];
    }

}