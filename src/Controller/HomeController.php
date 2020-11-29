<?php
declare(strict_types=1);

namespace ReiaDev\Controller;

class HomeController extends Controller {
    public function index(): void {
        $this->render("index.twig", []);
    }
    public function about(): void {
        $this->render("about.twig", []);
    }
    public function notFound(): void {
        $this->render("404.twig", []);
    }
}
