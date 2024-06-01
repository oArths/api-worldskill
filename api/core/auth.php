<?php
include_once(dirname(__FILE__) . '/../config/database.php');

class Auth {
    private $key;

    public function __construct(){
        $this->key = 'senai115';
    }

    public function create_token($params) {
        $db = new database;
        
        $user = $params['username'];    
        $name = $params['name'];
        
        date_default_timezone_set('America/Sao_Paulo');
        $nowDate = time();
        
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $payload = [
            'username' => $user,
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
            ':username' => $params['username'],
        ];
        
        
        $results = $db->QUERY('SELECT id FROM  user WHERE username = :username', $consulta);
        
        $userId = $results[0]['id'];
        
        $insert = [
            ':userId' => $userId,
            ':tokenString' => $token,
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
            $token = $this->create_token($params);
            return $token;
        }elseif($this->is_valid($params)){
            return true;
        }


    }
    public function is_valid ($params) {
        $db = new database;

        $consulta = [
            ':userId' => $params['id']
        ];

        $token = $db->QUERY("SELECT creationDate FROM accesstoken WHERE  userId = :userId", $consulta); 
        if (empty($token)) {
            return false;
        }
       $now = time();

       $date = $token[0]['creationDate'];
       $datexpired = $date + 3600;

       if( $now < $datexpired){
        //valido
        return true;
       }else{
        $db->QUERY("DELETE FROM accesstoken WHERE userId = :userId", $consulta);
         return [
            'message' => 'Invalid token2',
         ];
       }

    }

}
?>
