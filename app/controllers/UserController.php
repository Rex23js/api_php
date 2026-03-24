<?php

require_once __DIR__ . '/../models/User.php';

class UserController
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    // GET /users
    public function index(): void
    {
        $users = $this->user->findAll();
        $this->json(200, $users);
    }

    // GET /users/{id}
    public function show(string $id): void
    {
        $user = $this->user->findById((int) $id);

        if (!$user) {
            $this->json(404, ['error' => 'Usuário não encontrado.']);
            return;
        }

        $this->json(200, $user);
    }

    // POST /users
    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['name']) || empty($data['email'])) {
            $this->json(422, ['error' => 'Os campos name e email são obrigatórios.']);
            return;
        }

        $id = $this->user->create($data);
        $this->json(201, ['id' => $id, 'message' => 'Usuário criado com sucesso.']);
    }

    // PUT /users/{id}
    public function update(string $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['name']) || empty($data['email'])) {
            $this->json(422, ['error' => 'Os campos name e email são obrigatórios.']);
            return;
        }

        $exists = $this->user->findById((int) $id);
        if (!$exists) {
            $this->json(404, ['error' => 'Usuário não encontrado.']);
            return;
        }

        $this->user->update((int) $id, $data);
        $this->json(200, ['message' => 'Usuário atualizado com sucesso.']);
    }

    // DELETE /users/{id}
    public function destroy(string $id): void
    {
        $exists = $this->user->findById((int) $id);
        if (!$exists) {
            $this->json(404, ['error' => 'Usuário não encontrado.']);
            return;
        }

        $this->user->delete((int) $id);
        $this->json(200, ['message' => 'Usuário excluído com sucesso.']);
    }

    // ── Helper ──────────────────────────────────────

    private function json(int $status, mixed $data): void
    {
        http_response_code($status);
        echo json_encode($data);
    }
}
