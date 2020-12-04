<?php
declare(strict_types=1);

namespace ReiaDev\Controller;
/**
 * TODO: Remember me.
 */
class LoginController extends Controller {
    private \ReiaDev\CSRFToken $csrfToken;

    public function __construct(\ReiaDev\Model\Model $model, \Twig\Environment $twig, \ReiaDev\Flash $flash, \ReiaDev\CSRFToken $csrfToken) {
        parent::__construct($model, $twig, $flash);
        $this->csrfToken = $csrfToken;

        if (!$this->csrfToken->get()) {
            $this->csrfToken->generate();
        }
    }
    public function index(): void {
        $formInput = $_SESSION["form_input"] ?? null;
        unset($_SESSION["form_input"]);

        if ($this->user) {
            $this->flash->error("You're already logged in.");
            $this->flash->setMessages();
            header("Location: /user/" . $this->user->username);
            exit();
        }
        $this->render("login.twig", [
            "form_input" => $formInput,
            "csrf_token" => $this->csrfToken->get()
        ]);
    }
    public function login(): void {
        $csrfToken = $_POST["csrf_token"] ?? "";
        $username = $_POST["username"] ?? "";
        $password = $_POST["password"] ?? "";
        $user = null;

        if ($this->csrfToken->verify($csrfToken)) {
            if ($username && $password) {
                $user = $this->model->verify($username, $password);

                if (!$user) {
                    $this->flash->error("Invalid credentials.");
                } elseif ($user["role"] === \ReiaDev\Role::UNVERIFIED_USER) {
                    $this->flash->error("Unverified user. Please contact a member of the moderation team.");
                } elseif ($user["role"] === \ReiaDev\Role::BANNED_USER) {
                    $this->flash->error("Banned user. Please contact a member of the moderation team.");
                }
            } else {
                $this->flash->error("Please enter a username and password.");
            }
        } else {
            $this->flash->error("Possible Cross-Site Request Forgery. Please contact the server administrator.");
        }
        if ($this->flash->hasErrors()) {
            $this->flash->setMessages();
            $_SESSION["form_input"] = [
                "username" => $username
            ];
            header("Location: /login");
        } else {
            $_SESSION["user_id"] = $user["id"];
            $this->flash->success("User logged in successfully.");
            $this->flash->setMessages();
            $this->csrfToken->destroy();
            header("Location: /user/" . $user["username"]);
        }
    }
    public function logout(): void {
        if ($this->user) {
            unset($_SESSION["user_id"]);
            $this->flash->success("User logged out succesfully.");
            $this->flash->setMessages();
            header("Location: /");
        } else {
            $this->flash->error("Please log in to view this page.");
            $this->flash->setMessages();
            header("Location: /login");
        }
    }
}
