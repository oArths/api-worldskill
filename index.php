<?php
$url ='/api-worldskill';

require_once('./api/core/router.php');
require_once('./api/core/controllers.php');

$router = new Router;
$control = new Controllers;

$router->add('GET', $url . '/status', function() use ($control) {
     $response = $control->api_status();

     header('Content-Type: application/json');
     echo json_encode($response);
    
    });
$router->add('POST', $url . '/api/v1/auth/signup', function() use ($control) {

    $data = json_decode(file_get_contents('php://input'), true);

     $response = $control->create_new_user($data);

     header('Content-Type: application/json');
     http_response_code(201);
     echo json_encode($response);
    
    });


$router->run();

?>