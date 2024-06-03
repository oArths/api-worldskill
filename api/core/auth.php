<?php
include_once(dirname(__FILE__) . '/../config/database.php');

class Auth {
    private $key;

    public function __construct(){
        $this->key = 'senai115';
    }

    public function create_token($params) {
        $db = new database;

        $name = $params['email'];
        
        date_default_timezone_set('America/Sao_Paulo');
        $nowDate = time();
        
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $payload = [
            'name' => $name,
            'create' => $nowDate,
        ];
        
        $header = json_encode($header);
        $payload = json_encode($payload);
        
        $header = base64_encode($header);
        $payload = base64_encode($payload);
        
        $sing = hash_hmac('sha256', $header . "." . $payload, $this->key, true);
        $sing = base64_encode($sing);
        
        $token = "Bearer" . " " . $header . "." . $payload . "." . $sing;
        
        $consulta = [
            ':email' => $params['email'],
        ];
        
        $nowDate = date("Y-m-d H:i:s",time());

        $results = $db->QUERY('SELECT * FROM  user WHERE email = :email', $consulta);
        
        if($results)
        {
            $userId = $results[0]['id'];
        }
        
        $clear_token = explode(" ", $token);

        $insert = [
            ':userId' => $userId,
            ':tokenString' => $clear_token[1],
            ':creationDate' => $nowDate
        ];
        
        $db->QUERY("
        INSERT INTO accesstoken (userId, tokenString, creationDate) 
        VALUES (:userId,:tokenString,:creationDate)", $insert);
        
        
        return $token;       
    }

    public function exist_token($params){
        $db = new database;

        
        $consulta = [
            ':userId' => $params['id']
        ];
        
        $exits = $db->QUERY('SELECT id FROM accesstoken WHERE userId = :userId', $consulta);
        
        if(empty($exits)){
            header('Content-Type: application/json');
            http_response_code(201);
            $token = $this->create_token($params);
            return $token;
        }else{
           return $this->is_valid($params);
        }
        
        
    }
    public function is_valid ($params) {
        $db = new database;
        

        $consulta = [
            ':userId' => $params['id']
        ];
        
        $token = $db->QUERY("SELECT * FROM accesstoken WHERE  userId = :userId", $consulta); 
        if (empty($token)) {
            return "sem tpoken user";
        }

        date_default_timezone_set('America/Sao_Paulo');
       $now = strtotime(date("Y-m-d H:i:s"));

       $date = strtotime($token[0]['creationDate']);
       $datexpired = strtotime('+1 hour', $date);

       if( $now > $datexpired){
           $db->QUERY("DELETE FROM accesstoken WHERE userId = :userId", $consulta);
           header('Content-Type: application/json');
           http_response_code(201);
           return $token = $this->create_token($params);
    }else{
           header('Content-Type: application/json');
           http_response_code(201);
           return 'Bearer' . ' ' . $token[0]['tokenString'];
       }

    }

}
?>
