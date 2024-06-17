<?php
include_once(dirname(__FILE__) . '/../config/database.php');

class Auth {
    private $key;

    public function __construct(){
        $this->key = 'senai115';
    }

    public function create_token($params) {
        $db = new database;

        // return $params;

        $email = $params['email'];
        
        date_default_timezone_set('America/Sao_Paulo');
        $nowDate = time();

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $payload = [
            'email' => $email,
            'create' => $nowDate,
            'exp' => $nowDate + 3600
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

    public function valid_token($token){
        
        list($header, $payload, $signature) = explode('.', $token);

        // $explode = explode('.', $token);
        // $header = $explode[0];
        // $payload = $explode[1];
        // $signature = $explode[2];

        // return $signature;
        
        $dec_header = json_decode(base64_decode($header, true));
        $dec_payload = json_decode(base64_decode($payload));

        $valid_signature = base64_encode(hash_hmac('sha256', $header . "." . $payload, $this->key,true));

        if($signature !== $valid_signature){
            return false;
        }
        if($dec_payload->exp < time()){
            return false;
        }
        return $dec_payload;
    }

    public function delete_token($token){
        $db = new database;

        $insert = [
            ':token' => $token,
        ];

        $consulta = $db->QUERY('DELETE FROM accesstoken WHERE tokenString = :token', $insert);

        if($consulta !== false){
            return true;
        }else{
            return false;
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
           $token = $this->create_token($params);
           $response = [
               'type' => 'expired',
               'token' => $token
               ];
            header('Content-Type: application/json');
           http_response_code(201);
           return $response;
    }else{
        $token = 'Bearer ' . $token[0]['tokenString'];
            $response = [
            'type' => 'exist',
            'token' => $token
            ];
           header('Content-Type: application/json');
           http_response_code(200);
           return $response;
       }

    }

}
?>
