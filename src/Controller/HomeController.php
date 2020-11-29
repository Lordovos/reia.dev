<?php
declare(strict_types=1);

namespace ReiaDev\Controller;

class HomeController extends Controller {
    public function index(): void {
        $this->render("index.twig", []);
    }
}
