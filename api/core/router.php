<?php
class Router {
    private $routes = [];

    public function add($method, $path, $callback) {
        $pattern = preg_replace('/\//', '\\/', $path);
        $pattern = '/^' . $pattern . '$/';
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'callback' => $callback
        ];
    }

    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $path, $matches)) {
                array_shift($matches);
                call_user_func_array($route['callback'], $matches);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['message' => 'Endpoint not found']);
    }
}

    // public function rota (){
    //     $method = $_SERVER['REQUEST_METHOD'];
    //     $path = $_SERVER['REQUEST_URI'];

    //     foreach ($this->routes as $route) {
    //                 if ($route['method'] === $method && $route['path'] === $path) {
    //                     call_user_func($route['callback']);
    //                     return ;
    //                 }
    //             }
    // }

    // public function run() {
    //     $method = $_SERVER['REQUEST_METHOD'];
    //     $path = $_SERVER['REQUEST_URI'];
    //     $last_data = strrpos($path, '/');

    // $rota = $this->rota();

    // if($rota === false){
    //     http_response_code(404);
    //      echo json_encode(['message' => 'Endpoint not found1',]);
         
    // }elseif(strpos($path, '?') !== false){
    //     $clear = explode("?", $path);
    //       $newpath = $clear[0];
    
    
    //       foreach ($this->routes as $route) {
    //         if ($route['method'] === $method && $route['path'] === $newpath) {
    //             call_user_func($route['callback']);
    //             return ;
    //         }
    //     }
    
    //     http_response_code(404);
    //     echo json_encode(['message' => 'Endpoint not found2',]);
       
    // }else if($last_data !== false){
    //         $path = substr($path, 0, $last_data + 1);
          
    //         foreach ($this->routes as $route) {
    //             if ($route['method'] === $method && $route['path'] === $path) {
    //                 call_user_func($route['callback']);
    //                 return ;
    //             }
    //     }   
    // }
         
    //      http_response_code(404);
    //      echo json_encode(['message' => 'Endpoint not found3',]);

       
    // }


