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

         $token = $authentic->create_token($params);
        return [
            'token' => $token,
        ];
    }
    public function login_user ($params) {
        $db = new database;
        $auth = new Auth;

        // return $params;
        // a validação ta errada pq 
        if((!isset($params['email']) && !isset($params['password'])) && (strlen($params['password']) < 6)){
            http_response_code(422);
            return $this->erro_response("Invalid properties4");      
        }

        $pass = password_hash($params['password'], PASSWORD_DEFAULT);

        $consulta = [
            ':email' => $params['email'],
            ':password' => $pass

        ];
        
        $results = $db->QUERY('SELECT * FROM user WHERE email = :email AND password = :password', $consulta);
        return $results;
        if(empty($results)){
            http_response_code(422);
            return $this->erro_response("Invalid properties5");
        }

        $exist = $auth->exist_token($params);
        
        if($exist){

        }
    }}


?>