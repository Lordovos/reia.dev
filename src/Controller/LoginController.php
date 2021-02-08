<?php
declare(strict_types=1);

namespace ReiaDev\Controller;

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
        $username = trim($_POST["username"]) ?? "";
        $password = $_POST["password"] ?? "";
        $rememberMe = $_POST["remember_me"] ?? "no";
        $rememberMe = ($rememberMe === "yes" ? 1 : 0);
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
                "username" => $username,
                "remember_me" => $rememberMe
            ];
            header("Location: /login");
        } else {
            $_SESSION["user_id"] = $user["id"];

            if ($rememberMe) {
                $selector = base64_encode(random_bytes(9));
                $authenticator = random_bytes(33);
                $authToken = new \ReiaDev\AuthToken();
                $authToken->generateCookie($selector, $authenticator);
                $authToken->add($selector, $authenticator, $user["id"]);
            }
            $this->flash->success("User logged in successfully.");
            $this->flash->setMessages();
            $this->csrfToken->destroy();
            $previousUrl = $this->getPreviousUrl();

            if ($previousUrl) {
                header("Location: " . $previousUrl);
            } else {
                header("Location: /user/" . $user["username"]);
            }
        }
    }
    public function logout(): void {
        if ($this->user) {
            $authToken = new \ReiaDev\AuthToken();
            $authToken->remove($this->user->id);
            $authToken->destroyCookie();
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
