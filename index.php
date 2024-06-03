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


$router->run();

?>