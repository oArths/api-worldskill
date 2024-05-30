<?php
// core/Auth.php

class Auth {
    public static function authenticate() {
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthenticated user']);
            exit;
        }

        $user = $_SERVER['PHP_AUTH_USER'];
        $pass = $_SERVER['PHP_AUTH_PW'];

        // Validar usuário e senha aqui (ex: consultando no banco de dados)
        // Exemplo de validação simples (substituir por lógica real)
        if ($user !== 'usuario' || $pass !== 'senha') {
            http_response_code(403);
            echo json_encode(['message' => 'Invalid token']);
            exit;
        }
    }
}
?>
