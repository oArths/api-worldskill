<?php
class Router
{
    private $routes = [];

    public function add($method, $path, $callback)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback
        ];
    }


    public function run(){
        
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $_SERVER['REQUEST_URI'];
        $queryString = $_SERVER['QUERY_STRING'];
        // $auth = new Auth;
        // $header = getallheaders();
       
        // if (!isset($header['Authorization'])) {
        //     http_response_code(401);
        //     echo json_encode(['message' => 'Unauthenticated user']);
        //     return;
        // }

        // $authHeader = $header['Authorization'];
        // $token = explode(' ', $authHeader)[1];
        // $userData = $auth->valid_token($token);

        // if (!$userData) {
        //     http_response_code(403);
        //     echo json_encode(['message' => 'Invalid tokejmn']);
        //     return;
        // }

        $id = explode('/', $path);
        $end = end($id);


        if (empty($queryString) && preg_match('/[0-9.]/', $end)) {
            //array_pop retirar e guarda o ultimo item do array npo caso o id
           array_pop($id);

            // aq ele junta todo o array em uma str com o implode juntando baseado na /
            // como  a gente tirou o o id do final agr temos a uri limpa pra comparar
           $newpath = implode('/', $id);
            // concatena com mais uma / pra não ter erro
            $newpath = $newpath . '/';


            foreach ($this->routes as $route) {
                if ($route['method'] === $method && $route['path'] === $newpath) {
                    header('Content-Type: application/json');
                    call_user_func($route['callback']);
                    return;
                }
            }
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Endpoint not found1']);
        } elseif (!empty($queryString)) {
            
            $clear = explode("?", $path);
            $newpath = $clear[0];

            foreach ($this->routes as $route) {
                if ($route['method'] === $method && $route['path'] === $newpath) {
                    header('Content-Type: application/json');
                    call_user_func($route['callback']);
                    return;
                }
            }
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Endpoint not found2']);
        } else {
            foreach ($this->routes as $route) {
                if ($route['method'] === $method && $route['path'] === $path) {
                    header('Content-Type: application/json');
                    call_user_func($route['callback']);
                    return;
                }
            }
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Endpoint not found3',]);
        }
    }
}
