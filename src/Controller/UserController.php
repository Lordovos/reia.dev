<?php
declare(strict_types=1);

namespace ReiaDev\Controller;

class UserController extends Controller {
    public function index(string $username): void {
        $profile = $this->model->findUsername($username);

        if (!$profile) {
            header("HTTP/1.1 404 Not Found");
        }
        $this->render("user.twig", [
            "profile" => $profile
        ]);
    }
}
