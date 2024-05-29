<?php

class database{
    public function QUERY($query, $params ){
        $result = null;

        $host = "localhost:3307";
        $user = "root";
        $pass = "";
        $db = "worldskill";
        
        
        $conn = new PDO(
            "mysqli:host=$host;
            dbname=$db",
            $user,
            $pass
            array(PDO::ATTR_PERSISTENT => true));
    }
}
?>