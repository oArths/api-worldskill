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
    public function content($message){
        return [
            'content' => $message,
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
       $end = $validacao[0];

        $exist = $auth->exist_token($end);
        $token = $exist['token'];
        return $token;
    }
    //-----------------------------------------------
    public function token_delete($params){
        $db = new database;

        if(empty($params)){
            return ;
        }

        $clear = explode(" ",$params['Authorization']);
        $token = $clear[1];

        $insert = [
            ':token' => $token,
        ];

        $consulta = $db->QUERY('DELETE FROM accesstoken WHERE tokenString = :token', $insert);
        
        if($consulta !== false ){
            return  http_response_code(204); ;
        }else{
            return ;
        }
    }
    public function get_any_movie($params){
        $db = new database;

        $validacao = $db->QUERY('SELECT * FROM movie');

        $page = $params['page'];
        $pagesize = $params['pageSize'];
        $sortdir = $params['sortDir'];
        $sortby = $params['sortBy'];
        
        if($page < 1 || $page > count($validacao)){
            $page = 1;
            $pagesize = 2;
        }
        if($pagesize < 1){
            $pagesize = 4;
        }
        if($sortdir !== 'asc' && $sortdir !== 'desc'){
            $sortdir = 'desc';
        }
        if($sortby !== 'title' && $sortby !== 'releaseDate'){
            $sortby = 'releaseDate';
        }
        
        
        $offset = ($page - 1) * $pagesize;
        
        $consulta = $db->QUERY("SELECT * FROM movie ORDER BY $sortby $sortdir LIMIT $pagesize OFFSET $offset");

        return $consulta;

    }
    public function get_movie($params){
        $db = new database;
        $insert = [
            ':id' => $params
        ];

        $validacao = $db->QUERY('SELECT * FROM movie WHERE id = :id', $insert);

        if(empty($validacao)){
            http_response_code(404);
            return $this->erro_messenge('Invalid movie id');
        }
        $credit = $db->QUERY
        ('SELECT 
            name, photoUrl, role.title, artist.id
        FROM 
            credit
        JOIN 
            artist ON artistId = artist.id
        JOIN 
            role ON roleId = role.id
        WHERE
            credit.movieId = :id 
        ', $insert);


        if (!empty($validacao)) {
            $last_item = end($validacao);
            $last_item['credit'] = [$credit];

            $results = $data[key($validacao)] = $last_item;
            return $results;
        }
        

        return $this->erro_messenge('Invalid movie id');



    }
    public function get_movie_inter($params){
        $db = new database;
        $insert = [
            ':id' => $params
        ];

        $validacao = $db->QUERY('SELECT * FROM movie WHERE id = :id', $insert);

        if(empty($validacao)){
            http_response_code(404);
            return $this->erro_messenge('Invalid movie id');
        }
   

        if (!empty($validacao)) {
        
            return $validacao;
        }
        

        return $this->erro_messenge('Invalid movie id');



    }
    public function get_any_artist($params){
        $db = new database;

        $consulta = $db->QUERY('SELECT * FROM artist');

        $page = $params['page'];
        $pagesize = $params['pageSize'];
        $sortDir = $params['sortDir'];

        if($page < 1){
            $page = 1;
        }
        if($pagesize < 1){
            $pagesize = 1;
        }
        if($sortDir !== 'asc' && $sortDir !== 'desc'){
            $sortDir = 'asc';
        }

        $offset = ($page - 1) * $pagesize;

        $results = $db->QUERY("SELECT  id, name, photoUrl FROM artist ORDER BY  id $sortDir LIMIT $pagesize OFFSET $offset");

        return $results;
    }
    public function get_artist($params){
        $db = new database;

        $insert = [
            ':id' => $params
        ];

        $consulta = $db->QUERY('SELECT * FROM artist WHERE id = :id', $insert);

        if(empty($consulta)){
            http_response_code(404);
            return $this->erro_messenge('Invalid artist id');
        }

        $query = $db->QUERY(
            'SELECT movie.id, movie.title, movie.durationMinutes, movie.releaseDate, movie.posterUrl
            FROM 
            credit
            JOIN 
             movie ON credit.movieId = movie.Id
             WHERE credit.artistId = :id
            ', $insert);

        $id = $query[0]['id'];
            if(!empty($query)){

                $final = end($query);
                $final['singlePageUrl'] = "/api-worldskill/api/v1/movies/$id";
                $newQuery = $final;
            }

         if(!empty($consulta)){
            $movie = end($consulta);
            $movie['movies'] = [$newQuery];

            $results = $data[key($consulta)] = $movie;
            return $results;
         }


    }
    public function get_any_genres($params){
        $db = new database;

        $consulta = $db->QUERY('SELECT * FROM genre');

        $page = $params['page'];
        $pagesize = $params['pageSize'];
        $sortDir = $params['sortDir'];

        if($page < 1){
            $page = 1;
        }
        if($pagesize < 1){
            $pagesize = 1;
        }
        if($sortDir !== 'asc' && $sortDir !== 'desc'){
            $sortDir = 'desc';
        }
        $offset = ($page - 1) * $pagesize;
        
        $consulta = $db->QUERY("SELECT * FROM genre ORDER BY id $sortDir LIMIT $pagesize OFFSET $offset");

        return $consulta;
    }
    public function get_any_reviews($params){
        $db = new database;

        $id = $params['id'];
        $page = $params['page'];
        $pagesize = $params['pageSize'];
        $pagedir = $params['sortDir'];
        $sortby = $params['sortBy'];



        if($page < 1){
            $page = 1;
        }
        if($pagesize < 1){
            $pagesize = 1;
        }
        if($pagedir !== 'asc' && $pagedir !== 'desc'){
            $pagedir = 'desc';
        }
        if($sortby !== 'stars' && $sortby !== 'createdAt'){
            $sortby == 'stars';
        }

        $offset = ($page - 1) * $pagesize;

        $consulta  = $db->QUERY("SELECT * FROM review ORDER BY stars $pagedir LIMIT $pagesize OFFSET $offset");


        $review = $db->QUERY
        ("SELECT reviewevaluation.positive, review.movieId
         FROM
            review
        JOIN reviewevaluation ON reviewevaluation.id = review.id
        WHERE review.userId = $id
         ");


        if(!empty($review)){
            $reviewMap =[];
            foreach ($review as $map) {
                $reviewMap[$map['movieId']] = $map;
                }
        }
       
        if(!empty($consulta)){
            $newconsulta = [];
            foreach ($consulta as $avaliacao) {
                if(isset($reviewMap[$avaliacao['movieId']])){
                    $avaliacao['myEvalution'] = $reviewMap[$avaliacao['movieId']]['positive'];
                }else{
                    $avaliacao['myEvalution'] = 0;
                }
                $newconsulta[] = $avaliacao;
            }
            return $newconsulta;
        }

    }
    function getMediaContent($id) {
        $db = new database();
        $clear = explode('.', $id);
        $id = $clear[0];
        $ext = '.' . $clear[1];

        if($ext !== '.jpg' && $ext !== '.mp4'){
            http_response_code(404);
            return $this->erro_messenge("Could not find any file with the id $id");

        }
        $result = $db->query("
        SELECT 'photoUrl' AS type, photoUrl AS url FROM artist WHERE photoUrl = $id
        UNION
        SELECT 'posterUrl' AS type, posterUrl AS url FROM movie WHERE posterUrl = $id
        UNION
        SELECT 'trailerUrl' AS type, trailerUrl AS url FROM movie WHERE trailerUrl = $id
        ");
        if (empty($result)) {
            http_response_code(404);
            return $this->erro_messenge("Could not find any file with the id $id");
        }

        if($result[0]['type'] === 'trailerUrl' && $ext !== '.mp4'){
            return $this->erro_messenge("Cosddshe id $id");
        }
        if($result[0]['type'] === 'posterUrl' && $ext !== '.jpg'){
            return $this->erro_messenge("Cosddshe id $id");
        }
        if($result[0]['type'] === 'photoUrl' && $ext !== '.jpg'){
            return $this->erro_messenge("Cosddshe id $id");
        }

        $fileName = $result[0]['url'];
        $baseDir = dirname(__FILE__) . "\\media\\"; 
        $filePath = $baseDir . $fileName . $ext;

        if (!file_exists($filePath)) {
            http_response_code(404);
            return $this->erro_messenge("Could not find any file with the id $id");
        }
    
        $fileContent = file_get_contents($filePath);
    
        $base64Content = base64_encode($fileContent);
    
        return $this->content($base64Content);
    }
    public function create_reviews($params){
        $auth = new Auth;

        $brutToken = isset($params['token']) ? $params['token'] : null;
        $cleartoken = explode(" ", $brutToken);
        $token['id'] = $cleartoken[1];
        $star = isset($params['data']['stars']) ? $params['data']['stars'] : null;
        $movieId = isset($params['data']['movieId']) ? $params['data']['movieId'] : null;
        $content = isset($params['data']['content']) ? $params['data']['content'] : null;

        $erro = [
            'propriedade' => ['erro'],
            'propriedades' => ['erro']
        ];
        
        if(empty($star) || empty($content)){
            http_response_code(422);
            return $this->erro_response($erro);
        }
        if(empty($movieId)){
            http_response_code(400);
            return $this->erro_messenge('Invalid movie id');
        }

        $exist = $auth->is_valid($token);

        return  $exist;
    }

}


