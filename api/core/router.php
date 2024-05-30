<?php
class Router {
    private $routes = [];

    public function add($method, $path, $callback) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback
        ];
    }

    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $_SERVER['REQUEST_URI'];

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                call_user_func($route['callback']);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['message' => 'Endpoint not found',]);
        // echo json_encode(['message' => $method,]);
        // echo json_encode(['message' => $path]);
    }
}
?>
