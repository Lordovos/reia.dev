<?php
declare(strict_types=1);
require_once __DIR__ . "/../vendor/autoload.php";
/**
 * If the environment variable APP_ENV isn't set, or it isn't equal to
 * "production" we load the .env file from the user's local repository.
 */
if (empty($_ENV["APP_ENV"]) || $_ENV["APP_ENV"] !== "production") {
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
    "home_controller" => new \ReiaDev\Controller\HomeController(new \ReiaDev\Model\HomeModel(), $twig, $flash),
    "register_controller" => new \ReiaDev\Controller\RegisterController(new \ReiaDev\Model\RegisterModel(), $twig, $flash, $csrfToken),
    "login_controller" => new \ReiaDev\Controller\LoginController(new \ReiaDev\Model\LoginModel(), $twig, $flash, $csrfToken),
    "user_controller" => new \ReiaDev\Controller\UserController(new \ReiaDev\Model\UserModel(), $twig, $flash),
    "wiki_controller" => new \ReiaDev\Controller\WikiController(new \ReiaDev\Model\WikiModel(), $twig, $flash, $csrfToken)
];
$router = new \Bramus\Router\Router();
require_once __DIR__ . "/routes.php";
