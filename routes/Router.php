<?php

class Router
{
    private array $routes = [];

    /**
     * Registra uma rota.
     */
    public function add(string $method, string $path, string $controller, string $action): void
    {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'path'       => $path,
            'controller' => $controller,
            'action'     => $action,
        ];
    }

    // Atalhos
    public function get(string $path, string $controller, string $action): void
    {
        $this->add('GET', $path, $controller, $action);
    }

    public function post(string $path, string $controller, string $action): void
    {
        $this->add('POST', $path, $controller, $action);
    }

    public function put(string $path, string $controller, string $action): void
    {
        $this->add('PUT', $path, $controller, $action);
    }

    public function delete(string $path, string $controller, string $action): void
    {
        $this->add('DELETE', $path, $controller, $action);
    }

    /**
     * Despacha a requisição para o controller/action correspondente.
     */
    public function dispatch(string $method, string $uri): void
    {
        foreach ($this->routes as $route) {
            $pattern = $this->convertToRegex($route['path']);

            if ($route['method'] === strtoupper($method) && preg_match($pattern, $uri, $matches)) {
                $controllerFile = __DIR__ . '/../app/controllers/' . $route['controller'] . '.php';

                if (!file_exists($controllerFile)) {
                    $this->sendJson(500, ['error' => "Controller '{$route['controller']}' não encontrado."]);
                    return;
                }

                require_once $controllerFile;
                $controller = new $route['controller']();
                $action     = $route['action'];

                if (!method_exists($controller, $action)) {
                    $this->sendJson(500, ['error' => "Action '{$action}' não encontrada."]);
                    return;
                }

                // Remove a primeira posição (match completo)
                array_shift($matches);
                call_user_func_array([$controller, $action], $matches);
                return;
            }
        }

        $this->sendJson(404, ['error' => 'Rota não encontrada.']);
    }

    /**
     * Converte placeholders {param} para regex.
     */
    private function convertToRegex(string $path): string
    {
        $pattern = preg_replace('/\{[a-zA-Z_]+\}/', '([a-zA-Z0-9_-]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function sendJson(int $status, array $data): void
    {
        http_response_code($status);
        echo json_encode($data);
    }
}
