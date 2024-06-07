<?php
$url ='/api-worldskill';

require_once('./api/core/router.php');
require_once('./api/core/controllers.php');

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

    $data = $_GET;

    $response = $control->get_any_movie($data);
    echo json_encode($response); 


});
$router->add('GET',  $url . '/api/v1/movies/', function() use ($control) {
    $clear = explode('/', $_SERVER['REQUEST_URI']);
    $id = end($clear);

    $response = $control->get_movie($id);
    echo json_encode($response);
});
$router->add('GET', $url . '/api/v1/artists', function() use ($control){
        $data = $_GET;

        $response = $control->get_any_artist($data);

        echo json_encode($response);


});
$router->add('GET',  $url . '/api/v1/genres', function()  use ($control){
    $data = $_GET;
    $response = $control->get_any_genres($data);
    echo json_encode($response);

});
$router->add('GET', $url . '/api/v1/artists/', function() use ($control){
    $clear = explode('/',$_SERVER['REQUEST_URI']);

    $data = end($clear);

    $response = $control->get_artist($data);

    echo json_encode($response);
});
$router->add('GET', $url . '/api/v1/reviews', function() use ($control){
    
    $data = $_GET; 

    $response = $control->get_any_reviews($data);

    echo json_encode($response);
});
$router->add('GET', $url . '/api/v1/media/', function() use ($control){
    $clear = explode('/',$_SERVER['REQUEST_URI']);

    $data = end($clear);
    // return $clear;
    $response = $control->getMediaContent($data);

    echo json_encode($response);
});

$router->run();

?>