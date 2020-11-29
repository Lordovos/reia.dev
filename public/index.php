<?php
declare(strict_types=1);
require_once __DIR__ . "/../config/bootstrap.php";

$controller = new \ReiaDev\Controller\HomeController($twig);
$controller->index();
