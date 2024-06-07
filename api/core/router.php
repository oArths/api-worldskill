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

        $id = explode('/', $path);
        $end = end($id);


        if(preg_match('/^[0-9a-zA-Z.]+$/', $end)){
           array_pop($id);
           $newpath = implode('/', $id) ;
            $newpath = $newpath . '/';
           foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $newpath) {
                header('Content-Type: application/json');
                call_user_func($route['callback']);
                return ;
            }
        }   
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Endpoint not found1']);
        
        }elseif($_SERVER['QUERY_STRING']){
            $clear = explode("?", $path);
            $newpath = $clear[0];
            
            foreach ($this->routes as $route) {
                if ($route['method'] === $method && $route['path'] === $newpath) {
                    header('Content-Type: application/json');
                    call_user_func($route['callback']);
                    return ;
                }
                
            }
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Endpoint not found2']);
        }else{

            foreach ($this->routes as $route) {
                if ($route['method'] === $method && $route['path'] === $path) {
                    header('Content-Type: application/json');
                    call_user_func($route['callback']);
                    return ;
                }
                
            }
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Endpoint not found3',]);
        }
    }    
    
}