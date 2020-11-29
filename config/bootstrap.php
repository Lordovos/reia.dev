<?php
declare(strict_types=1);
require_once __DIR__ . "/../vendor/autoload.php";
/**
 * If the environment variable APP_ENV isn't set, or it isn't equal to
 * "production" we load the .env file from the user's local repository.
 */
if (!isset($_ENV["APP_ENV"]) || $_ENV["APP_ENV"] !== "production") {
    if (file_exists(__DIR__ . "/../.env")) {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
        $dotenv->load();
    }
}
/**
 * TODO: Wrap bootstrapping portion of the application with an application
 * class?
 */
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . "/../templates");
$twig = new \Twig\Environment($loader);
$flash = new \ReiaDev\Flash();
$csrfToken = new \ReiaDev\CSRFToken();
$controllers = [
    "home_controller" => new \ReiaDev\Controller\HomeController(new \ReiaDev\Model\Model(), $twig, $flash),
    "register_controller" => new \ReiaDev\Controller\RegisterController(new \ReiaDev\Model\RegisterModel(), $twig, $flash, $csrfToken),
    "login_controller" => new \ReiaDev\Controller\LoginController(new \ReiaDev\Model\LoginModel(), $twig, $flash, $csrfToken)
];
$router = new \Bramus\Router\Router();
/**
 * TODO: As the list of routes grows larger, eventually we'll need to break
 * them off into their own file.
 */
$router->get("/", function () use ($controllers) {
    $controllers["home_controller"]->index();
});
$router->get("/about", function () use ($controllers) {
    $controllers["home_controller"]->about();
});
$router->get("/register", function () use ($controllers) {
    $controllers["register_controller"]->index();
});
$router->post("/register", function () use ($controllers) {
    $controllers["register_controller"]->register();
});
$router->get("/login", function () use ($controllers) {
    $controllers["login_controller"]->index();
});
$router->post("/login", function () use ($controllers) {
    $controllers["login_controller"]->login();
});
$router->set404(function () use ($controllers) {
    header("HTTP/1.1 404 Not Found");
    $controllers["home_controller"]->notFound();
});
