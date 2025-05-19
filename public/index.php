<?php 
    require_once '../vendor/autoload.php';
    require_once '../framework/autoload.php';

    require_once "../controllers/MainController.php";

    require_once "../controllers/Controller404.php";
    require_once "../controllers/ObjectController.php";
    require_once "../controllers/SearchController.php";
    require_once "../controllers/PortalObjectCreateController.php";
    require_once "../controllers/PortalObjectTypesController.php";
    require_once "../controllers/PortalObjectDeleteController.php";
    require_once "../controllers/PortalObjectUpdateController.php";

    require_once "../RestAPI/PortalRestController.php";
    require_once "../middlewares/LoginRequiredMiddleware.php";

    $url = $_SERVER['REQUEST_URI'];

    $matches = [];
    if(preg_match('#^/api/portal(/\d+)?/?#', $url, $matches)) {
        $id = substr($matches[1] ?? "", 1);
        //print_r($matches);
        $controller = new PortalRestController;
        $controller->process($id);
    }

    


    $loader = new \Twig\Loader\FilesystemLoader('../views');
    $twig = new \Twig\Environment($loader, [
        "debug" => true // добавляем тут debug режим
    ]);
    $twig->addExtension(new \Twig\Extension\DebugExtension()); // и активируем расширение

    $context = [];

    $pdo = new PDO("mysql:host=localhost;dbname=portal_db;charset=utf8", "root", "");

    $router = new Router($twig, $pdo);
    $router->add("/", MainController::class);

    $router->add("/portal-character/(?P<id>\d+)", ObjectController::class); 
    $router->add("/search", SearchController::class);
    $router->add("/create", PortalObjectCreateController::class)
        ->middleware(new LoginRequiredMiddleware());
    $router->add("/types", PortalObjectTypesController::class)
        ->middleware(new LoginRequiredMiddleware());
    $router->add("/portal-character/(?P<id>\d+)/delete", PortalObjectDeleteController::class)
        ->middleware(new LoginRequiredMiddleware());
    $router->add("/portal-character/(?P<id>\d+)/edit", PortalObjectUpdateController::class)
        ->middleware(new LoginRequiredMiddleware());

    $router->get_or_default(Controller404::class);
?>