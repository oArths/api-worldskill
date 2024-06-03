<?php
include_once(dirname(__FILE__) . '/../config/database.php');
include_once(dirname(__FILE__) . '/../core/auth.php');


class Controllers{
    private $params;


    public function __construct($params = null){
        $this->params = $params;

    }

    public function erro_response($message){
        return [
            'message' => 'Invalid properties',
            'errors'=> $message
        ];
    }
    public function erro_messenge($message){
        return [
            'message' => $message,
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
        $authentic = new Auth;

        if(
            !isset($params['email']) ||
        !isset($params['password']) ||
        !isset($params['name']) ||
        !isset($params['username'])    
        ){
            return $this->erro_response('campos insuficientes1');
        }
        if( strlen($params['password']) < 6 ){
            return $this->erro_response("Invalid properties2");

        }
        $consulta = [
            ':email' => $params['email'],
            ':username' => $params['username'],

        ];
        $results = $db->QUERY('SELECT id FROM user WHERE email = :email OR  username =:username', $consulta);
        if(count($results) != 0  ){
            http_response_code(422);
            return $this->erro_response("Invalid properties3");
        }
        
        
        $pass = password_hash($params['password'], PASSWORD_DEFAULT);
        
        $Insert = [
            ':name' => $params['name'],
            ':email' => $params['email'],
            ':username' => $params['username'],
            ':password' => $pass,
        ];

        
        $db-> QUERY("
        INSERT INTO user (name, email, username, password) VALUES
        (:name,:email,:username,:password)", $Insert);
        
        // return $params;
         $token = $authentic->create_token($params);


        header('Content-Type: application/json');
        http_response_code(201);
        return [
            'token' => $token,
        ];
    }
    //------------------------------------------------
    public function login_user ($params) {
        $db = new database;
        $auth = new Auth;
        $erro = [
            'propriedade' => ['erro'],
            'propriedades' => ['erro']
        ];

        if((!isset($params['email']) || !isset($params['password'])) || (strlen($params['password']) < 6)){
            http_response_code(422);
            return $this->erro_response($erro);      
        }


        $Insert = [
            ':email' => $params['email'],
        ];

        $validacao = $db->QUERY('SELECT * FROM user WHERE  email = :email', $Insert);

        if(empty($validacao)){
            return "vazio";
        }

        if(!$validacao){
            http_response_code(422);
            return $this->erro_response($erro);      
        }elseif(!password_verify($params['password'], $validacao[0]['password'])){
            http_response_code(422);
            return $this->erro_messenge('Invalid email or password');
        }
        
        // return $validacao;
        $exist = $auth->exist_token($validacao);
        return $exist;
    }}


?>