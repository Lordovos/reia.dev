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
        if (empty($_SESSION["user_id"]) && !empty($_COOKIE["remember_me"])) {
            list($selector, $authenticator) = explode(":", $_COOKIE["remember_me"]);
            $authToken = new \ReiaDev\AuthToken();
            $token = $authToken->get($selector);

            if ($token && hash_equals($token["token"], hash("sha256", base64_decode($authenticator)))) {
                $_SESSION["user_id"] = $token["user_id"];
                $selector = base64_encode(random_bytes(9));
                $authenticator = random_bytes(33);
                $authToken->generateCookie($selector, $authenticator);
                $authToken->update($selector, $authenticator, $token["user_id"]);
            }
        }
        if (!empty($_SESSION["user_id"])) {
            $userModel = new \ReiaDev\Model\UserModel();
            $user = $userModel->findId($_SESSION["user_id"]);

            if ($user) {
                $this->user = new \ReiaDev\User($user["id"], $user["username"], $user["email"], $user["role"]);
                /**
                 * If a user is banned while logged in, we force them to
                 * logout.
                 */
                if ($this->user->role === \ReiaDev\Role::BANNED_USER) {
                    header("Location: /logout");
                }
            } else {
                $this->user = null;
            }
        } else {
            $this->user = null;
        }
    }
    protected function hasUser(): void {
        if (!$this->user) {
            $this->flash->error("Please log in to view this page.");
            $this->flash->setMessages();
            $this->setPreviousUrl($_SERVER["REQUEST_URI"] ?? "");
            header("Location: /login");
            exit();
        }
    }
    protected function isAdministrator(): void {
        if (!$this->user->isAdministrator()) {
            $this->flash->error("You're not authorized to view this page.");
            $this->flash->setMessages();
            header("Location: /");
            exit();
        }
    }
    protected function setTitle(?string $str): string {
        $pageTitle = "reia.dev";

        if ($str) {
            $pageTitle = $str . " - " . $pageTitle;
        }
        return $pageTitle;
    }
    protected function setPreviousUrl(string $str): void {
        $_SESSION["previous_url"] = $str;
    }
    protected function getPreviousUrl(): ?string {
        $previousUrl = $_SESSION["previous_url"] ?? null;
        unset($_SESSION["previous_url"]);
        return $previousUrl;
    }
    /**
     * Converts strings to all lowercase and converts whitespace to dashes.
     */
    protected function toSlug(string $str): string {
        $str = strtolower($str);
        $str = trim($str);
        $str = preg_replace("/[^a-z0-9 -]/", "", $str);
        $str = preg_replace("/\s+/", " ", $str);
        $str = str_replace(" ", "-", $str);
        return $str;
    }
}
