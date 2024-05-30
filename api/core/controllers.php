<?php
include_once(dirname(__FILE__) . '/../config/database.php');


class Controllers{
    private $params;


    public function __construct($params = null){
        $this->params = $params;

    }

    public function erro_response($message){
        return [
            'message' => $message,
            'errors'=> [
                'propriedade'=>['error'],
                'propriedade'=>['error'],
            ],
        ];
    }




    public function api_status (){
        return [
            'status' => 'SUCCESS',
            'message' => 'Api esta rodando',
            'results' => []
        ]; 

    }
    public function create_new_user ($params){
        $db = new database;

        if(
            !isset($params['email']) ||
        !isset($params['password']) ||
        !isset($params['name']) ||
        !isset($params['username'])    
        ){
            return $this->erro_response('campos insuficientes');
            // return $params;
        }
        $consulta = [
            ':email' => $params['email'],
            ':username' => $params['username'],

        ];
        $results = $db->QUERY('SELECT id FROM user WHERE email = :email OR  username =:username', $consulta);
        if(count($results) != 0  ){
            http_response_code(422);
            return $this->erro_response("Invalid properties");
        }
        date_default_timezone_set('America/Sao_Paulo');
        $pass = password_hash($params['password'], PASSWORD_DEFAULT);
        $randomHash = password_hash(random_bytes(32), PASSWORD_DEFAULT);

        $token = $randomHash . date(" Y-m-d H:i:s");
        

        $params = [
            ':name' => $params['name'],
            ':email' => $params['email'],
            ':username' => $params['username'],
            ':password' => $pass,
        ];

        $db-> QUERY("
        INSERT INTO user (name, email, username, password) VALUES
        (:name,:email,:username,:password)", $params);

        return [
            'token' => $token,
        ];
    }
  
}


?>