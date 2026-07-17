<?php

require_once __DIR__ . '/../config/DatabaseSingle.php';
require_once __DIR__ . '/../models/Utilizador.php';

class UtilizadorDAO
{
    private PDO $conexao;

    public function __construct()
    {
        $this->conexao = DatabaseSingle::connect();
    }

    /**
     * @return Utilizador[]
     */
    public function getAll(): array
    {
        $stmt = $this->conexao->query('SELECT * FROM Utilizadores ORDER BY nome ASC');

        return array_map(fn(array $row) => Utilizador::fromArray($row), $stmt->fetchAll());
    }

    public function getById(int $id): ?Utilizador
    {
        $stmt = $this->conexao->prepare('SELECT * FROM Utilizadores WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        return $row ? Utilizador::fromArray($row) : null;
    }

    public function getByEmail(string $email): ?Utilizador
    {
        $stmt = $this->conexao->prepare('SELECT * FROM Utilizadores WHERE email = :email');
        $stmt->execute(['email' => $email]);

        $row = $stmt->fetch();

        return $row ? Utilizador::fromArray($row) : null;
    }

    public function create(Utilizador $utilizador): int
    {
        $stmt = $this->conexao->prepare(
            'INSERT INTO Utilizadores (nome, email) VALUES (:nome, :email)'
        );

        $stmt->execute([
            'nome' => $utilizador->getNome(),
            'email' => $utilizador->getEmail(),
        ]);

        return (int) $this->conexao->lastInsertId();
    }
}