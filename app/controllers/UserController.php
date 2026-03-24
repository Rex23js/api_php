<?php

require_once __DIR__ . '/../models/User.php';

class UserController
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function index(): void
    {
        $users = $this->user->findAll();
        $this->json(200, $users);
    }

    public function show(string $id): void
    {
        $user = $this->user->findById((int) $id);

        if (!$user) {
            $this->json(404, ['error' => 'Usuario nao encontrado.']);
            return;
        }

        $this->json(200, $user);
    }

    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $errors = $this->validate($data);
        if (!empty($errors)) {
            $this->json(422, ['errors' => $errors]);
            return;
        }

        try {
            $id = $this->user->create($data);
            $this->json(201, ['id' => $id, 'message' => 'Usuario criado com sucesso.']);
        } catch (PDOException $e) {
            if ($this->isDuplicateEntry($e)) {
                $this->json(409, ['error' => 'O campo email ja esta em uso.']);
                return;
            }

            $this->json(500, ['error' => 'Erro interno ao criar o usuario.']);
        }
    }

    public function update(string $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $errors = $this->validate($data);
        if (!empty($errors)) {
            $this->json(422, ['errors' => $errors]);
            return;
        }

        $exists = $this->user->findById((int) $id);
        if (!$exists) {
            $this->json(404, ['error' => 'Usuario nao encontrado.']);
            return;
        }

        try {
            $this->user->update((int) $id, $data);
            $this->json(200, ['message' => 'Usuario atualizado com sucesso.']);
        } catch (PDOException $e) {
            if ($this->isDuplicateEntry($e)) {
                $this->json(409, ['error' => 'O campo email ja esta em uso.']);
                return;
            }

            $this->json(500, ['error' => 'Erro interno ao atualizar o usuario.']);
        }
    }

    public function destroy(string $id): void
    {
        $exists = $this->user->findById((int) $id);
        if (!$exists) {
            $this->json(404, ['error' => 'Usuario nao encontrado.']);
            return;
        }

        $this->user->delete((int) $id);
        $this->json(200, ['message' => 'Usuario excluido com sucesso.']);
    }

    private function validate(?array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'O campo name e obrigatorio.';
        }
        if (empty($data['email'])) {
            $errors[] = 'O campo email e obrigatorio.';
        }
        if (empty($data['hair_color'])) {
            $errors[] = 'O campo hair_color e obrigatorio.';
        }
        if (empty($data['eye_color'])) {
            $errors[] = 'O campo eye_color e obrigatorio.';
        }
        if (!isset($data['height']) || !is_numeric($data['height'])) {
            $errors[] = 'O campo height e obrigatorio e deve ser numerico (ex: 1.75).';
        }
        if (!isset($data['weight']) || !is_numeric($data['weight'])) {
            $errors[] = 'O campo weight e obrigatorio e deve ser numerico (ex: 72.5).';
        }

        return $errors;
    }

    private function isDuplicateEntry(PDOException $e): bool
    {
        return $e->getCode() === '23000' || str_contains($e->getMessage(), '1062 Duplicate entry');
    }

    private function json(int $status, mixed $data): void
    {
        http_response_code($status);
        echo json_encode($data);
    }
}
