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
    public function search(string $term): void {
        $results = $this->model->search($term);

        $this->render("search.twig", [
            "term" => $term,
            "results" => $results
        ]);
    }
    public function newSearch(): void {
        $term = $_POST["search"] ?? "";
        header("Location: /search/" . $term);
    }
}
