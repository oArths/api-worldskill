<?php
include_once(dirname(__FILE__) . '/../config/database.php');
include_once(dirname(__FILE__) . '/../core/auth.php');
date_default_timezone_set('America/Sao_Paulo');

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
            http_response_code(422);
            return $this->erro_response($erro);    ;
        }

        $user = $validacao[0];

        if(!password_verify($params['password'], $user['password'])){
            
            if($params['password'] == $user['password']){
                $cripPass = password_hash($params['password'], PASSWORD_DEFAULT);
                $update = [
                    ':id' => $user['id'],
                    ':password' => $cripPass
                ];
                $db-> QUERY("
                    UPDATE  user SET password = :password WHERE id = :id", $update);
            }else{
                http_response_code(422);
                return $this->erro_messenge('Invalid email or password');
            }
        }
        
        $consulta = [
            ':userId' => $validacao[0]['id']
        ];
        
        $token = $db->QUERY("SELECT tokenString FROM accesstoken WHERE userId = :userId ", $consulta);

        
        if(empty($token)){
            return $auth->create_token($params);
        }
        
        $valid = $auth->valid_token($token[0]['tokenString']);
        
        if($valid !== true){
            $newparams = $params;
            $auth->delete_token($token[0]['tokenString']);
            return $auth->create_token($newparams);
        }else{
            return $token;    
        }
        
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
        $auth = new Auth;

        $validacao = $db->QUERY('SELECT * FROM movie');
        $exist = $auth->valid_token($params['token']);

        if($exist == false ){
            $insert = [
                ':token' => $params['token']
            ];
            http_response_code(403);
             $db->QUERY('DELETE FROM accesstoken WHERE tokenString = :token', $insert);
            return $this->erro_messenge('Invalid token');
        }

        $page = $params['data']['page'];
        $pagesize = $params['data']['pageSize'];
        $sortdir = $params['data']['sortDir'];
        $sortby = $params['data']['sortBy'];
        
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
        
        $movies = [];
        foreach($consulta as $movie){
            $fromat = explode("-", $movie['releaseDate']);
            $date = implode('/', array_reverse($fromat));
            
            $movies[] = [
                'id' =>$movie['id'],
                'title' =>$movie['title'],
                'durationMinutes' =>$movie['durationMinutes'],
                'releaseDate' =>$date,
                'posterUrl' => '/api-worldskill/api/v1/media/' . $movie['posterUrl'],
                'singlePageUrl' =>'/api-worldskill/api/v1/movies/' . $movie['id'],
            ];
        
        }


        return $movies;

    }
    public function get_movie($params){
        $db = new database;
        $auth = new Auth;

       
        $exist = $auth->valid_token($params['token']);

        if($exist == false ){
            $insert = [
                ':token' => $params['token']
            ];
            http_response_code(403);
             $db->QUERY('DELETE FROM accesstoken WHERE tokenString = :token', $insert);
            return $this->erro_messenge('Invalid token');
        }
        $consulta = [
            ':id' => $params['data']
        ];
        // return $consulta;
        $validacao = $db->QUERY('SELECT * FROM movie WHERE id = :id', $consulta);

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
        ', $consulta);
        
        $movieEnpooint = [];
        if(!empty($validacao)){
            foreach($validacao as $movie){
 
                $movieEnpooint[] = [
                    'title' =>$movie['title'],
                    'synopsis' =>$movie['synopsis'],
                    'durationMinutes' =>$movie['durationMinutes'],
                    'releaseDate' =>$movie['releaseDate'],
                    'posterUrl' => '/api-worldskill/api/v1/media/' . $movie['posterUrl'],
                    'trailerUrl' => '/api-worldskill/api/v1/media/' . $movie['trailerUrl'],
                ];
            
           }

        }
         $artists = [];
         foreach($credit as $artist){
 
             $artists[] = [
                 'artistId' =>$artist['id'],
                 'name' =>$artist['name'],
                 'role' =>$artist['title'],
                 'photoUrl' => '/api-worldskill/api/v1/media/' . $params['data'],
                 'singlePageUrl' =>'/api-worldskill/api/v1/artists/' . $artist['id'],
             ];
         
        }
        $results = [];

        if (!empty($validacao)) {
            $last_item = end($movieEnpooint);
            $last_item['credit'] = $artists;

            $results = $data[key($validacao)] = $last_item;
        }
        return $results;

    }
    public function get_any_artist($params){
        $db = new database;
        $auth = new Auth;

        // $consulta = $db->QUERY('SELECT * FROM artist');
        $exist = $auth->valid_token($params['token']);

        if($exist == false ){
            $insert = [
                ':token' => $params['token']
            ];
            http_response_code(403);
             $db->QUERY('DELETE FROM accesstoken WHERE tokenString = :token', $insert);
            return $this->erro_messenge('Invalid token');
        }


        $page = $params['data']['page'];
        $pagesize = $params['data']['pageSize'];
        $sortDir = $params['data']['sortDir'];

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
        $artists = [];
        if(!empty($results)){
            foreach($results as $artist){

                $artists[] =[
                    'id' =>$artist['id'],
                    'name' =>$artist['name'],
                    'photoUrl' => '/api-worldskill/api/v1/media/' . $artist['photoUrl'],
                    'singlePageUrl' => '/api-worldskill/api/v1/artists/' . $artist['id'],
                ];

            }
        }

        return $artists;
    }
    public function get_artist($params){
        $db = new database;
        $auth = new Auth;

        $insert = [
            ':id' => $params['data']
        ];

        $exist = $auth->valid_token($params['token']);

        if($exist == false ){
            $insert = [
                ':token' => $params['token']
            ];
            http_response_code(403);
             $db->QUERY('DELETE FROM accesstoken WHERE tokenString = :token', $insert);
            return $this->erro_messenge('Invalid token');
        }

        $consulta = $db->QUERY('SELECT * FROM artist WHERE id = :id', $insert);

        if(empty($consulta)){
            http_response_code(404);
            return $this->erro_messenge('Invalid artist id');
        }
        $artists = [];
        if(!empty($consulta)){
            foreach($consulta as $artist){
 
                $artists[] = [
                    'name' =>$artist['name'],
                    'birthday' =>$artist['birthday'],
                    'biography' =>$artist['biography'],
                    'photoUrl' => '/api-worldskill/api/v1/media/' . $artist['photoUrl'],
                ];
            
           }

        }

        $query = $db->QUERY(
            'SELECT movie.id, movie.title, movie.durationMinutes, movie.releaseDate, movie.posterUrl
            FROM 
            credit
            JOIN 
             movie ON credit.movieId = movie.Id
             WHERE credit.artistId = :id
            ', $insert);
        
        $movie = [];    
        if(!empty($query)){
            foreach($query as $movies){
                $movie[] = [
                    'id' =>$movies['id'],
                    'title' =>$movies['title'],
                    'duration' =>$movies['durationMinutes'],
                    'releaseDate' =>$movies['releaseDate'],
                    'posterUrl' => '/api-worldskill/api/v1/media/' . $movies['posterUrl'],
                    'singlePageUrl' => '/api-worldskill/api/v1/movies/' . $movies['id'],
                ];
            }
        }
        // return $movie;
        $results = [];
        if (!empty($consulta)) {
            $last_item = end($artists);
            $last_item['movie'] = $movie;

            $results = $data[key($consulta)] = $last_item;
        }
        return $results;



    }
    public function get_any_genres($params){
        $db = new database;
        $auth = new Auth;
        $consulta = $db->QUERY('SELECT * FROM genre');

        $exist = $auth->valid_token($params['token']);

        if($exist == false ){
            $insert = [
                ':token' => $params['token']
            ];
            http_response_code(403);
             $db->QUERY('DELETE FROM accesstoken WHERE tokenString = :token', $insert);
            return $this->erro_messenge('Invalid token');
        }
        $page = $params['data']['page'];
        $pagesize = $params['data']['pageSize'];
        $sortDir = $params['data']['sortDir'];

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
        $auth = new Auth;

        $id = $params['data']['id'];
        $page = $params['data']['page'];
        $pagesize = $params['data']['pageSize'];
        $pagedir = $params['data']['sortDir'];
        $sortby = $params['data']['sortBy'];
        
        $exist = $auth->valid_token($params['token']);

        if($exist == false ){
            $insert = [
                ':token' => $params['token']
            ];
            http_response_code(403);
             $db->QUERY('DELETE FROM accesstoken WHERE tokenString = :token', $insert);
            return $this->erro_messenge('Invalid token');
        }


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
                    $avaliacao['myEvalution'] = 'null';
                }
                $newconsulta[] = $avaliacao;
            }
            return $newconsulta;
        }

    }
    function getMediaContent($data) {
        $db = new database();
        $auth = new Auth;

        $id = $data['data'];

        $exist = $auth->valid_token($data['token']);

        if($exist == false ){
            $insert = [
                ':token' => $data['token']
            ];
            http_response_code(403);
             $db->QUERY('DELETE FROM accesstoken WHERE tokenString = :token', $insert);
            return $this->erro_messenge('Invalid token');
        }


        $exist = $db->QUERY("
        SELECT 'posterUrl' AS type FROM movie WHERE posterUrl = $id 
        UNION ALL
        SELECT 'trailerUrl' AS type FROM movie WHERE trailerUrl = $id
        UNION ALL
        SELECT 'photoUrl' AS type FROM artist WHERE photoUrl = $id
        ");
        
        if(empty($exist)){
            http_response_code(404);
            return $this->erro_messenge("Could not find any file with the id $id");
            
        }
        $baseDir = dirname(__FILE__) . "\\media\\"; 

        switch ($exist[0]['type']) {
            case 'trailerUrl':
                $filePath = $baseDir . $id . '.mp4';
                break;
                default:
                $filePath = $baseDir . $id . '.jpg';
                break;
            }
            
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
        $db = new database;

        $brutToken = isset($params['token']) ? $params['token'] : null;
        $cleartoken = explode(" ", $brutToken);
        $token = $cleartoken[1];

       


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

        $exist = $auth->valid_token($token);

        if($exist == false ){
            $insert = [
                ':token' => $token
            ];
            http_response_code(403);
             $db->QUERY('DELETE FROM accesstoken WHERE tokenString = :token', $insert);
            return $this->erro_messenge('Invalid token');
        }

        $emailUser = [':email'=> $exist->email];
        
        $userId = $db->QUERY("SELECT id FROM user WHERE email = :email", $emailUser)[0]['id'];

        $reviewExist = $db->QUERY("SELECT * FROM review where $movieId = movieId AND userId = $userId");

        $params = [
            ':movieId' => $movieId,
            ':stars' => $star,
            ':content' => $content,
            ':userId' => $userId,
            ':createdAt'=> date("Y-m-d H:i:s",time())
        ];

        if(empty($reviewExist)){
            $db->QUERY("INSERT INTO review ( userId, movieId, content, stars, createdAt) 
            VALUES ( :userId, :movieId, :content, :stars, :createdAt)", $params);
            return $this->erro_messenge('Review has been successfully created');
            

        }else{
            $db->QUERY("UPDATE review SET movieId = :movieId, content = :content, stars = :stars, createdAt = :createdAt WHERE userId = :userId ", $params);
            return$this->erro_messenge('Review has been successfully update');
            
            
        }

    }
    public function delete_reviews($params){
        $auth = new Auth;
        $db = new database;
        $idreview = $params['data']['reviewId'];

        $exist = $auth->valid_token($params['token']);
        

        if($exist == false ){
            $insert = [
                ':token' => $params['token']
            ];
            http_response_code(403);
            $db->QUERY('DELETE FROM accesstoken WHERE tokenString = :token', $insert);
            return $this->erro_messenge('Invalid token');
        }

        $idExisted = $db->QUERY("SELECT * FROM reviewevaluation WHERE reviewId = $idreview ");

        if(empty($idExisted)){
            http_response_code(400);
            return $this->erro_messenge('Invalid review id');
        }
        $emailUser = [':email'=> $exist->email];
        
        $userId = $db->QUERY("SELECT id FROM user WHERE email = :email", $emailUser)[0]['id'];
        
        $reviewuser = $db->QUERY("SELECT * FROM reviewevaluation WHERE reviewId = $idreview AND userId = $userId ");

        if(empty($reviewuser)){
            http_response_code(404);
            return $this->erro_messenge('You haven`t published a evaluation to this review');
        }elseif(!empty($reviewuser)){
            $db->QUERY("DELETE FROM reviewevaluation WHERE reviewId = $idreview AND userId = $userId");
            http_response_code(204);
            return ;
        }
       
    }
    public function delete_reviews_movie($params){
        $auth = new Auth;
        $db = new database;
        $idmovie = $params['data']['movieId'];

        $exist = $auth->valid_token($params['token']);
        

       if($exist == false ){
            $insert = [
                ':token' => $params['token']
            ];
            http_response_code(403);
             $db->QUERY('DELETE FROM accesstoken WHERE tokenString = :token', $insert);
            return $this->erro_messenge('Invalid token');
        }

        $idExisted = $db->QUERY("SELECT * FROM review WHERE movieId = $idmovie ");

        if(empty($idExisted)){
            http_response_code(400);
            return $this->erro_messenge('Invalid movie id');
        }
        $emailUser = [':email'=> $exist->email];
        
        $userId = $db->QUERY("SELECT id FROM user WHERE email = :email", $emailUser)[0]['id'];
        
        $reviewuser = $db->QUERY("SELECT * FROM review WHERE movieId = $idmovie AND userId = $userId ");

        if(empty($reviewuser)){
            http_response_code(404);
            return $this->erro_messenge('You haven`t published a evaluation to this movie');
        }elseif(!empty($reviewuser)){
            $db->QUERY("DELETE FROM review WHERE movieId = $idmovie AND userId = $userId");
            http_response_code(204);
            return ;
        }

    }

}


