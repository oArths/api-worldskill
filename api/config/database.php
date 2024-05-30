<?php

define('API_URL', 'http://localhost/api-worldskill');

class database{
    public function QUERY($query, $params = null, $debug = true, $close_conn = true ){

        $result = null;

        $host = "localhost:3307";
        $user = "root";
        $pass = "";
        $db = "worldskill";
        
        
        $conn = new PDO(
            "mysql:host=$host;
            dbname=$db",
            $user,
            $pass,
            array(PDO::ATTR_PERSISTENT => true));

            if($debug){
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            };
            try {
               if ($params != null) {
                   $gestor = $conn->prepare($query);
                   $gestor->execute($params);
                   $results = $gestor->fetchAll(PDO::FETCH_ASSOC);
               } else {
                   $gestor = $conn->prepare($query);
                   $gestor->execute();
                   $results = $gestor->fetchAll(PDO::FETCH_ASSOC);
               }
           } catch (PDOException $e) {        
               return false;
           }
             if ($close_conn) {
               $conn = null;
           }

           return $results;
    }
    public function NON_QUERY($query, $params = null, $debug = true, $close_conn = true){
        
        $host = "localhost:3307";
        $user = "root";
        $pass = "";
        $db = "worldskill";

        $conn = new PDO(
            "mysql:host=$host; 
            dbname=$db", 
            $user, 
            $pass, 
            array(PDO::ATTR_PERSISTENT => true)); 

        if($debug){
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        }
        // define que se todas as transações com o banco ou são completas ou não acontecem
        $conn->beginTransaction();
        //tenta fazer a query
        try {
            if ($params != null) {
                $gestor = $conn->prepare($query);
                $gestor->execute($params);
            } else {
                $gestor = $conn->prepare($query);
                $gestor->execute();
            }
            // se ocorrer tudo normal ele valida e efetua a query caso contratio defaz
            $conn->commit();
        // captura se tiver acontecido um erro
        // o $e é responsavel por capturar  o erro e caso necessario posso saber oq exatamente deu errado    
        } catch (PDOException $e) {    
            // reposavel por desfazer oq foi feito e com isso retorna false como resuktado da requisição    
            $conn->rollBack();
            return false;
        }

        if ($close_conn) {
            $conn = null;
        }
        
        return true;
    }
}
?>