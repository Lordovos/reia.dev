<?php
declare(strict_types=1);

namespace ReiaDev\Controller;

class Controller {
    protected \ReiaDev\Model\Model $model;
    protected \Twig\Environment $twig;
    protected \ReiaDev\Flash $flash;
    protected ?\ReiaDev\User $user = null;

    public function __construct(\ReiaDev\Model\Model $model, \Twig\Environment $twig, \ReiaDev\Flash $flash) {
        $this->model = $model;
        $this->twig = $twig;
        $this->flash = $flash;
        $this->setUser();
    }
    protected function render(string $template, array $data): void {
        /**
         * Before rendering the template, we inject a few helpers such as flash
         * messages, the application's current version, and the user.
         */
        $data["flash"] = $this->flash->getMessages();
        /**
         * The seed is used as a random value to prevent outdated versions of
         * stylesheets and scripts from being cached.
         */
        $data["seed"] = bin2hex(random_bytes(8));
        $version = new \ReiaDev\Version();
        $data["version"] = $version->get();
        $data["user"] = $this->user;

        echo $this->twig->render($template, $data);
    }
    protected function setUser(): void {
        if (!empty($_SESSION["user_id"])) {
            $userModel = new \ReiaDev\Model\UserModel();
            $user = $userModel->findId($_SESSION["user_id"]);

            if ($user) {
                $this->user = new \ReiaDev\User($user["id"], $user["username"], $user["email"], $user["role"]);
            } else {
                $this->user = null;
            }
        } else {
            $this->user = null;
        }
    }
    protected function toSlug(string $str): string {
        $str = strtolower($str);
        $str = trim($str);
        $str = preg_replace("/[^a-z0-9 -]/", "", $str);
        $str = preg_replace("/\s+/", " ", $str);
        $str = str_replace(" ", "-", $str);
        return $str;
    }
}
