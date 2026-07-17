<?php

require_once __DIR__ . '/../models/Utilizador.php';
require_once __DIR__ . '/../dao/UtilizadorDAO.php';
require_once __DIR__ . '/../utils/Utils.php';

class UtilizadorController
{
    private UtilizadorDAO $utilizadorDAO;

    public function __construct()
    {
        $this->utilizadorDAO = new UtilizadorDAO();
    }

    /**
     * GET /api/utilizadores
     */
    public function listar(): void
    {
        $utilizadores = $this->utilizadorDAO->getAll();

        Utils::jsonResponse([
            'sucesso' => true,
            'dados' => array_map(fn(Utilizador $u) => $this->utilizadorParaArray($u), $utilizadores),
        ]);
    }

    /**
     * GET /api/utilizadores/{id}
     */
    public function obter(int $id): void
    {
        $utilizador = $this->utilizadorDAO->getById($id);

        if (!$utilizador) {
            Utils::jsonResponse(['sucesso' => false, 'erro' => 'Utilizador não encontrado'], 404);
            return;
        }

        Utils::jsonResponse(['sucesso' => true, 'dados' => $this->utilizadorParaArray($utilizador)]);
    }

    /**
     * POST /api/utilizadores
     * Corpo esperado: { "nome": "...", "email": "..." }
     */
    public function criar(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['nome']) || empty($input['email'])) {
            Utils::jsonResponse(['sucesso' => false, 'erro' => 'Nome e email são obrigatórios'], 400);
            return;
        }

        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            Utils::jsonResponse(['sucesso' => false, 'erro' => 'Email inválido'], 400);
            return;
        }

        // Evita duplicados antes de tentar inserir (a BD também tem UNIQUE, mas assim
        // devolvemos um erro amigável em vez de deixar rebentar a exceção do PDO).
        if ($this->utilizadorDAO->getByEmail($input['email'])) {
            Utils::jsonResponse(['sucesso' => false, 'erro' => 'Já existe um utilizador com este email'], 409);
            return;
        }

        $utilizador = new Utilizador(null, $input['nome'], $input['email']);
        $novoId = $this->utilizadorDAO->create($utilizador);

        Utils::jsonResponse(['sucesso' => true, 'id' => $novoId], 201);
    }

    private function utilizadorParaArray(Utilizador $utilizador): array
    {
        return [
            'id' => $utilizador->getId(),
            'nome' => $utilizador->getNome(),
            'email' => $utilizador->getEmail(),
            'criado_em' => $utilizador->getCriadoEm(),
        ];
    }

}