<?php
$url ='/api-worldskill';

require_once('./api/core/router.php');
require_once('./api/core/controllers.php');
require_once('./api/core/auth.php');

$router = new Router;
$control = new Controllers;


$router->add('GET', $url . '/status', function() use ($control) {
     $response = $control->api_status();

     echo json_encode($response);
    
    });
$router->add('POST', $url . '/api/v1/auth/signup', function() use ($control) {

    $data = json_decode(file_get_contents('php://input'), true);

     $response = $control->create_new_user($data);

     echo json_encode($response);
    
    });
$router->add('POST', $url . '/api/v1/signin', function() use ($control) {

    $data = json_decode(file_get_contents('php://input'), true);

     $response = $control->login_user($data);

     echo json_encode($response);
    
    });
$router->add('DELETE', $url . '/api/v1/auth/signout', function() use ($control) {

    $data = getallheaders(); 
     $response = $control->token_delete($data);
     echo json_encode($response);
    
    });

$router->add('GET', $url . '/api/v1/movies', function() use ($control){

    $get = $_GET;
    $header = getallheaders();
    
        if(!isset($header['Authorization']) || empty($header['Authorization'])){
            http_response_code(401);
            echo json_encode($control->erro_messenge('Unauthenticated user'));
            return ;
        }else{
            $token = explode(" ", $header['Authorization'])[1];
        }
    
        $data = [
            'data'=> $get,
            'token' => $token    
        ];
    
    $response = $control->get_any_movie($data);
    echo json_encode($response); 


});

$router->add('GET',  $url . '/api/v1/movies/', function() use ($control) {
    $clear = explode('/', $_SERVER['REQUEST_URI']);
    $id = end($clear);
    $header = getallheaders();
    
        if(!isset($header['Authorization']) || empty($header['Authorization'])){
            http_response_code(401);
            echo json_encode($control->erro_messenge('Unauthenticated user'));
            return ;
        }else{
            $token = explode(" ", $header['Authorization'])[1];
        }
    
        $data = [
            'data'=> $id,
            'token' => $token    
        ];

    $response = $control->get_movie($data);
    echo json_encode($response);
});

$router->add('GET', $url . '/api/v1/artists', function() use ($control){
        $get = $_GET;
        $header = getallheaders();
    
        if(!isset($header['Authorization']) || empty($header['Authorization'])){
            http_response_code(401);
            echo json_encode($control->erro_messenge('Unauthenticated user'));
            return ;
        }else{
            $token = explode(" ", $header['Authorization'])[1];
        }
    
        $data = [
            'data'=> $get,
            'token' => $token    
        ];

        $response = $control->get_any_artist($data);

        echo json_encode($response);


});

$router->add('GET',  $url . '/api/v1/genres', function()  use ($control){
    $get = $_GET;
    $header = getallheaders();
    
    if(!isset($header['Authorization']) || empty($header['Authorization'])){
        http_response_code(401);
        echo json_encode($control->erro_messenge('Unauthenticated user'));
        return ;
    }else{
        $token = explode(" ", $header['Authorization'])[1];
    }

    $data = [
        'data'=> $get,
        'token' => $token    
    ];
    $response = $control->get_any_genres($data);
    echo json_encode($response);

});

$router->add('GET', $url . '/api/v1/artists/', function() use ($control){
    $clear = explode('/',$_SERVER['REQUEST_URI']);

    $get = end($clear);
    $header = getallheaders();
    
    if(!isset($header['Authorization']) || empty($header['Authorization'])){
        http_response_code(401);
        echo json_encode($control->erro_messenge('Unauthenticated user'));
        return ;
    }else{
        $token = explode(" ", $header['Authorization'])[1];
    }

    $data = [
        'data'=> $get,
        'token' => $token    
    ];

    $response = $control->get_artist($data);

    echo json_encode($response);
});
$router->add('GET', $url . '/api/v1/reviews', function() use ($control){
    
    $get = $_GET; 
    $header = getallheaders();
    
    if(!isset($header['Authorization']) || empty($header['Authorization'])){
        http_response_code(401);
        echo json_encode($control->erro_messenge('Unauthenticated user'));
        return ;
    }else{
        $token = explode(" ", $header['Authorization'])[1];
    }

    $data = [
        'data'=> $get,
        'token' => $token    
    ];

    $response = $control->get_any_reviews($data);

    echo json_encode($response);
});

$router->add('GET', $url . '/api/v1/media/', function() use ($control){
    $clear = explode('/',$_SERVER['REQUEST_URI']);
    $header = getallheaders();
    
    if(!isset($header['Authorization']) || empty($header['Authorization'])){
        http_response_code(401);
        echo json_encode($control->erro_messenge('Unauthenticated user'));
        return ;
    }else{
        $token = explode(" ", $header['Authorization'])[1];
    }

    
    $id = end($clear);

    $data = [
        'data'=> $id,
        'token' => $token    
    ];
    $response = $control->getMediaContent($data);

    echo json_encode($response);
});
$router->add('POST', $url . '/api/v1/reviews/', function() use ($control){

    $get = $_GET;
    $header = getallheaders();
    
    if(!isset($header['Authorization']) || empty($header['Authorization'])){
        http_response_code(401);
        echo json_encode($control->erro_messenge('Unauthenticated user'));
        return ;
    }else{
        $token = $header['Authorization'];
    }

    $data = [
        'data'=> $get,
        'token' => $token    
    ];

    $response = $control->create_reviews($data);

    echo json_encode($response);

});
$router->add('DELETE', $url . '/api/v1/reviews/evaluations', function() use ($control){
    $id = $_GET;
    $header = getallheaders();
   

    if(!isset($header['Authorization']) || empty($header['Authorization'])){
        http_response_code(401);
        echo json_encode($control->erro_messenge('Unauthenticated user'));
        return ;
    }else{
        $token = explode(" ", $header['Authorization'])[1];
    }

    $data = [
        'data'=> $id,
        'token' => $token    
    ];

    $response = $control->delete_reviews($data);

    echo json_encode($response);

});

$router->run();

?>