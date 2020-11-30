<?php
declare(strict_types=1);

namespace ReiaDev\Controller;

class UserController extends Controller {
    public function index(string $username): void {
        $profile = $this->model->findUsername($username);

        $this->render("user.twig", [
            "profile" => $profile
        ]);
    }
}
