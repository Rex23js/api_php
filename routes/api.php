<?php

/**
 * Registra todas as rotas da API.
 */
function registerRoutes(Router $router): void
{
    // ── Usuários ────────────────────────────────────
    $router->get('/users',       'UserController', 'index');   // Listar todos
    $router->get('/users/{id}',  'UserController', 'show');    // Exibir um
    $router->post('/users',      'UserController', 'store');   // Criar
    $router->put('/users/{id}',  'UserController', 'update');  // Atualizar
    $router->delete('/users/{id}', 'UserController', 'destroy'); // Excluir
}
