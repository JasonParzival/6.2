<?php

class LoginRequiredMiddleware extends BaseMiddleware {
    public function apply(BaseController $controller, array $context)
    {
        // берем значения которые введет пользователь
        $user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
        $password = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';

        $query = $controller->pdo->prepare("SELECT password FROM portal_users WHERE username= :my_username");
        $query->bindValue("my_username", $user);
        $query->execute();

        $pw_from_db = $query->fetch(PDO::FETCH_ASSOC);

        if ($password != $pw_from_db['password']) {
            header('WWW-Authenticate: Basic realm="Portal objects"');
            http_response_code(401); // ну и статус 401 -- Unauthorized, то есть неавторизован
            exit; // прерываем выполнение скрипта
        }
    }
}