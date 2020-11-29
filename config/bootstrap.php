<?php
declare(strict_types=1);
require_once __DIR__ . "/../vendor/autoload.php";

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . "/../templates");
$twig = new \Twig\Environment($loader);
