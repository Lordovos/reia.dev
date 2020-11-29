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
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . "/../templates");
$twig = new \Twig\Environment($loader);
$flash = new \ReiaDev\Flash();
$controllers = [
    "home_controller" => new \ReiaDev\Controller\HomeController(new \ReiaDev\Model\Model(), $twig, $flash),
    "register_controller" => new \ReiaDev\Controller\RegisterController(new \ReiaDev\Model\RegisterModel(), $twig, $flash, new \ReiaDev\CSRFToken())
];
$router = new \Bramus\Router\Router();

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
$router->set404(function () use ($controllers) {
    header("HTTP/1.1 404 Not Found");
    $controllers["home_controller"]->notFound();
});
